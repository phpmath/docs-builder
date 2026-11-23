<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder;

use PHPMath\DocsBuilder\Exception\InvalidDocumentException;

use function array_key_exists;
use function count;
use function dirname;
use function file_get_contents;
use function file_put_contents;
use function glob;
use function is_dir;
use function is_int;
use function is_string;
use function mkdir;
use function ob_get_clean;
use function ob_start;
use function pathinfo;
use function rtrim;
use function usort;

use const PATHINFO_FILENAME;

/**
 * Builds the HTML pages of one library version from Markdown files.
 */
final readonly class Builder
{
    private MarkdownRenderer $renderer;

    public function __construct(private BuildOptions $options)
    {
        $this->renderer = new MarkdownRenderer();
    }

    /**
     * Builds the site and returns the written files.
     *
     * @return list<string>
     */
    public function build(): array
    {
        $pages = $this->readPages();
        $logo = $this->read(dirname(__DIR__) . '/resources/logo.html');
        $target = rtrim($this->options->output, '/') . '/' . $this->options->version;
        $this->ensureDirectory($target);

        $written = [];

        foreach ($pages as $index => $page) {
            $html = $this->renderTemplate($page, $pages, $logo, $pages[$index - 1] ?? null, $pages[$index + 1] ?? null);
            $path = $target . '/' . $page->fileName();

            if (file_put_contents($path, $html) === false) {
                throw InvalidDocumentException::cannotWrite($path);
            }

            $written[] = $path;
        }

        return $written;
    }

    /** @return list<Page> */
    private function readPages(): array
    {
        $files = glob(rtrim($this->options->source, '/') . '/*.md');

        if ($files === false || count($files) === 0) {
            throw InvalidDocumentException::noPages($this->options->source);
        }

        $pages = [];

        foreach ($files as $file) {
            $pages[] = $this->readPage($file);
        }

        usort($pages, static fn (Page $a, Page $b): int => [$a->order, $a->name] <=> [$b->order, $b->name]);

        return $pages;
    }

    private function readPage(string $file): Page
    {
        $name = pathinfo($file, PATHINFO_FILENAME);
        $rendered = $this->renderer->render($this->read($file));
        $frontMatter = $rendered['frontMatter'];

        if (!array_key_exists('title', $frontMatter)) {
            throw InvalidDocumentException::missingFrontMatter($file, 'title');
        }

        $title = $frontMatter['title'];
        $intro = $frontMatter['intro'] ?? '';
        $group = $frontMatter['group'] ?? 'Documentation';
        $order = $frontMatter['order'] ?? 1000;
        $layout = $frontMatter['layout'] ?? '';

        if (!is_string($title)) {
            throw InvalidDocumentException::invalidFrontMatter($file, 'title', 'a string');
        }

        if (!is_string($intro)) {
            throw InvalidDocumentException::invalidFrontMatter($file, 'intro', 'a string');
        }

        if (!is_string($group)) {
            throw InvalidDocumentException::invalidFrontMatter($file, 'group', 'a string');
        }

        if (!is_int($order)) {
            throw InvalidDocumentException::invalidFrontMatter($file, 'order', 'an integer');
        }

        $html = $rendered['html'];

        if ($layout === 'reference') {
            $html = ReferenceLayout::apply($html);
        }

        return new Page($name, $title, $intro, $group, $order, $html);
    }

    /** @param list<Page> $pages */
    private function renderTemplate(Page $page, array $pages, string $logo, ?Page $previous, ?Page $next): string
    {
        $options = $this->options;

        ob_start();
        require dirname(__DIR__) . '/templates/page.php';

        return (string)ob_get_clean();
    }

    private function read(string $path): string
    {
        $content = file_get_contents($path);

        if ($content === false) {
            throw InvalidDocumentException::cannotRead($path);
        }

        return $content;
    }

    private function ensureDirectory(string $directory): void
    {
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw InvalidDocumentException::cannotWrite($directory);
        }
    }
}
