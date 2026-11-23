<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder\Tests\Callouts;

use PHPMath\DocsBuilder\Callouts;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversClass(Callouts::class)]
#[CoversMethod(Callouts::class, 'apply')]
final class ApplyTest extends TestCase
{
    public function testNoteBecomesCallout(): void
    {
        // Arrange
        $html = "<blockquote>\n<p>[!NOTE]\nRemember this.</p>\n</blockquote>";

        // Act
        $result = Callouts::apply($html);

        // Assert
        static::assertSame('<div class="note"><p>Remember this.</p></div>', $result);
    }

    public function testWarningBecomesWarningCallout(): void
    {
        // Arrange
        $html = "<blockquote>\n<p>[!WARNING]\nBe careful.</p>\n</blockquote>";

        // Act
        $result = Callouts::apply($html);

        // Assert
        static::assertSame('<div class="note warn"><p>Be careful.</p></div>', $result);
    }

    public function testCalloutKeepsFurtherParagraphs(): void
    {
        // Arrange
        $html = "<blockquote>\n<p>[!NOTE]\nOne.</p>\n<p>Two.</p>\n</blockquote>";

        // Act
        $result = Callouts::apply($html);

        // Assert
        static::assertSame("<div class=\"note\"><p>One.</p>\n<p>Two.</p></div>", $result);
    }

    public function testPlainBlockquoteIsUntouched(): void
    {
        // Arrange
        $html = "<blockquote>\n<p>A quote.</p>\n</blockquote>";

        // Act
        $result = Callouts::apply($html);

        // Assert
        static::assertSame($html, $result);
    }
}
