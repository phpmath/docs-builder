<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder\Tests\Page;

use PHPMath\DocsBuilder\Page;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversClass(Page::class)]
#[CoversMethod(Page::class, '__construct')]
final class ConstructorTest extends TestCase
{
    public function testStoresTheValues(): void
    {
        // Arrange

        // Act
        $page = new Page('arithmetic', 'Arithmetic', 'Add numbers.', 'Guides', 30, '<p>Body</p>');

        // Assert
        self::assertSame('arithmetic', $page->name);
        self::assertSame('Arithmetic', $page->title);
        self::assertSame('Add numbers.', $page->intro);
        self::assertSame('Guides', $page->group);
        self::assertSame(30, $page->order);
        self::assertSame('<p>Body</p>', $page->html);
    }
}
