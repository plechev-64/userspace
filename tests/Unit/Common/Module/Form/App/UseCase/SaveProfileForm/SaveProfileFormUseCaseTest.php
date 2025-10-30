<?php

namespace UserSpace\Tests\Unit\Common\Module\Form\App\UseCase\SaveProfileForm;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use UserSpace\Common\Module\Form\App\UseCase\SaveProfileForm\SaveProfileFormCommand;
use UserSpace\Common\Module\Form\App\UseCase\SaveProfileForm\SaveProfileFormUseCase;
use UserSpace\Common\Module\Form\Src\Domain\Field\FieldInterface;
use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfig;
use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfigManagerInterface;
use UserSpace\Common\Module\Form\Src\Domain\Form\FormInterface;
use UserSpace\Common\Module\Form\Src\Infrastructure\Factory\FormFactory;
use UserSpace\Common\Module\Media\Src\Domain\TemporaryFileRepositoryInterface;
use UserSpace\Common\Module\User\Src\Domain\UserApiInterface;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\String\StringFilterInterface;

class SaveProfileFormUseCaseTest extends TestCase
{
    private const TEST_USER_ID = 123;
    private const TEST_FORM_TYPE = 'profile';

    // Core user fields
    private const TEST_CORE_EMAIL_FIELD_NAME = 'user_email';
    private const TEST_CORE_EMAIL_FIELD_VALUE = 'test@example.com';
    private const TEST_CORE_DISPLAY_NAME_FIELD_NAME = 'display_name';
    private const TEST_CORE_DISPLAY_NAME_FIELD_VALUE = 'Test User';
    private const TEST_CORE_PASS_FIELD_NAME = 'user_pass';
    private const TEST_CORE_PASS_FIELD_VALUE = 'new_secure_password';

    // Meta fields
    private const TEST_META_FIRST_NAME_FIELD_NAME = 'first_name';
    private const TEST_META_FIRST_NAME_FIELD_VALUE = 'John';
    private const TEST_META_LAST_NAME_FIELD_NAME = 'last_name';
    private const TEST_META_LAST_NAME_FIELD_VALUE = 'Doe';

    // Attachment field
    private const TEST_ATTACHMENT_FIELD_NAME = 'avatar_id';
    private const TEST_ATTACHMENT_ID = 456;

    private FormConfigManagerInterface|MockObject $formConfigManagerMock;
    private FormFactory|MockObject $formFactoryMock;
    private StringFilterInterface|MockObject $stringFilterMock;
    private UserApiInterface|MockObject $userApiMock;
    private TemporaryFileRepositoryInterface|MockObject $tempFileRepositoryMock;
    private SaveProfileFormUseCase $useCase;

    protected function setUp(): void
    {
        $this->formConfigManagerMock = $this->createMock(FormConfigManagerInterface::class);
        $this->formFactoryMock = $this->createMock(FormFactory::class);
        $this->stringFilterMock = $this->createMock(StringFilterInterface::class);
        $this->userApiMock = $this->createMock(UserApiInterface::class);
        $this->tempFileRepositoryMock = $this->createMock(TemporaryFileRepositoryInterface::class);

        $this->useCase = new SaveProfileFormUseCase(
            $this->formConfigManagerMock,
            $this->formFactoryMock,
            $this->stringFilterMock,
            $this->userApiMock,
            $this->tempFileRepositoryMock
        );
    }

