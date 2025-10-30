<?php

namespace UserSpace\Tests\Unit\Common\Module\Form\App\UseCase\GetPopulatedProfileForm;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use UserSpace\Common\Module\Form\App\UseCase\GetPopulatedProfileForm\GetPopulatedProfileFormCommand;
use UserSpace\Common\Module\Form\App\UseCase\GetPopulatedProfileForm\GetPopulatedProfileFormUseCase;
use UserSpace\Common\Module\Form\Src\Domain\Field\FieldInterface;
use UserSpace\Common\Module\Form\Src\Domain\Factory\FormFactoryInterface;
use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfig;
use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfigManagerInterface;
use UserSpace\Common\Module\Form\Src\Domain\Form\FormInterface;
use UserSpace\Common\Module\User\Src\Domain\UserApiInterface;

class GetPopulatedProfileFormUseCaseTest extends TestCase
{
    private const TEST_USER_ID = 123;
    private const TEST_FIELD_NAME_1 = 'first_name';
    private const TEST_FIELD_NAME_2 = 'last_name';
    private const TEST_FIELD_NAME_3 = 'email';
    private const TEST_FIELD_VALUE_1 = 'John';
    private const TEST_FIELD_VALUE_2 = 'Doe';

    private FormConfigManagerInterface|MockObject $formConfigManagerMock;
    private FormFactoryInterface|MockObject $formFactoryMock;
    private UserApiInterface|MockObject $userApiMock;
    private GetPopulatedProfileFormUseCase $useCase;

    protected function setUp(): void
    {
        $this->formConfigManagerMock = $this->createMock(FormConfigManagerInterface::class);
        $this->formFactoryMock = $this->createMock(FormFactoryInterface::class);
        $this->userApiMock = $this->createMock(UserApiInterface::class);

        $this->useCase = new GetPopulatedProfileFormUseCase(
            $this->formConfigManagerMock,
            $this->formFactoryMock,
            $this->userApiMock
        );
    }

    public function testExecuteSuccessfullyReturnsPopulatedForm(): void
    {
        // Arrange
        $command = new GetPopulatedProfileFormCommand(self::TEST_USER_ID);
        $formConfigMock = $this->createMock(FormConfig::class);
        $formMock = $this->createMock(FormInterface::class);

        // Mock fields
        $field1Mock = $this->createMock(FieldInterface::class);
        $field1Mock->method('getName')->willReturn(self::TEST_FIELD_NAME_1);
        $field1Mock->expects($this->once())
            ->method('setValue')
            ->with(self::TEST_FIELD_VALUE_1);

        $field2Mock = $this->createMock(FieldInterface::class);
        $field2Mock->method('getName')->willReturn(self::TEST_FIELD_NAME_2);
        $field2Mock->expects($this->once())
            ->method('setValue')
            ->with(self::TEST_FIELD_VALUE_2);

        $field3Mock = $this->createMock(FieldInterface::class);
        $field3Mock->method('getName')->willReturn(self::TEST_FIELD_NAME_3);
        // This field will not have a value from userApi, so setValue should not be called
        $field3Mock->expects($this->never())->method('setValue');

        $this->formConfigManagerMock
            ->expects($this->once())
            ->method('load')
            ->with('profile')
            ->willReturn($formConfigMock);

        $this->formFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($formConfigMock)
            ->willReturn($formMock);

        $formMock->expects($this->once())
            ->method('getFields')
            ->willReturn([$field1Mock, $field2Mock, $field3Mock]);

        $this->userApiMock
            ->expects($this->exactly(3)) // Called for each field
            ->method('getUserMeta')
            ->willReturnMap([
                [self::TEST_USER_ID, self::TEST_FIELD_NAME_1, true, self::TEST_FIELD_VALUE_1],
                [self::TEST_USER_ID, self::TEST_FIELD_NAME_2, true, self::TEST_FIELD_VALUE_2],
                [self::TEST_USER_ID, self::TEST_FIELD_NAME_3, true, null], // No value for email
            ]);

        // Act
        $result = $this->useCase->execute($command);

        // Assert
        $this->assertSame($formMock, $result->form);
    }

    public function testExecuteReturnsNullFormWhenConfigNotFound(): void
    {
        // Arrange
        $command = new GetPopulatedProfileFormCommand(self::TEST_USER_ID);

        $this->formConfigManagerMock
            ->expects($this->once())
            ->method('load')
            ->with('profile')
            ->willReturn(null);

        $this->formFactoryMock
            ->expects($this->never())
            ->method('create');

        $this->userApiMock
            ->expects($this->never())
            ->method('getUserMeta');

        // Act
        $result = $this->useCase->execute($command);

        // Assert
        $this->assertNull($result->form);
    }

    public function testExecuteReturnsFormWithUnsetValuesWhenNoUserData(): void
    {
        // Arrange
        $command = new GetPopulatedProfileFormCommand(self::TEST_USER_ID);
        $formConfigMock = $this->createMock(FormConfig::class);
        $formMock = $this->createMock(FormInterface::class);

        $field1Mock = $this->createMock(FieldInterface::class);
        $field1Mock->method('getName')->willReturn(self::TEST_FIELD_NAME_1);
        $field1Mock->expects($this->never())->method('setValue'); // No user data, so setValue not called

        $this->formConfigManagerMock->method('load')->willReturn($formConfigMock);
        $this->formFactoryMock->method('create')->willReturn($formMock);
        $formMock->method('getFields')->willReturn([$field1Mock]);

        $this->userApiMock
            ->expects($this->once())
            ->method('getUserMeta')
            ->with(self::TEST_USER_ID, self::TEST_FIELD_NAME_1, true)
            ->willReturn(null); // Simulate no user data

        // Act
        $result = $this->useCase->execute($command);

        // Assert
        $this->assertSame($formMock, $result->form);
    }
}