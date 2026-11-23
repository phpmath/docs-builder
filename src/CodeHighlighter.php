<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder;

use Tempest\Highlight\Highlighter;

use function htmlspecialchars;
use function in_array;
use function strtolower;
use function trim;

use const ENT_QUOTES;

/**
 * Highlights code at build time.
 */
final readonly class CodeHighlighter
{
    private Highlighter $highlighter;

    public function __construct()
    {
        $this->highlighter = new Highlighter();
    }

    /**
     * Returns the highlighted HTML for the given code. Unknown languages are escaped only.
     *
     * A failure of the highlighter is not hidden, so a broken build is visible.
     */
    public function highlight(string $code, string $language): string
    {
        $language = $this->normalize($language);

        if ($language === '') {
            return htmlspecialchars($code, ENT_QUOTES);
        }

        return $this->highlighter->parse($code, $language);
    }

    private function normalize(string $language): string
    {
        $language = strtolower(trim($language));

        if (in_array($language, ['bash', 'sh', 'shell', 'console'], true)) {
            return 'shell';
        }

        return in_array($language, ['php', 'json', 'html', 'xml', 'yaml', 'yml'], true) ? $language : '';
    }
}
