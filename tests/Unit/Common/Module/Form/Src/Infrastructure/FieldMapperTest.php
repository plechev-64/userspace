<?php

namespace UserSpace\Tests\Unit\Common\Module\Form\Src\Infrastructure;

use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;
use UserSpace\Common\Module\Form\Src\Infrastructure\FieldMapper;

class FieldMapperTest extends TestCase
{
    private FieldMapper $fieldMapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fieldMapper = new FieldMapper();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    /**
     * @dataProvider fieldTypeProvider
     * Тестирует, что getClass() и getDtoClass() возвращают правильные классы для всех типов полей.
     * @param string $type
     * @param string $expectedClass
     * @param string $expectedDtoClass
     */
    public function testGetClassAndDtoClassReturnCorrectClasses(string $type, string $expectedClass, string $expectedDtoClass): void
    {
        $this->assertEquals($expectedClass, $this->fieldMapper->getClass($type));
        $this->assertEquals($expectedDtoClass, $this->fieldMapper->getDtoClass($type));
    }

    /**
     * @dataProvider exceptionMethodProvider
     * Тестирует, что методы выбрасывают исключение для несуществующего типа поля.
     * @param string $methodName
     */
    public function testMethodsThrowExceptionForInvalidType(string $methodName): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Тип поля 'non_existent' не поддерживается.");
        $this->fieldMapper->{$methodName}('non_existent');
    }

    /**
     * Тестирует, что has() возвращает true для существующего типа поля.
     */
    public function testHasReturnsTrueForExistingType(): void
    {
        $this->assertTrue($this->fieldMapper->has('boolean'));
    }

    /**
     * Тестирует, что has() возвращает false для несуществующего типа поля.
     */
    public function testHasReturnsFalseForNonExistingType(): void
    {
        $this->assertFalse($this->fieldMapper->has('non_existent'));
    }

    /**
     * Тестирует, что getMap() возвращает полную карту полей.
     */
    public function testGetMapReturnsFullMap(): void
    {
        $map = $this->fieldMapper->getMap();
        $this->assertIsArray($map);

        // Проверяем, что количество элементов в публичной карте совпадает с приватной
        $reflection = new \ReflectionClass($this->fieldMapper);
        $mapProperty = $reflection->getProperty('map');
        $internalMap = $mapProperty->getValue($this->fieldMapper);
        $this->assertCount(count($internalMap), $map);

        // Проверяем, что структура одного из элементов корректна
        $this->assertArrayHasKey('text', $map);
        $this->assertArrayHasKey('class', $map['text']);
        $this->assertArrayHasKey('dto', $map['text']);
        $this->assertTrue(class_exists($map['text']['class']));
        $this->assertTrue(class_exists($map['text']['dto']));
    }

    public static function fieldTypeProvider(): array
    {
        // Динамически генерируем данные для теста из самого класса FieldMapper
        $mapper = new FieldMapper();
        $map = $mapper->getMap();
        $providerData = [];
        foreach ($map as $type => $data) {
            $providerData[$type] = [$type, $data['class'], $data['dto']];
        }
        return $providerData;
    }

    public static function exceptionMethodProvider(): array
    {
        return [
            ['getClass'],
            ['getDtoClass'],
        ];
    }
}