    public function testExecuteSuccessfullySavesProfile(): void
    {
        // Arrange
        $fieldsWithValues = [
            self::TEST_CORE_EMAIL_FIELD_NAME => self::TEST_CORE_EMAIL_FIELD_VALUE,
            self::TEST_CORE_DISPLAY_NAME_FIELD_NAME => self::TEST_CORE_DISPLAY_NAME_FIELD_VALUE,
            self::TEST_META_FIRST_NAME_FIELD_NAME => self::TEST_META_FIRST_NAME_FIELD_VALUE,
            self::TEST_META_LAST_NAME_FIELD_NAME => self::TEST_META_LAST_NAME_FIELD_VALUE,
            self::TEST_ATTACHMENT_FIELD_NAME => self::TEST_ATTACHMENT_ID,
            self::TEST_CORE_PASS_FIELD_NAME => self::TEST_CORE_PASS_FIELD_VALUE,
        ];
        $command = new SaveProfileFormCommand(self::TEST_FORM_TYPE, $fieldsWithValues);

        $formConfigMock = $this->createMock(FormConfig::class);
        $formMock = $this->createMock(FormInterface::class);

        // Mock fields for FormConfig::getFields()
        $fieldMocks = $this->createFieldMocks($fieldsWithValues);

        $this->formConfigManagerMock
            ->expects($this->once())
            ->method('load')
            ->with(self::TEST_FORM_TYPE)
            ->willReturn($formConfigMock);

        $formConfigMock->expects($this->once())
            ->method('getFields')
            ->willReturn($fieldMocks); // Return an array of mock fields for iteration

        // Expect updateFieldValue to be called for each field
        foreach ($fieldsWithValues as $fieldName => $fieldValue) {
            $formConfigMock->expects($this->atLeastOnce())
                ->method('updateFieldValue');
        }

        // unslash будет вызван для каждого значения, возвращаем то же значение, что и получили
        $this->stringFilterMock->method('unslash')
            ->willReturnCallback(fn($value) => $value);

        $this->formFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($formConfigMock)
            ->willReturn($formMock);

        $formMock->expects($this->once())
            ->method('validate')
            ->willReturn(true);

        $this->userApiMock
            ->expects($this->once())
            ->method('getCurrentUserId')
            ->willReturn(self::TEST_USER_ID);

        $formMock->expects($this->once())
            ->method('getFields')
            ->willReturn($this->createFieldMocksWithValues($fieldsWithValues));

        // Expected core data for updateUser
        $expectedCoreData = [
            'ID' => self::TEST_USER_ID,
            self::TEST_CORE_EMAIL_FIELD_NAME => self::TEST_CORE_EMAIL_FIELD_VALUE,
            self::TEST_CORE_DISPLAY_NAME_FIELD_NAME => self::TEST_CORE_DISPLAY_NAME_FIELD_VALUE,
            self::TEST_CORE_PASS_FIELD_NAME => self::TEST_CORE_PASS_FIELD_VALUE,
        ];
        $this->userApiMock
            ->expects($this->once())
            ->method('updateUser')
            ->with($expectedCoreData);

        // Expected meta data for updateUserMeta
        $this->userApiMock
            ->expects($this->exactly(3)) // first_name, last_name, and avatar_id
            ->method('updateUserMeta')
            ->willReturnMap([
                [self::TEST_USER_ID, self::TEST_META_FIRST_NAME_FIELD_NAME, self::TEST_META_FIRST_NAME_FIELD_VALUE, true],
                [self::TEST_USER_ID, self::TEST_META_LAST_NAME_FIELD_NAME, self::TEST_META_LAST_NAME_FIELD_VALUE, true],
                [self::TEST_USER_ID, self::TEST_ATTACHMENT_FIELD_NAME, self::TEST_ATTACHMENT_ID, true],
            ]);

        $this->tempFileRepositoryMock
            ->expects($this->once())
            ->method('remove')
            ->with([self::TEST_ATTACHMENT_ID]);

        // Act
        $this->useCase->execute($command);

        // Assert - no exception should be thrown
    }

    public function testExecuteThrowsExceptionWhenFormConfigNotFound(): void
    {
        // Arrange
        $command = new SaveProfileFormCommand(self::TEST_FORM_TYPE, []);

        $this->formConfigManagerMock
            ->expects($this->once())
            ->method('load')
            ->with(self::TEST_FORM_TYPE)
            ->willReturn(null);

        $this->stringFilterMock
            ->expects($this->once())
            ->method('translate')
            ->with('Form configuration not found.')
            ->willReturn('Form configuration not found.');

        // Assert
        $this->expectException(UspException::class);
        $this->expectExceptionMessage('Form configuration not found.');
        $this->expectExceptionCode(404);

        // Act
        $this->useCase->execute($command);
    }

    public function testExecuteThrowsExceptionWhenValidationFails(): void
    {
        // Arrange
        $fieldsWithValues = [self::TEST_CORE_EMAIL_FIELD_NAME => 'invalid-email'];
        $command = new SaveProfileFormCommand(self::TEST_FORM_TYPE, $fieldsWithValues);

        $formConfigMock = $this->createMock(FormConfig::class);
        $formMock = $this->createMock(FormInterface::class);
        $validationErrors = ['user_email' => ['Invalid email format.']];

        $this->formConfigManagerMock->method('load')->willReturn($formConfigMock);
        $formConfigMock->method('getFields')->willReturn($this->createFieldMocks($fieldsWithValues));
        $formConfigMock->method('updateFieldValue');
        $this->stringFilterMock->method('unslash')->willReturnArgument(0);
        $this->formFactoryMock->method('create')->willReturn($formMock);

        $formMock->expects($this->once())
            ->method('validate')
            ->willReturn(false);

        $formMock->expects($this->once())
            ->method('getErrors')
            ->willReturn($validationErrors);

        $this->stringFilterMock
            ->expects($this->once())
            ->method('translate')
            ->with('Validation error. Please check the fields.')
            ->willReturn('Validation error. Please check the fields.');

        // Assert
        $this->expectException(UspException::class);
        $this->expectExceptionMessage('Validation error. Please check the fields.');
        $this->expectExceptionCode(422);
        $this->expectExceptionObject(new UspException(
            'Validation error. Please check the fields.',
            422,
            ['errors' => $validationErrors]
        ));

        // Act
        $this->useCase->execute($command);
    }

