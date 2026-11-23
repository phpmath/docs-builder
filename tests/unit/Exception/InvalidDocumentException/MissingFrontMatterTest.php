<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder\Tests\Exception\InvalidDocumentException;

use PHPMath\DocsBuilder\Exception\InvalidDocumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidDocumentException::class)]
#[CoversMethod(InvalidDocumentException::class, 'missingFrontMatter')]
final class MissingFrontMatterTest extends TestCase
{
    public function testCreatesTheMessage(): void
    {
        // Arrange

        // Act
        $exception = InvalidDocumentException::missingFrontMatter('index.md', 'title');

        // Assert
        self::assertSame('index.md: the front matter is missing the "title" key.', $exception->getMessage());
    }
}
