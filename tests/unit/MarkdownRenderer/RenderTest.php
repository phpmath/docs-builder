<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder\Tests\MarkdownRenderer;

use PHPMath\DocsBuilder\MarkdownRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversClass(MarkdownRenderer::class)]
#[CoversMethod(MarkdownRenderer::class, 'render')]
final class RenderTest extends TestCase
{
    public function testReadsFrontMatter(): void
    {
        // Arrange
        $renderer = new MarkdownRenderer();
        $markdown = "---\ntitle: Hello\norder: 2\n---\n\nBody";

        // Act
        $result = $renderer->render($markdown);

        // Assert
        static::assertSame(['title' => 'Hello', 'order' => 2], $result['frontMatter']);
        static::assertStringContainsString('<p>Body</p>', $result['html']);
    }

    public function testReturnsEmptyFrontMatterWhenAbsent(): void
    {
        // Arrange
        $renderer = new MarkdownRenderer();

        // Act
        $result = $renderer->render('Body');

        // Assert
        static::assertSame([], $result['frontMatter']);
    }

    public function testRewritesRelativeMarkdownLinks(): void
    {
        // Arrange
        $renderer = new MarkdownRenderer();

        // Act
        $result = $renderer->render('[Math](arithmetic.md#division)');

        // Assert
        static::assertStringContainsString('href="arithmetic.html#division"', $result['html']);
    }

    public function testRewritesMarkdownLinksInRawHtml(): void
    {
        // Arrange
        $renderer = new MarkdownRenderer();

        // Act
        $result = $renderer->render('<a href="concepts.md">Concepts</a>');

        // Assert
        static::assertStringContainsString('href="concepts.html"', $result['html']);
    }

    public function testKeepsAbsoluteLinks(): void
    {
        // Arrange
        $renderer = new MarkdownRenderer();

        // Act
        $result = $renderer->render('[Site](https://example.com/page.md) [Root](/biginteger/a.md) [Top](#top)');

        // Assert
        static::assertStringContainsString('href="https://example.com/page.md"', $result['html']);
        static::assertStringContainsString('href="/biginteger/a.md"', $result['html']);
        static::assertStringContainsString('href="#top"', $result['html']);
    }

    public function testAddsHeadingAnchors(): void
    {
        // Arrange
        $renderer = new MarkdownRenderer();

        // Act
        $result = $renderer->render('## Division rules');

        // Assert
        static::assertStringContainsString('<h2 id="division-rules">', $result['html']);
        static::assertStringContainsString('class="anchor"', $result['html']);
    }

    public function testWrapsTablesForScrolling(): void
    {
        // Arrange
        $renderer = new MarkdownRenderer();
        $markdown = "| A | B |\n|---|---|\n| 1 | 2 |";

        // Act
        $result = $renderer->render($markdown);

        // Assert
        static::assertStringContainsString('<div class="tablewrap"><table>', $result['html']);
        static::assertStringContainsString('</table></div>', $result['html']);
    }

    public function testHighlightsFencedCode(): void
    {
        // Arrange
        $renderer = new MarkdownRenderer();
        $markdown = "```php\n\$a = new Foo();\n```";

        // Act
        $result = $renderer->render($markdown);

        // Assert
        static::assertStringContainsString('<span class="hl-keyword">new</span>', $result['html']);
        static::assertStringStartsWith('<pre><code>', $result['html']);
    }

    public function testRendersCallouts(): void
    {
        // Arrange
        $renderer = new MarkdownRenderer();

        // Act
        $result = $renderer->render("> [!NOTE]\n> Remember this.");

        // Assert
        static::assertStringContainsString('<div class="note">', $result['html']);
    }
}
