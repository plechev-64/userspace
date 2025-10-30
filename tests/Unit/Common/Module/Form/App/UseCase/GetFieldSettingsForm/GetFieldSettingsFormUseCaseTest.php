<?php

namespace UserSpace\Tests\Unit\Common\Module\Form\App\UseCase\GetFieldSettingsForm;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use UserSpace\Common\Module\Form\App\UseCase\GetFieldSettingsForm\GetFieldSettingsFormCommand;
use UserSpace\Common\Module\Form\App\UseCase\GetFieldSettingsForm\GetFieldSettingsFormUseCase;
use UserSpace\Common\Module\Form\Src\Domain\Field\FieldInterface;
use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfig;
use UserSpace\Common\Module\Form\Src\Domain\Form\FormInterface;
use UserSpace\Common\Module\Form\Src\Domain\Factory\FieldFactoryInterface;
use UserSpace\Common\Module\Form\Src\Infrastructure\Factory\FormFactory;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\TextFieldDto;

class GetFieldSettingsFormUseCaseTest extends TestCase
{
    private FieldFactoryInterface|MockObject $fieldFactoryMock;
    private FormFactory|MockObject $formFactoryMock;
    private GetFieldSettingsFormUseCase $useCase;

    protected function setUp(): void
    {
        $this->fieldFactoryMock = $this->createMock(FieldFactoryInterface::class);
        $this->formFactoryMock = $this->createMock(FormFactory::class);

        $this->useCase = new GetFieldSettingsFormUseCase(
            $this->fieldFactoryMock,
            $this->formFactoryMock
        );
    }

    public function testExecuteSuccessfullyCreatesSettingsForm(): void
    {
        // Arrange
        $fieldConfig = [
            'label' => 'Test Label',
            'rules' => ['required' => true],
            'attributes' => ['placeholder' => 'Enter text here'],
        ];
        $fieldDto = new TextFieldDto('test_field', $fieldConfig);

        $command = new GetFieldSettingsFormCommand($fieldDto);

        $fieldMock = $this->createMock(FieldInterface::class);
        $settingsFormMock = $this->createMock(FormInterface::class);

        // Ожидаемая конфигурация полей настроек, которую вернет мок поля
        $settingsFieldsConfig = [
            'label' => ['type' => 'text', 'label' => 'Label'],
            'required' => ['type' => 'boolean', 'label' => 'Required'],
            'placeholder' => ['type' => 'text', 'label' => 'Placeholder'],
        ];

        $this->fieldFactoryMock->expects($this->once())
            ->method('createFromDto')
            ->with($fieldDto)
            ->willReturn($fieldMock);

        $fieldMock->expects($this->once())
            ->method('getSettingsFormConfig')
            ->willReturn($settingsFieldsConfig);

        // Ожидаемая конфигурация формы, которая будет передана в FormFactory
        $this->formFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->callback(function (FormConfig $formConfig) {
                $fields = $formConfig->getFields();

                // Проверяем, что значения из DTO были правильно установлены
                $this->assertArrayHasKey('label', $fields);
                $this->assertEquals('Test Label', $fields['label']['value']);

                $this->assertArrayHasKey('required', $fields);
                $this->assertTrue($fields['required']['value']);

                $this->assertArrayHasKey('placeholder', $fields);
                $this->assertEquals('Enter text here', $fields['placeholder']['value']);

                // Проверяем структуру
                $sections = $formConfig->getSections();
                $this->assertCount(1, $sections);
                $this->assertEquals('', $sections[0]->getTitle());

                $blocks = $sections[0]->getBlocks();
                $this->assertCount(1, $blocks);
                $this->assertEquals('', $blocks[0]->getTitle());

                return true;
            }))
            ->willReturn($settingsFormMock);

        // Act
        $result = $this->useCase->execute($command);

        // Assert
        $this->assertSame($settingsFormMock, $result->form);
    }
}