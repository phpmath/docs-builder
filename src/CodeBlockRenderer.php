<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder;

use InvalidArgumentException;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;

use function explode;
use function rtrim;
use function trim;

/**
 * Renders fenced code blocks with build time syntax highlighting.
 */
final readonly class CodeBlockRenderer implements NodeRendererInterface
{
    public function __construct(private CodeHighlighter $highlighter)
    {
    }

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        if (!$node instanceof FencedCode) {
            throw new InvalidArgumentException('Incompatible node type: ' . $node::class);
        }

        $words = explode(' ', trim($node->getInfo() ?? ''));
        $language = $words[0];
        $code = rtrim($node->getLiteral(), "\n");

        return '<pre><code>' . $this->highlighter->highlight($code, $language) . "</code></pre>\n";
    }
}
