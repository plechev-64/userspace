<?php

namespace UserSpace\Tests\Unit\Common\Module\Form\App\UseCase\GetForgotPasswordForm;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use UserSpace\Common\Module\Form\App\UseCase\GetForgotPasswordForm\GetForgotPasswordFormCommand;
use UserSpace\Common\Module\Form\App\UseCase\GetForgotPasswordForm\GetForgotPasswordFormUseCase;
use UserSpace\Common\Module\Form\Src\Domain\Factory\FormFactoryInterface;
use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfig;
use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfigManagerInterface;
use UserSpace\Common\Module\Form\Src\Domain\Form\FormInterface;
use UserSpace\Core\Exception\UspException;

class GetForgotPasswordFormUseCaseTest extends TestCase
{
    private FormConfigManagerInterface|MockObject $formConfigManagerMock;
    private FormFactoryInterface|MockObject $formFactoryMock;
    private GetForgotPasswordFormUseCase $useCase;

    protected function setUp(): void
    {
        $this->formConfigManagerMock = $this->createMock(FormConfigManagerInterface::class);
        $this->formFactoryMock = $this->createMock(FormFactoryInterface::class);

        $this->useCase = new GetForgotPasswordFormUseCase(
            $this->formConfigManagerMock,
            $this->formFactoryMock
        );
    }

    public function testExecuteSuccessfullyReturnsForm(): void
    {
        // Arrange
        $command = new GetForgotPasswordFormCommand();
        $formConfigMock = $this->createMock(FormConfig::class);
        $formMock = $this->createMock(FormInterface::class);

        $this->formConfigManagerMock
            ->expects($this->once())
            ->method('load')
            ->with(GetForgotPasswordFormUseCase::FORM_TYPE)
            ->willReturn($formConfigMock);

        $this->formFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($formConfigMock)
            ->willReturn($formMock);

        // Act
        $result = $this->useCase->execute($command);

        // Assert
        $this->assertSame($formMock, $result->form);
    }

    public function testExecuteThrowsExceptionWhenConfigNotFound(): void
    {
        // Arrange
        $command = new GetForgotPasswordFormCommand();

        $this->formConfigManagerMock
            ->expects($this->once())
            ->method('load')
            ->with(GetForgotPasswordFormUseCase::FORM_TYPE)
            ->willReturn(null);

        $this->formFactoryMock
            ->expects($this->never())
            ->method('create');

        // Assert
        $this->expectException(UspException::class);
        $this->expectExceptionMessage('Forgot password form config not found.');
        $this->expectExceptionCode(404);

        // Act
        $this->useCase->execute($command);
    }
}