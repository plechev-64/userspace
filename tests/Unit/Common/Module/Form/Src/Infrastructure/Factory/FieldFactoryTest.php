<?php

namespace UserSpace\Tests\Unit\Common\Module\Form\Src\Infrastructure\Factory;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use UserSpace\Common\Module\Form\Src\Domain\Field\DTO\AbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Domain\Field\FieldInterface;
use UserSpace\Common\Module\Form\Src\Domain\Service\FieldMapRegistryInterface;
use UserSpace\Common\Module\Form\Src\Infrastructure\Factory\FieldFactory;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\TextFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\Text;
use UserSpace\Core\Container\ContainerInterface;

class FieldFactoryTest extends TestCase
{
    private const TEST_FIELD_NAME = 'test_field';
    private const TEST_FIELD_TYPE = 'text';
    private const TEST_FIELD_LABEL = 'Test Label';

    private ContainerInterface|MockObject $containerMock;
    private FieldMapRegistryInterface|MockObject $fieldMapperMock;
    private FieldFactory $fieldFactory;

    protected function setUp(): void
    {
        $this->containerMock = $this->createMock(ContainerInterface::class);
        $this->fieldMapperMock = $this->createMock(FieldMapRegistryInterface::class);

        $this->fieldFactory = new FieldFactory(
            $this->containerMock,
            $this->fieldMapperMock
        );
    }

    public function testCreateFromDtoSuccessfullyCreatesAndInitializesField(): void
    {
        // Arrange
        $fieldDto = new TextFieldDto(self::TEST_FIELD_NAME, ['label' => self::TEST_FIELD_LABEL]);
        $fieldMock = $this->createMock(FieldInterface::class);

        $this->fieldMapperMock->expects($this->once())
            ->method('getClass')
            ->with(self::TEST_FIELD_TYPE)
            ->willReturn(Text::class);

        $this->containerMock->expects($this->once())
            ->method('build')
            ->with(Text::class)
            ->willReturn($fieldMock);

        $fieldMock->expects($this->once())
            ->method('init')
            ->with($fieldDto);

        // Act
        $result = $this->fieldFactory->createFromDto($fieldDto);

        // Assert
        $this->assertSame($fieldMock, $result);
    }

    public function testCreateFromConfigSuccessfullyCreatesAndInitializesField(): void
    {
        // Arrange
        $fieldConfig = [
            'type' => self::TEST_FIELD_TYPE,
            'label' => self::TEST_FIELD_LABEL,
            'rules' => ['required' => true],
        ];
        $fieldMock = $this->createMock(FieldInterface::class);

        $this->fieldMapperMock->expects($this->once())
            ->method('getDtoClass')
            ->with(self::TEST_FIELD_TYPE)
            ->willReturn(TextFieldDto::class);

        // Этот мок нужен для внутреннего вызова createFromDto
        $this->fieldMapperMock->expects($this->once())
            ->method('getClass')
            ->with(self::TEST_FIELD_TYPE)
            ->willReturn(Text::class);

        $this->containerMock->expects($this->once())
            ->method('build')
            ->with(Text::class)
            ->willReturn($fieldMock);

        // Проверяем, что init будет вызван с корректно созданным DTO
        $fieldMock->expects($this->once())
            ->method('init')
            ->with($this->callback(function (AbstractFieldDto $dto) use ($fieldConfig) {
                $this->assertInstanceOf(TextFieldDto::class, $dto);
                $this->assertEquals(self::TEST_FIELD_NAME, $dto->name);
                $this->assertEquals($fieldConfig['label'], $dto->label);
                $this->assertEquals($fieldConfig['rules'], $dto->rules);
                return true;
            }));

        // Act
        $result = $this->fieldFactory->createFromConfig(self::TEST_FIELD_NAME, $fieldConfig);

        // Assert
        $this->assertSame($fieldMock, $result);
    }
}