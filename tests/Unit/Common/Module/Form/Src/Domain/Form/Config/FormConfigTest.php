<?php

namespace UserSpace\Tests\Unit\Common\Module\Form\Src\Domain\Form\Config;

use LogicException;
use PHPUnit\Framework\TestCase;
use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfig;

class FormConfigTest extends TestCase
{
    private const TEST_SECTION_1_TITLE = 'User Information';
    private const TEST_BLOCK_1_TITLE = 'Personal Data';
    private const TEST_FIELD_1_NAME = 'first_name';
    private const TEST_FIELD_1_CONFIG = ['type' => 'text', 'label' => 'First Name'];
    private const TEST_FIELD_2_NAME = 'last_name';
    private const TEST_FIELD_2_CONFIG = ['type' => 'text', 'label' => 'Last Name'];

    private const TEST_SECTION_2_TITLE = 'Account Settings';
    private const TEST_BLOCK_2_TITLE = 'Credentials';
    private const TEST_FIELD_3_NAME = 'email';
    private const TEST_FIELD_3_CONFIG = ['type' => 'email', 'label' => 'Email Address'];

    public function testFluentInterfaceWorksCorrectly(): void
    {
        // Arrange
        $formConfig = new FormConfig();

        // Act
        $formConfig
            ->addSection(self::TEST_SECTION_1_TITLE)
            ->addBlock(self::TEST_BLOCK_1_TITLE)
            ->addField(self::TEST_FIELD_1_NAME, self::TEST_FIELD_1_CONFIG);

        // Assert
        $sections = $formConfig->getSections();
        $this->assertCount(1, $sections);
        $this->assertEquals(self::TEST_SECTION_1_TITLE, $sections[0]->getTitle());

        $blocks = $sections[0]->getBlocks();
        $this->assertCount(1, $blocks);
        $this->assertEquals(self::TEST_BLOCK_1_TITLE, $blocks[0]->getTitle());

        $fields = $blocks[0]->getFields();
        $this->assertCount(1, $fields);
        $this->assertArrayHasKey(self::TEST_FIELD_1_NAME, $fields);
        $this->assertEquals(self::TEST_FIELD_1_CONFIG, $fields[self::TEST_FIELD_1_NAME]);
    }

    public function testAddBlockWithoutSectionThrowsException(): void
    {
        // Assert
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot add a block without a section. Call addSection() first.');

        // Arrange & Act
        $formConfig = new FormConfig();
        $formConfig->addBlock('Some Block');
    }

    public function testAddFieldWithoutBlockThrowsException(): void
    {
        // Assert
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot add a field without a block. Call addBlock() first.');

        // Arrange & Act
        $formConfig = new FormConfig();
        $formConfig->addSection('Some Section')
            ->addField('some_field', []);
    }

    public function testFromArrayBuildsCorrectObject(): void
    {
        // Arrange
        $configData = $this->getTestConfigAsArray();

        // Act
        $formConfig = FormConfig::fromArray($configData);

        // Assert
        $this->assertEquals($configData, $formConfig->toArray());
    }

    public function testToArrayReturnsCorrectStructure(): void
    {
        // Arrange
        $formConfig = $this->createTestFormConfig();
        $expectedArray = $this->getTestConfigAsArray();

        // Act
        $resultArray = $formConfig->toArray();

        // Assert
        $this->assertEquals($expectedArray, $resultArray);
    }

    public function testUpdateFieldValueSuccessfully(): void
    {
        // Arrange
        $formConfig = $this->createTestFormConfig();
        $newValue = 'new_value@test.com';

        // Act
        $result = $formConfig->updateFieldValue(self::TEST_FIELD_3_NAME, $newValue);

        // Assert
        $this->assertTrue($result);
        $fields = $formConfig->getFields();
        $this->assertEquals($newValue, $fields[self::TEST_FIELD_3_NAME]['value']);
    }

    public function testUpdateFieldValueForNonExistentField(): void
    {
        // Arrange
        $formConfig = $this->createTestFormConfig();

        // Act
        $result = $formConfig->updateFieldValue('non_existent_field', 'some_value');

        // Assert
        $this->assertFalse($result);
    }

    public function testRemoveFieldSuccessfully(): void
    {
        // Arrange
        $formConfig = $this->createTestFormConfig();

        // Act
        $result = $formConfig->removeField(self::TEST_FIELD_2_NAME);

        // Assert
        $this->assertTrue($result);
        $fields = $formConfig->getFields();
        $this->assertArrayNotHasKey(self::TEST_FIELD_2_NAME, $fields);
        $this->assertCount(2, $fields);
    }

    public function testRemoveFieldForNonExistentField(): void
    {
        // Arrange
        $formConfig = $this->createTestFormConfig();

        // Act
        $result = $formConfig->removeField('non_existent_field');

        // Assert
        $this->assertFalse($result);
        $this->assertCount(3, $formConfig->getFields());
    }

    public function testGetFieldsReturnsFlatArrayOfAllFields(): void
    {
        // Arrange
        $formConfig = $this->createTestFormConfig();

        // Act
        $fields = $formConfig->getFields();

        // Assert
        $this->assertCount(3, $fields);
        $this->assertArrayHasKey(self::TEST_FIELD_1_NAME, $fields);
        $this->assertArrayHasKey(self::TEST_FIELD_2_NAME, $fields);
        $this->assertArrayHasKey(self::TEST_FIELD_3_NAME, $fields);
    }

    private function createTestFormConfig(): FormConfig
    {
        return (new FormConfig())
            ->addSection(self::TEST_SECTION_1_TITLE)
            ->addBlock(self::TEST_BLOCK_1_TITLE)
            ->addField(self::TEST_FIELD_1_NAME, self::TEST_FIELD_1_CONFIG)
            ->addField(self::TEST_FIELD_2_NAME, self::TEST_FIELD_2_CONFIG)
            ->addSection(self::TEST_SECTION_2_TITLE)
            ->addBlock(self::TEST_BLOCK_2_TITLE)
            ->addField(self::TEST_FIELD_3_NAME, self::TEST_FIELD_3_CONFIG);
    }

    private function getTestConfigAsArray(): array
    {
        return [
            'sections' => [
                [
                    'title' => self::TEST_SECTION_1_TITLE,
                    'blocks' => [
                        [
                            'title' => self::TEST_BLOCK_1_TITLE,
                            'fields' => [
                                self::TEST_FIELD_1_NAME => self::TEST_FIELD_1_CONFIG,
                                self::TEST_FIELD_2_NAME => self::TEST_FIELD_2_CONFIG,
                            ],
                        ],
                    ],
                ],
                [
                    'title' => self::TEST_SECTION_2_TITLE,
                    'blocks' => [
                        [
                            'title' => self::TEST_BLOCK_2_TITLE,
                            'fields' => [
                                self::TEST_FIELD_3_NAME => self::TEST_FIELD_3_CONFIG,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}