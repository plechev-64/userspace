<?php

namespace UserSpace\Tests\Unit\Common\Module\Form\Src\Infrastructure\Form\Config;

use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfig;
use UserSpace\Common\Module\Form\Src\Infrastructure\Form\Config\FormConfigBuilder;
use UserSpace\Core\String\StringFilterInterface;

class FormConfigBuilderTest extends TestCase
{
    private const TEST_SECTION_ID_1 = 'section_1';
    private const TEST_SECTION_TITLE_1 = 'User Information';
    private const TEST_BLOCK_ID_1 = 'block_1';
    private const TEST_BLOCK_TITLE_1 = 'Personal Data';
    private const TEST_FIELD_NAME_1 = 'first_name';
    private const TEST_FIELD_CONFIG_1 = ['type' => 'text', 'label' => 'First Name'];

    private const TEST_SECTION_ID_2 = 'section_2';
    private const TEST_SECTION_TITLE_2 = 'Account Settings';
    private const TEST_BLOCK_ID_2 = 'block_2';
    private const TEST_BLOCK_TITLE_2 = 'Credentials';
    private const TEST_FIELD_NAME_2 = 'email';
    private const TEST_FIELD_CONFIG_2 = ['type' => 'email', 'label' => 'Email Address'];

    private const TEST_AVAILABLE_FIELD_NAME = 'available_field';
    private const TEST_AVAILABLE_FIELD_CONFIG = ['type' => 'text', 'label' => 'Available Field'];

    private StringFilterInterface|MockObject $stringFilterMock;
    private FormConfigBuilder $builder;

    protected function setUp(): void
    {
        $this->stringFilterMock = $this->createMock(StringFilterInterface::class);
        // По умолчанию мокаем методы StringFilterInterface, чтобы они возвращали переданное значение
        $this->stringFilterMock->method('escAttr')->willReturnArgument(0);
        $this->stringFilterMock->method('escHtml')->willReturnArgument(0);
        $this->stringFilterMock->method('translate')->willReturnArgument(0);

        $this->builder = new FormConfigBuilder($this->stringFilterMock);
    }

    public function testAddSectionSuccessfully(): void
    {
        // Act
        $this->builder->addSection(self::TEST_SECTION_ID_1, self::TEST_SECTION_TITLE_1);

        // Assert
        $config = $this->builder->build()->toArray();
        $this->assertArrayHasKey('sections', $config);
        $this->assertCount(1, $config['sections']);
        $this->assertEquals(self::TEST_SECTION_TITLE_1, $config['sections'][0]['title']);
    }

    public function testAddSectionThrowsExceptionIfIdExists(): void
    {
        // Arrange
        $this->builder->addSection(self::TEST_SECTION_ID_1, self::TEST_SECTION_TITLE_1);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Секция с ID '" . self::TEST_SECTION_ID_1 . "' уже существует.");

        // Act
        $this->builder->addSection(self::TEST_SECTION_ID_1, 'Another Title');
    }

    public function testAddBlockSuccessfully(): void
    {
        // Arrange
        $this->builder->addSection(self::TEST_SECTION_ID_1, self::TEST_SECTION_TITLE_1);

        // Act
        $this->builder->addBlock(self::TEST_BLOCK_ID_1, self::TEST_BLOCK_TITLE_1);

        // Assert
        $config = $this->builder->build()->toArray();
        $this->assertArrayHasKey('sections', $config);
        $this->assertCount(1, $config['sections']);
        $this->assertArrayHasKey('blocks', $config['sections'][0]);
        $this->assertCount(1, $config['sections'][0]['blocks']);
        $this->assertEquals(self::TEST_BLOCK_TITLE_1, $config['sections'][0]['blocks'][0]['title']);
    }

    public function testAddBlockThrowsExceptionIfNoSection(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Необходимо сначала добавить секцию с помощью addSection().');

        // Act
        $this->builder->addBlock(self::TEST_BLOCK_ID_1, self::TEST_BLOCK_TITLE_1);
    }

    public function testAddBlockThrowsExceptionIfIdExistsInCurrentSection(): void
    {
        // Arrange
        $this->builder->addSection(self::TEST_SECTION_ID_1, self::TEST_SECTION_TITLE_1)
            ->addBlock(self::TEST_BLOCK_ID_1, self::TEST_BLOCK_TITLE_1);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Блок с ID '" . self::TEST_BLOCK_ID_1 . "' уже существует в текущей секции.");

        // Act
        $this->builder->addBlock(self::TEST_BLOCK_ID_1, 'Another Block Title');
    }

