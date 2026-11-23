<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder\Tests\CodeHighlighter;

use PHPMath\DocsBuilder\CodeHighlighter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversClass(CodeHighlighter::class)]
#[CoversMethod(CodeHighlighter::class, 'highlight')]
final class HighlightTest extends TestCase
{
    public function testHighlightsPhp(): void
    {
        // Arrange
        $highlighter = new CodeHighlighter();

        // Act
        $result = $highlighter->highlight('$a = new Foo();', 'php');

        // Assert
        static::assertStringContainsString('<span class="hl-keyword">new</span>', $result);
    }

    public function testShellAliasesAreHighlightedAsShell(): void
    {
        // Arrange
        $highlighter = new CodeHighlighter();

        // Act
        $result = $highlighter->highlight('composer require phpmath/biginteger', 'bash');

        // Assert
        static::assertStringContainsString('composer require phpmath/biginteger', $result);
    }

    public function testUnknownLanguageIsEscapedOnly(): void
    {
        // Arrange
        $highlighter = new CodeHighlighter();

        // Act
        $result = $highlighter->highlight('<b>&</b>', 'brainfuck');

        // Assert
        static::assertSame('&lt;b&gt;&amp;&lt;/b&gt;', $result);
    }

    public function testMissingLanguageIsEscapedOnly(): void
    {
        // Arrange
        $highlighter = new CodeHighlighter();

        // Act
        $result = $highlighter->highlight('a < b', '');

        // Assert
        static::assertSame('a &lt; b', $result);
    }
}
