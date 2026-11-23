<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;

use function is_array;
use function preg_replace;
use function str_replace;

/**
 * Converts the Markdown of a documentation page to HTML.
 */
final readonly class MarkdownRenderer
{
    private MarkdownConverter $converter;

    public function __construct()
    {
        $environment = new Environment([
            'html_input' => 'allow',
            'allow_unsafe_links' => false,
            'heading_permalink' => [
                'html_class' => 'anchor',
                'id_prefix' => '',
                'fragment_prefix' => '',
                'apply_id_to_heading' => true,
                'heading_class' => '',
                'insert' => 'after',
                'min_heading_level' => 2,
                'max_heading_level' => 3,
                'title' => 'Link to this section',
                'symbol' => '#',
                'aria_hidden' => false,
            ],
        ]);

        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new TableExtension());
        $environment->addExtension(new FrontMatterExtension());
        $environment->addExtension(new HeadingPermalinkExtension());
        $environment->addRenderer(FencedCode::class, new CodeBlockRenderer(new CodeHighlighter()), 10);

        $this->converter = new MarkdownConverter($environment);
    }

    /**
     * Renders the Markdown.
     *
     * @return array{frontMatter: array<mixed>, html: string}
     */
    public function render(string $markdown): array
    {
        $result = $this->converter->convert($markdown);
        $frontMatter = $result instanceof RenderedContentWithFrontMatter ? $result->getFrontMatter() : [];
        $html = Callouts::apply($result->getContent());
        $html = (string)preg_replace('~href="((?:[^":/#]+/)*[^":/#]+)\.md(#[^"]*)?"~', 'href="$1.html$2"', $html);
        $html = str_replace(['<table>', '</table>'], ['<div class="tablewrap"><table>', '</table></div>'], $html);

        return [
            'frontMatter' => is_array($frontMatter) ? $frontMatter : [],
            'html' => $html,
        ];
    }
}
