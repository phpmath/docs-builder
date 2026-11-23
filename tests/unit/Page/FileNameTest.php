<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder\Tests\Page;

use PHPMath\DocsBuilder\Page;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversClass(Page::class)]
#[CoversMethod(Page::class, 'fileName')]
final class FileNameTest extends TestCase
{
    public function testAppendsTheHtmlExtension(): void
    {
        // Arrange
        $page = new Page('arithmetic', 'Arithmetic', '', 'Guides', 30, '');

        // Act
        $result = $page->fileName();

        // Assert
        self::assertSame('arithmetic.html', $result);
    }
}
