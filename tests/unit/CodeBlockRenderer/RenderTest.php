<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder\Tests\CodeBlockRenderer;

use InvalidArgumentException;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use PHPMath\DocsBuilder\CodeBlockRenderer;
use PHPMath\DocsBuilder\CodeHighlighter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversClass(CodeBlockRenderer::class)]
#[CoversMethod(CodeBlockRenderer::class, 'render')]
final class RenderTest extends TestCase
{
    public function testRendersAHighlightedCodeBlock(): void
    {
        // Arrange
        $renderer = new CodeBlockRenderer(new CodeHighlighter());
        $block = new FencedCode(3, '`', 3);
        $block->setInfo('php');
        $block->setLiteral('$a = new Foo();' . "\n");
        $children = $this->createStub(ChildNodeRendererInterface::class);

        // Act
        $result = $renderer->render($block, $children);

        // Assert
        self::assertStringStartsWith('<pre><code>', $result);
        self::assertStringContainsString('<span class="hl-keyword">new</span>', $result);
    }

    public function testRejectsOtherNodes(): void
    {
        // Arrange
        $renderer = new CodeBlockRenderer(new CodeHighlighter());
        $children = $this->createStub(ChildNodeRendererInterface::class);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Incompatible node type: League\\CommonMark\\Node\\Inline\\Text');

        // Act
        $renderer->render(new Text('text'), $children);
    }
}
