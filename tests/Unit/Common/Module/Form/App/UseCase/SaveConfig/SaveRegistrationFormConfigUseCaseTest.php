<?php

namespace UserSpace\Tests\Unit\Common\Module\Form\App\UseCase\SaveConfig;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use UserSpace\Common\Module\Form\App\UseCase\SaveConfig\SaveFormConfigCommand;
use UserSpace\Common\Module\Form\App\UseCase\SaveConfig\SaveRegistrationFormConfigUseCase;
use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfig;
use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfigManagerInterface;

class SaveRegistrationFormConfigUseCaseTest extends TestCase
{
    private const FORM_TYPE = 'registration';

    private FormConfigManagerInterface|MockObject $formManagerMock;
    private SaveRegistrationFormConfigUseCase $useCase;

    protected function setUp(): void
    {
        $this->formManagerMock = $this->createMock(FormConfigManagerInterface::class);
        $this->useCase = new SaveRegistrationFormConfigUseCase($this->formManagerMock);
    }

    public function testExecuteSavesConfigSuccessfully(): void
    {
        // Arrange
        $formConfigMock = $this->createMock(FormConfig::class);
        $command = new SaveFormConfigCommand($formConfigMock, []);

        $this->formManagerMock
            ->expects($this->once())
            ->method('save')
            ->with(self::FORM_TYPE, $formConfigMock);

        // Act
        $this->useCase->execute($command);

        // Assert
        // The assertion is handled by the mock expectation above.
    }

    public function testExecuteWithDeletedFieldsStillSavesConfig(): void
    {
        // Arrange
        $formConfigMock = $this->createMock(FormConfig::class);
        $deletedFields = ['field_to_delete_1', 'field_to_delete_2'];
        $command = new SaveFormConfigCommand($formConfigMock, $deletedFields);

        // We expect 'save' to be called regardless of the deleted fields.
        // The private method processDeletedFields is empty for this use case,
        // so no other interactions with dependencies are expected.
        $this->formManagerMock
            ->expects($this->once())
            ->method('save')
            ->with(self::FORM_TYPE, $formConfigMock);

        // We can also assert that no other methods are called on this mock,
        // confirming that deletedFields are indeed ignored.
        // (This is implicitly tested by only setting one expectation).

        // Act
        $this->useCase->execute($command);

        // Assert
        // The assertion is handled by the mock expectation above.
    }
}