    public function testAddFieldSuccessfully(): void
    {
        // Arrange
        $this->builder->addSection(self::TEST_SECTION_ID_1, self::TEST_SECTION_TITLE_1)
            ->addBlock(self::TEST_BLOCK_ID_1, self::TEST_BLOCK_TITLE_1);

        // Act
        $this->builder->addField(self::TEST_FIELD_NAME_1, self::TEST_FIELD_CONFIG_1);

        // Assert
        $config = $this->builder->build()->toArray();
        $this->assertArrayHasKey('sections', $config);
        $this->assertCount(1, $config['sections']);
        $this->assertArrayHasKey('blocks', $config['sections'][0]);
        $this->assertCount(1, $config['sections'][0]['blocks']);
        $this->assertArrayHasKey('fields', $config['sections'][0]['blocks'][0]);
        $this->assertArrayHasKey(self::TEST_FIELD_NAME_1, $config['sections'][0]['blocks'][0]['fields']);
        $this->assertEquals(self::TEST_FIELD_CONFIG_1, $config['sections'][0]['blocks'][0]['fields'][self::TEST_FIELD_NAME_1]);
    }

    public function testAddFieldThrowsExceptionIfNoBlock(): void
    {
        // Arrange
        $this->builder->addSection(self::TEST_SECTION_ID_1, self::TEST_SECTION_TITLE_1);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Необходимо сначала добавить блок с помощью addBlock().');

        // Act
        $this->builder->addField(self::TEST_FIELD_NAME_1, self::TEST_FIELD_CONFIG_1);
    }

    public function testBuildReturnsCorrectFormConfig(): void
    {
        // Arrange
        $this->builder->addSection(self::TEST_SECTION_ID_1, self::TEST_SECTION_TITLE_1)
            ->addBlock(self::TEST_BLOCK_ID_1, self::TEST_BLOCK_TITLE_1)
            ->addField(self::TEST_FIELD_NAME_1, self::TEST_FIELD_CONFIG_1)
            ->addSection(self::TEST_SECTION_ID_2, self::TEST_SECTION_TITLE_2)
            ->addBlock(self::TEST_BLOCK_ID_2, self::TEST_BLOCK_TITLE_2)
            ->addField(self::TEST_FIELD_NAME_2, self::TEST_FIELD_CONFIG_2);

        // Act
        $formConfig = $this->builder->build();

        // Assert
        $this->assertInstanceOf(FormConfig::class, $formConfig);
        $sections = $formConfig->getSections();
        $this->assertCount(2, $sections);
        $this->assertEquals(self::TEST_SECTION_TITLE_1, $sections[0]->getTitle());
        $this->assertEquals(self::TEST_SECTION_TITLE_2, $sections[1]->getTitle());

        $blocks1 = $sections[0]->getBlocks();
        $this->assertCount(1, $blocks1);
        $this->assertEquals(self::TEST_BLOCK_TITLE_1, $blocks1[0]->getTitle());
        $fields1 = $blocks1[0]->getFields();
        $this->assertArrayHasKey(self::TEST_FIELD_NAME_1, $fields1);
        $this->assertEquals(self::TEST_FIELD_CONFIG_1, $fields1[self::TEST_FIELD_NAME_1]);

        $blocks2 = $sections[1]->getBlocks();
        $this->assertCount(1, $blocks2);
        $this->assertEquals(self::TEST_BLOCK_TITLE_2, $blocks2[0]->getTitle());
        $fields2 = $blocks2[0]->getFields();
        $this->assertArrayHasKey(self::TEST_FIELD_NAME_2, $fields2);
        $this->assertEquals(self::TEST_FIELD_CONFIG_2, $fields2[self::TEST_FIELD_NAME_2]);
    }

    public function testSetAvailableFields(): void
    {
        // Arrange
        $availableFields = [
            self::TEST_AVAILABLE_FIELD_NAME => self::TEST_AVAILABLE_FIELD_CONFIG,
        ];

        // Act
        $this->builder->setAvailableFields($availableFields);

        // Assert (indirectly through render, as availableFields is private)
        $html = $this->builder->render();
        $this->assertStringContainsString(self::TEST_AVAILABLE_FIELD_NAME, $html);
        $this->assertStringContainsString(self::TEST_AVAILABLE_FIELD_CONFIG['label'], $html);
    }

    public function testResetClearsState(): void
    {
        // Arrange
        $this->builder->addSection(self::TEST_SECTION_ID_1, self::TEST_SECTION_TITLE_1)
            ->addBlock(self::TEST_BLOCK_ID_1, self::TEST_BLOCK_TITLE_1)
            ->addField(self::TEST_FIELD_NAME_1, self::TEST_FIELD_CONFIG_1);

        // Act
        $this->builder->reset();

        // Assert
        $config = $this->builder->build()->toArray();
        $this->assertArrayHasKey('sections', $config);
        $this->assertEmpty($config['sections']);
    }