    public function testExecuteThrowsExceptionWhenUserNotLoggedIn(): void
    {
        // Arrange
        $command = new SaveProfileFormCommand(self::TEST_FORM_TYPE, []);

        $formConfigMock = $this->createMock(FormConfig::class);
        $formMock = $this->createMock(FormInterface::class);

        $this->formConfigManagerMock->method('load')->willReturn($formConfigMock);
        $formConfigMock->method('getFields')->willReturn([]); // No fields needed for this path
        $this->formFactoryMock->method('create')->willReturn($formMock);
        $formMock->method('validate')->willReturn(true);

        $this->userApiMock
            ->expects($this->once())
            ->method('getCurrentUserId')
            ->willReturn(0); // Simulate not logged in

        $this->stringFilterMock
            ->expects($this->once())
            ->method('translate')
            ->with('You must be logged in to save the profile.')
            ->willReturn('You must be logged in to save the profile.');

        // Assert
        $this->expectException(UspException::class);
        $this->expectExceptionMessage('You must be logged in to save the profile.');
        $this->expectExceptionCode(401);

        // Act
        $this->useCase->execute($command);
    }

    public function testExecuteIgnoresEmptyUserPassField(): void
    {
        // Arrange
        $fieldsWithValues = [
            self::TEST_CORE_EMAIL_FIELD_NAME => self::TEST_CORE_EMAIL_FIELD_VALUE,
            self::TEST_CORE_PASS_FIELD_NAME => '', // Empty password
        ];
        $command = new SaveProfileFormCommand(self::TEST_FORM_TYPE, $fieldsWithValues);

        $formConfigMock = $this->createMock(FormConfig::class);
        $formMock = $this->createMock(FormInterface::class);

        $this->formConfigManagerMock->method('load')->willReturn($formConfigMock);
        $formConfigMock->method('getFields')->willReturn($this->createFieldMocks($fieldsWithValues));
        $formConfigMock->method('updateFieldValue');
        $this->stringFilterMock->method('unslash')->willReturnArgument(0);
        $this->formFactoryMock->method('create')->willReturn($formMock);
        $formMock->method('validate')->willReturn(true);
        $this->userApiMock->method('getCurrentUserId')->willReturn(self::TEST_USER_ID);
        $formMock->method('getFields')->willReturn($this->createFieldMocksWithValues($fieldsWithValues));

        // Expect updateUser to be called, but without the user_pass field
        $expectedCoreData = [
            'ID' => self::TEST_USER_ID,
            self::TEST_CORE_EMAIL_FIELD_NAME => self::TEST_CORE_EMAIL_FIELD_VALUE,
        ];
        $this->userApiMock
            ->expects($this->once())
            ->method('updateUser')
            ->with($expectedCoreData);

        $this->userApiMock->expects($this->never())->method('updateUserMeta');
        $this->tempFileRepositoryMock->expects($this->never())->method('remove');

        // Act
        $this->useCase->execute($command);

        // Assert - no exception should be thrown
    }

    /**
     * Helper to create mock FieldInterface objects for FormConfig::getFields()
     * when iterating over field names.
     *
     * @param array $fieldNames
     * @return array<string, FieldInterface|MockObject>
     */
    private function createFieldMocks(array $fieldNames): array
    {
        $mocks = [];
        foreach (array_keys($fieldNames) as $name) {
            $fieldMock = $this->createMock(FieldInterface::class);
            $fieldMock->method('getName')->willReturn($name);
            $mocks[$name] = $fieldMock;
        }
        return $mocks;
    }

    /**
     * Helper to create mock FieldInterface objects with values for FormInterface::getFields()
     * after validation.
     *
     * @param array $fieldsWithValues
     * @return array<FieldInterface|MockObject>
     */
    private function createFieldMocksWithValues(array $fieldsWithValues): array
    {
        $mocks = [];
        foreach ($fieldsWithValues as $name => $value) {
            $fieldMock = $this->createMock(FieldInterface::class);
            $fieldMock->method('getName')->willReturn($name);
            $fieldMock->method('getValue')->willReturn($value);
            $mocks[] = $fieldMock;
        }
        return $mocks;
    }
}