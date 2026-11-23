<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder\Tests\ReferenceLayout;

use PHPMath\DocsBuilder\ReferenceLayout;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversClass(ReferenceLayout::class)]
#[CoversMethod(ReferenceLayout::class, 'apply')]
final class ApplyTest extends TestCase
{
    public function testWrapsThirdLevelHeadings(): void
    {
        // Arrange
        $html = '<h2>Group</h2><p>Intro</p><h3>add()</h3><p>Adds.</p><h3>sub()</h3><p>Subtracts.</p>';

        // Act
        $result = ReferenceLayout::apply($html);

        // Assert
        static::assertSame(
            '<h2>Group</h2><p>Intro</p>'
            . '<div class="method"><h3>add()</h3><p>Adds.</p></div>'
            . '<div class="method"><h3>sub()</h3><p>Subtracts.</p></div>',
            $result,
        );
    }

    public function testLeavesPagesWithoutThirdLevelHeadingsUntouched(): void
    {
        // Arrange
        $html = '<h2>Group</h2><p>Intro</p>';

        // Act
        $result = ReferenceLayout::apply($html);

        // Assert
        static::assertSame($html, $result);
    }
}
