<?php

namespace UserSpace\Tests\Unit\Common\Module\Form\Src\Infrastructure\Factory;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use UserSpace\Common\Module\Form\Src\Domain\Factory\FieldFactoryInterface;
use UserSpace\Common\Module\Form\Src\Domain\Field\FieldInterface;
use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfig;
use UserSpace\Common\Module\Form\Src\Infrastructure\Factory\FormFactory;
use UserSpace\Common\Module\Form\Src\Infrastructure\Form\Form;

class FormFactoryTest extends TestCase
{
    private const TEST_SECTION_1_TITLE = 'User Information';
    private const TEST_BLOCK_1_TITLE = 'Personal Data';
    private const TEST_FIELD_1_NAME = 'first_name';
    private const TEST_FIELD_1_CONFIG = ['type' => 'text', 'label' => 'First Name'];
    private const TEST_FIELD_2_NAME = 'last_name';
    private const TEST_FIELD_2_CONFIG = ['type' => 'text', 'label' => 'Last Name'];

    private FieldFactoryInterface|MockObject $fieldFactoryMock;
    private FormFactory $formFactory;

    protected function setUp(): void
    {
        $this->fieldFactoryMock = $this->createMock(FieldFactoryInterface::class);
        $this->formFactory = new FormFactory($this->fieldFactoryMock);
    }

    public function testCreateSuccessfullyBuildsForm(): void
    {
        // Arrange
        $formConfig = (new FormConfig())
            ->addSection(self::TEST_SECTION_1_TITLE)
            ->addBlock(self::TEST_BLOCK_1_TITLE)
            ->addField(self::TEST_FIELD_1_NAME, self::TEST_FIELD_1_CONFIG)
            ->addField(self::TEST_FIELD_2_NAME, self::TEST_FIELD_2_CONFIG);

        $field1Mock = $this->createMock(FieldInterface::class);
        $field2Mock = $this->createMock(FieldInterface::class);

        $this->fieldFactoryMock->expects($this->exactly(2))
            ->method('createFromConfig')
            ->willReturnMap([
                [self::TEST_FIELD_1_NAME, self::TEST_FIELD_1_CONFIG, $field1Mock],
                [self::TEST_FIELD_2_NAME, self::TEST_FIELD_2_CONFIG, $field2Mock],
            ]);

        // Act
        $form = $this->formFactory->create($formConfig);

        // Assert
        $this->assertInstanceOf(Form::class, $form);

        // Проверяем секции
        $sections = $form->getSections();
        $this->assertCount(1, $sections);
        $this->assertEquals(self::TEST_SECTION_1_TITLE, $sections[0]->getTitle());

        // Проверяем блоки
        $blocks = $sections[0]->getBlocks();
        $this->assertCount(1, $blocks);
        $this->assertEquals(self::TEST_BLOCK_1_TITLE, $blocks[0]->getTitle());

        // Проверяем поля
        $fields = $blocks[0]->getFields();
        $this->assertCount(2, $fields);
        $this->assertSame($field1Mock, $fields[0]);
        $this->assertSame($field2Mock, $fields[1]);
    }

    public function testCreateWithEmptyConfigReturnsEmptyForm(): void
    {
        // Arrange
        $emptyFormConfig = new FormConfig();

        $this->fieldFactoryMock->expects($this->never())
            ->method('createFromConfig');

        // Act
        $form = $this->formFactory->create($emptyFormConfig);

        // Assert
        $this->assertInstanceOf(Form::class, $form);
        $this->assertEmpty($form->getSections());
    }
}