    public function testLoadExistingFormConfig(): void
    {
        // Arrange
        $existingFormConfig = (new FormConfig())
            ->addSection('Existing Section Title')
            ->addBlock('Existing Block Title')
            ->addField('existing_field', ['type' => 'text', 'label' => 'Existing Field Label']);

        // Act
        $this->builder->load($existingFormConfig);

        // Assert
        $builtConfig = $this->builder->build();
        $sections = $builtConfig->getSections();
        $this->assertCount(1, $sections);
        $this->assertEquals('Existing Section Title', $sections[0]->getTitle());

        $blocks = $sections[0]->getBlocks();
        $this->assertCount(1, $blocks);
        $this->assertEquals('Existing Block Title', $blocks[0]->getTitle());

        $fields = $blocks[0]->getFields();
        $this->assertArrayHasKey('existing_field', $fields);
        $this->assertEquals(['type' => 'text', 'label' => 'Existing Field Label'], $fields['existing_field']);

        // Проверяем, что ID были сгенерированы, так как FormConfig::toArray() их не содержит
        $reflection = new \ReflectionClass($this->builder);
        $configProperty = $reflection->getProperty('config');
        $configProperty->setAccessible(true);
        $internalConfig = $configProperty->getValue($this->builder);

        $this->assertArrayHasKey('section-0', $internalConfig['sections']);
        $this->assertArrayHasKey('block-0', $internalConfig['sections']['section-0']['blocks']);
    }

    public function testLoadEmptyFormConfig(): void
    {
        // Arrange
        $emptyFormConfig = new FormConfig();

        // Act
        $this->builder->load($emptyFormConfig);

        // Assert
        $builtConfig = $this->builder->build();
        $this->assertEmpty($builtConfig->getSections());
    }

    public function testRenderGeneratesCorrectHtml(): void
    {
        // Arrange
        $this->builder->addSection(self::TEST_SECTION_ID_1, self::TEST_SECTION_TITLE_1)
            ->addBlock(self::TEST_BLOCK_ID_1, self::TEST_BLOCK_TITLE_1)
            ->addField(self::TEST_FIELD_NAME_1, self::TEST_FIELD_CONFIG_1);

        $this->builder->setAvailableFields([
            self::TEST_AVAILABLE_FIELD_NAME => self::TEST_AVAILABLE_FIELD_CONFIG,
        ]);

        // Ожидаемые вызовы translate
        $this->stringFilterMock->expects($this->atLeastOnce())
            ->method('translate')
            ->willReturnMap([
                ['Add Block', 'Add Block'],
                ['Delete', 'Delete'],
                ['Untitled Section', 'Untitled Section'],
                ['Untitled Block', 'Untitled Block'],
                ['Add Field', 'Add Field'],
                ['Edit', 'Edit'],
                ['Available Fields', 'Available Fields'],
            ]);

        // Act
        $html = $this->builder->render();

        // Assert
        $this->assertStringContainsString('<div class="usp-form-config-builder" data-usp-form-builder>', $html);
        $this->assertStringContainsString(sprintf('<div class="usp-form-builder-section" data-id="%s">', self::TEST_SECTION_ID_1), $html);
        $this->assertStringContainsString(sprintf('value="%s"', self::TEST_SECTION_TITLE_1), $html);
        $this->assertStringContainsString(sprintf('<div class="usp-form-builder-block" data-id="%s">', self::TEST_BLOCK_ID_1), $html);
        $this->assertStringContainsString(sprintf('value="%s"', self::TEST_BLOCK_TITLE_1), $html);
        $this->assertStringContainsString(sprintf('<div class="usp-form-builder-field" data-name="%s" data-type="%s"', self::TEST_FIELD_NAME_1, self::TEST_FIELD_CONFIG_1['type']), $html);
        $this->assertStringContainsString(sprintf('<span class="field-label">%s</span>', self::TEST_FIELD_CONFIG_1['label']), $html);
        $this->assertStringContainsString(sprintf('<span class="field-type">[%s]</span>', self::TEST_FIELD_CONFIG_1['type']), $html);
        $this->assertStringContainsString('data-action="add-section">Добавить секцию</button>', $html);

        // Проверка панели доступных полей
        $this->assertStringContainsString('<h4>Available Fields</h4>', $html);
        $this->assertStringContainsString(sprintf('<div class="usp-form-builder-field" data-name="%s" data-type="%s"', self::TEST_AVAILABLE_FIELD_NAME, self::TEST_AVAILABLE_FIELD_CONFIG['type']), $html);
    }

    public function testRenderWithoutAvailableFields(): void
    {
        // Arrange
        $this->builder->addSection(self::TEST_SECTION_ID_1, self::TEST_SECTION_TITLE_1);
        // Не вызываем setAvailableFields

        // Act
        $html = $this->builder->render();

        // Assert
        $this->assertStringNotContainsString('usp-form-builder-available-fields', $html);
    }
}