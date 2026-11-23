<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder;

use function preg_replace_callback;
use function rtrim;
use function sprintf;
use function strtolower;

/**
 * Turns GitHub style alert blockquotes into callout boxes.
 *
 * A blockquote that starts with [!NOTE] or [!WARNING] becomes a callout.
 */
final class Callouts
{
    public static function apply(string $html): string
    {
        return (string)preg_replace_callback(
            '~<blockquote>\s*<p>\[!(NOTE|WARNING)\]\s*(.*?)</blockquote>~s',
            static fn (array $match): string => sprintf(
                '<div class="note%s"><p>%s</div>',
                strtolower($match[1]) === 'warning' ? ' warn' : '',
                rtrim($match[2]),
            ),
            $html,
        );
    }
}
