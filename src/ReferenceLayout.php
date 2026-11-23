<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder;

use function assert;
use function implode;
use function preg_split;
use function str_starts_with;

use const PREG_SPLIT_NO_EMPTY;

/**
 * Wraps every third level heading and its content in a box.
 *
 * Reference pages describe one item (a method, a class) per third level heading.
 */
final class ReferenceLayout
{
    public static function apply(string $html): string
    {
        $chunks = preg_split('~(?=<h[23][ >])~', $html, -1, PREG_SPLIT_NO_EMPTY);

        assert($chunks !== false);

        $result = [];

        foreach ($chunks as $chunk) {
            $result[] = str_starts_with($chunk, '<h3') ? '<div class="method">' . $chunk . '</div>' : $chunk;
        }

        return implode('', $result);
    }
}
