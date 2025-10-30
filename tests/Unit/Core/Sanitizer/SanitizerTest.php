<?php

namespace UserSpace\Tests\Unit\Core\Sanitizer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use UserSpace\Core\Sanitizer\ClearedDataInterface;
use UserSpace\Core\Sanitizer\Sanitizer;
use UserSpace\Core\Sanitizer\SanitizerRule;
use UserSpace\Core\String\StringFilterInterface;

class SanitizerTest extends TestCase
{
    private StringFilterInterface|MockObject $stringFilterMock;
    private Sanitizer $sanitizer;

    protected function setUp(): void
    {
        $this->stringFilterMock = $this->createMock(StringFilterInterface::class);
        $this->sanitizer = new Sanitizer($this->stringFilterMock);
    }

    public static function sanitizationRulesProvider(): array
    {
        return [
            'Email Rule' => [' test@example.com ', SanitizerRule::EMAIL, 'sanitizeEmail', 'test@example.com'],
            'URL Rule' => ['http://example.com/ test', SanitizerRule::URL, 'sanitizeUrl', 'http://example.com/test'],
            'Integer Rule' => ['123.45', SanitizerRule::INT, null, 123],
            'Float Rule' => ['123.45', SanitizerRule::FLOAT, null, 123.45],
            'Boolean Rule (true)' => [1, SanitizerRule::BOOL, null, true],
            'Boolean Rule (false)' => [0, SanitizerRule::BOOL, null, false],
            'KSES Post Rule' => ['<p>content</p>', SanitizerRule::KSES_POST, 'ksesPost', '<p>content</p>'],
            'KSES Data Rule' => ['<p>content</p>', SanitizerRule::KSES_DATA, 'ksesData', '<p>content</p>'],
            'No HTML Rule' => ['<p>content</p>', SanitizerRule::NO_HTML, 'stripAllTags', 'content'],
            'Slug Rule' => ['Some Title', SanitizerRule::SLUG, 'sanitizeTitle', 'some-title'],
            'Key Rule' => ['some_key-1', SanitizerRule::KEY, 'sanitizeKey', 'some_key-1'],
            'File Name Rule' => ['file name.jpg', SanitizerRule::FILE_NAME, 'sanitizeFileName', 'file-name.jpg'],
            'HTML Class Rule' => ['class-1 class_2', SanitizerRule::HTML_CLASS, 'sanitizeHtmlClass', 'class-1-class_2'],
            'User Rule' => ['user name', SanitizerRule::USER, 'sanitizeUser', 'username'],
            'Default Text Field Rule' => ['<script>alert(1)</script>text', SanitizerRule::TEXT_FIELD, 'sanitizeTextField', 'text'],
        ];
    }

    #[DataProvider('sanitizationRulesProvider')]
    public function testSanitizeAppliesCorrectRule(mixed $inputValue, string $rule, ?string $expectedMethod, mixed $expectedOutput): void
    {
        // Arrange
        $data = ['field' => $inputValue];
        $config = ['field' => $rule];

        if ($expectedMethod) {
            $this->stringFilterMock
                ->expects($this->once())
                ->method($expectedMethod)
                ->with($inputValue)
                ->willReturn($expectedOutput);
        }

        // Act
        $clearedData = $this->sanitizer->sanitize($data, $config);
        $result = $clearedData->get('field');

        // Assert
        $this->assertSame($expectedOutput, $result);
    }

    public function testSanitizeHandlesNullValuesCorrectly(): void
    {
        // Arrange
        $data = ['field' => null];
        $config = ['field' => SanitizerRule::TEXT_FIELD];

        $this->stringFilterMock->expects($this->never())->method($this->anything());

        // Act
        $clearedData = $this->sanitizer->sanitize($data, $config);

        // Assert
        $this->assertNull($clearedData->get('field'));
    }

    public function testSanitizeAppliesRuleToArrayValuesRecursively(): void
    {
        // Arrange
        $data = [
            'emails' => [
                ' test1@example.com ',
                'test2@example.com',
            ],
        ];
        $config = ['emails' => SanitizerRule::EMAIL];

        $this->stringFilterMock
            ->expects($this->exactly(2))
            ->method('sanitizeEmail')
            ->willReturnMap([
                [' test1@example.com ', 'test1@example.com'],
                ['test2@example.com', 'test2@example.com'],
            ]);

        // Act
        $clearedData = $this->sanitizer->sanitize($data, $config);

        // Assert
        $expected = ['test1@example.com', 'test2@example.com'];
        $this->assertEquals($expected, $clearedData->get('emails'));
    }

    public function testSanitizeUsesDefaultRuleWhenNotSpecified(): void
    {
        // Arrange
        $inputValue = 'some text';
        $data = ['field' => $inputValue];
        $config = []; // No rule for 'field'

        $this->stringFilterMock
            ->expects($this->once())
            ->method('sanitizeTextField') // Default rule
            ->with($inputValue)
            ->willReturn($inputValue);

        // Act
        $this->sanitizer->sanitize($data, $config);

        // Assert - expectation is enough
    }

    public function testSanitizeReturnsClearedDataInstance(): void
    {
        // Arrange
        $data = ['field' => 'value'];
        $config = ['field' => SanitizerRule::TEXT_FIELD];

        $this->stringFilterMock->method('sanitizeTextField')->willReturn('value');

        // Act
        $result = $this->sanitizer->sanitize($data, $config);

        // Assert
        $this->assertInstanceOf(ClearedDataInterface::class, $result);
    }
}