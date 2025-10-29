<?php

namespace UserSpace\Tests\Unit\Common\Module\Form\Src\Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\TextFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\Text;
use UserSpace\Common\Module\Form\Src\Infrastructure\Service\FieldMap;

class FieldMapTest extends TestCase
{
    private const TEST_FIELD_CLASS = Text::class;
    private const TEST_DTO_CLASS = TextFieldDto::class;

    public function testGettersReturnCorrectClassStrings(): void
    {
        // Arrange
        $fieldMap = new FieldMap(self::TEST_FIELD_CLASS, self::TEST_DTO_CLASS);

        // Act
        $fieldClass = $fieldMap->getFieldClass();
        $dtoClass = $fieldMap->getDtoClass();

        // Assert
        $this->assertSame(self::TEST_FIELD_CLASS, $fieldClass);
        $this->assertSame(self::TEST_DTO_CLASS, $dtoClass);
    }

    public function testToArrayReturnsCorrectStructure(): void
    {
        // Arrange
        $fieldMap = new FieldMap(self::TEST_FIELD_CLASS, self::TEST_DTO_CLASS);
        $expectedArray = [
            'class' => self::TEST_FIELD_CLASS,
            'dto' => self::TEST_DTO_CLASS,
        ];

        // Act
        $resultArray = $fieldMap->toArray();

        // Assert
        $this->assertEquals($expectedArray, $resultArray);
    }
}