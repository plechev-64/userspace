<?php

namespace UserSpace\Tests\Unit\Common\Module\Form\Src\Infrastructure\Form\Config;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfig;
use UserSpace\Common\Module\Form\Src\Domain\Repository\FormRepositoryInterface;
use UserSpace\Common\Module\Form\Src\Infrastructure\Form\Config\FormConfigManager;

class FormConfigManagerTest extends TestCase
{
    private const TEST_FORM_TYPE = 'test_form';
    private const TEST_FORM_ID = 1;

    private FormRepositoryInterface|MockObject $formRepositoryMock;
    private FormConfigManager $formConfigManager;

    protected function setUp(): void
    {
        $this->formRepositoryMock = $this->createMock(FormRepositoryInterface::class);
        $this->formConfigManager = new FormConfigManager($this->formRepositoryMock);
    }

    public function testSaveSuccessfullyCallsRepository(): void
    {
        // Arrange
        $formConfigMock = $this->createMock(FormConfig::class);
        $configArray = ['sections' => [['title' => 'Test Section']]];

        $formConfigMock->expects($this->once())
            ->method('toArray')
            ->willReturn($configArray);

        $this->formRepositoryMock->expects($this->once())
            ->method('createOrUpdate')
            ->with(self::TEST_FORM_TYPE, $configArray)
            ->willReturn(self::TEST_FORM_ID);

        // Act
        $result = $this->formConfigManager->save(self::TEST_FORM_TYPE, $formConfigMock);

        // Assert
        $this->assertEquals(self::TEST_FORM_ID, $result);
    }

    public function testLoadFromRepositorySuccessfully(): void
    {
        // Arrange
        $configArray = ['sections' => [['title' => 'Test Section', 'blocks' => []]]];
        $configJson = json_encode($configArray);

        $formEntity = new stdClass();
        $formEntity->config = $configJson;

        $this->formRepositoryMock->expects($this->once())
            ->method('findByType')
            ->with(self::TEST_FORM_TYPE)
            ->willReturn($formEntity);

        // Act
        $formConfig = $this->formConfigManager->load(self::TEST_FORM_TYPE);

        // Assert
        $this->assertInstanceOf(FormConfig::class, $formConfig);
        $this->assertEquals($configArray, $formConfig->toArray());
    }

    public function testLoadReturnsNullWhenRepositoryFindsNothing(): void
    {
        // Arrange
        $this->formRepositoryMock->expects($this->once())
            ->method('findByType')
            ->with(self::TEST_FORM_TYPE)
            ->willReturn(null);

        // Act
        $formConfig = $this->formConfigManager->load(self::TEST_FORM_TYPE);

        // Assert
        $this->assertNull($formConfig);
    }

    public function testLoadReturnsNullWhenConfigPropertyIsMissing(): void
    {
        // Arrange
        $formEntity = new stdClass(); // No 'config' property

        $this->formRepositoryMock->expects($this->once())
            ->method('findByType')
            ->with(self::TEST_FORM_TYPE)
            ->willReturn($formEntity);

        // Act
        $formConfig = $this->formConfigManager->load(self::TEST_FORM_TYPE);

        // Assert
        $this->assertNull($formConfig);
    }

    public function testLoadReturnsNullForInvalidJson(): void
    {
        // Arrange
        $formEntity = new stdClass();
        $formEntity->config = '{"invalid_json":';

        $this->formRepositoryMock->expects($this->once())
            ->method('findByType')
            ->with(self::TEST_FORM_TYPE)
            ->willReturn($formEntity);

        // Act
        $formConfig = $this->formConfigManager->load(self::TEST_FORM_TYPE);

        // Assert
        $this->assertNull($formConfig);
    }

    public function testLoadUsesInternalConfigWhenAvailable(): void
    {
        // Arrange
        $internalFormConfig = new FormConfig();
        $internalFormConfig->addSection('Internal Section');

        $configProvider = fn() => $internalFormConfig;

        // Register the internal config provider
        $this->formConfigManager->registerInternalConfig(self::TEST_FORM_TYPE, $configProvider);

        // The repository should NOT be called
        $this->formRepositoryMock->expects($this->never())
            ->method('findByType');

        // Act
        $loadedConfig = $this->formConfigManager->load(self::TEST_FORM_TYPE);

        // Assert
        $this->assertSame($internalFormConfig, $loadedConfig);
        $sections = $loadedConfig->getSections();
        $this->assertCount(1, $sections);
        $this->assertEquals('Internal Section', $sections[0]->getTitle());
    }
}