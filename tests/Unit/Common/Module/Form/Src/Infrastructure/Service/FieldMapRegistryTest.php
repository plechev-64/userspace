<?php

namespace UserSpace\Tests\Unit\Common\Module\Form\Src\Infrastructure\Service;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use UserSpace\Common\Module\Form\Src\Domain\Field\FieldType;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\EmailFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\TextFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\Email;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\Text;
use UserSpace\Common\Module\Form\Src\Infrastructure\Service\FieldMap;
use UserSpace\Common\Module\Form\Src\Infrastructure\Service\FieldMapRegistry;

class FieldMapRegistryTest extends TestCase
{
    private const TEST_TYPE_TEXT = 'text';
    private const TEST_TYPE_EMAIL = 'email';
    private const TEST_TYPE_UNKNOWN = 'unknown';

    private array $initialMap;

    protected function setUp(): void
    {
        $this->initialMap = [
            self::TEST_TYPE_TEXT => new FieldMap(Text::class, TextFieldDto::class),
        ];
    }

    public function testConstructWithInitialMap(): void
    {
        // Arrange & Act
        $registry = new FieldMapRegistry($this->initialMap);

        // Assert
        $this->assertTrue($registry->has(self::TEST_TYPE_TEXT));
        $this->assertCount(1, $registry->getMap());
    }

    public function testGetClassSuccessfully(): void
    {
        // Arrange
        $registry = new FieldMapRegistry($this->initialMap);

        // Act
        $class = $registry->getClass(self::TEST_TYPE_TEXT);

        // Assert
        $this->assertSame(Text::class, $class);
    }

    public function testGetClassForUnknownTypeThrowsException(): void
    {
        // Arrange
        $registry = new FieldMapRegistry($this->initialMap);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Тип поля '" . self::TEST_TYPE_UNKNOWN . "' не поддерживается.");

        // Act
        $registry->getClass(self::TEST_TYPE_UNKNOWN);
    }

    public function testGetDtoClassSuccessfully(): void
    {
        // Arrange
        $registry = new FieldMapRegistry($this->initialMap);

        // Act
        $dtoClass = $registry->getDtoClass(self::TEST_TYPE_TEXT);

        // Assert
        $this->assertSame(TextFieldDto::class, $dtoClass);
    }

    public function testHasReturnsCorrectBoolean(): void
    {
        // Arrange
        $registry = new FieldMapRegistry($this->initialMap);

        // Assert
        $this->assertTrue($registry->has(self::TEST_TYPE_TEXT));
        $this->assertFalse($registry->has(self::TEST_TYPE_UNKNOWN));
    }

    public function testRegisterAddsNewFieldMap(): void
    {
        // Arrange
        $registry = new FieldMapRegistry($this->initialMap);
        $emailFieldMap = new FieldMap(Email::class, EmailFieldDto::class);

        // Act
        $registry->register(self::TEST_TYPE_EMAIL, $emailFieldMap);

        // Assert
        $this->assertTrue($registry->has(self::TEST_TYPE_EMAIL));
        $this->assertCount(2, $registry->getMap());
        $this->assertSame(Email::class, $registry->getClass(self::TEST_TYPE_EMAIL));
    }

    public function testToArrayReturnsCorrectStructure(): void
    {
        // Arrange
        $registry = new FieldMapRegistry($this->initialMap);
        $expectedArray = [
            self::TEST_TYPE_TEXT => [
                'class' => Text::class,
                'dto' => TextFieldDto::class,
            ],
        ];

        // Act
        $resultArray = $registry->toArray();

        // Assert
        $this->assertEquals($expectedArray, $resultArray);
    }
}