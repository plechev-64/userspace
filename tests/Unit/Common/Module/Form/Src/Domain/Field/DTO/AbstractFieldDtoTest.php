<?php

namespace UserSpace\Tests\Unit\Common\Module\Form\Src\Domain\Field\DTO;

use PHPUnit\Framework\TestCase;

class AbstractFieldDtoTest extends TestCase
{
    /**
     * @dataProvider propertyProvider
     * Тестирует, что конструктор корректно присваивает свойства из массива config.
     * @param string $property
     * @param mixed $value
     */
    public function testConstructorAssignsPropertiesCorrectly(string $property, mixed $value): void
    {
        $config = [$property => $value];
        $dto = new ConcreteFieldDto('test_field', $config);
        $this->assertEquals($value, $dto->{$property});
    }

    /**
     * @dataProvider defaultValueProvider
     * Тестирует, что конструктор корректно присваивает значения по умолчанию, если они не указаны в config.
     * @param string $property
     * @param mixed $expectedDefaultValue
     */
    public function testConstructorAssignsDefaultValues(string $property, mixed $expectedDefaultValue): void
    {
        $dto = new ConcreteFieldDto('default_field', []); // Пустой config
        $this->assertEquals($expectedDefaultValue, $dto->{$property});
    }

    /**
     * Тестирует, что метод toArray() корректно преобразует DTO в массив.
     */
    public function testToArrayMethod(): void
    {
        $name = 'array_field';
        $config = [
            'label' => 'Array Label',
            'value' => 123,
            'description' => 'Array Description',
            'rules' => ['min' => 10],
        ];

        $dto = new ConcreteFieldDto($name, $config);
        $arrayRepresentation = $dto->toArray();

        $expectedArray = [
            'name' => $name,
            'type' => 'concrete_type',
            'label' => $config['label'],
            'value' => $config['value'],
            'description' => $config['description'],
            'dependency' => null, // Не было в config, поэтому null
            'rules' => $config['rules'],
            'attributes' => [], // Не было в config, поэтому пустой массив
        ];

        $this->assertEquals($expectedArray, $arrayRepresentation);
    }

    public static function propertyProvider(): array
    {
        return [
            'label'       => ['label', 'Test Label'],
            'value'       => ['value', 'test_value'],
            'description' => ['description', 'Test Description'],
            'dependency'  => ['dependency', ['field' => 'other_field', 'value' => 'some_value']],
            'rules'       => ['rules', ['required' => true]],
            'attributes'  => ['attributes', ['class' => 'test-class']],
        ];
    }

    public static function defaultValueProvider(): array
    {
        return [
            'label'       => ['label', ''],
            'value'       => ['value', null],
            'description' => ['description', null],
            'dependency'  => ['dependency', null],
            'rules'       => ['rules', []],
            'attributes'  => ['attributes', []],
        ];
    }
}