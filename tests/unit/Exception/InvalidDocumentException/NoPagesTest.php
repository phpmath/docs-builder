<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder\Tests\Exception\InvalidDocumentException;

use PHPMath\DocsBuilder\Exception\InvalidDocumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidDocumentException::class)]
#[CoversMethod(InvalidDocumentException::class, 'noPages')]
final class NoPagesTest extends TestCase
{
    public function testCreatesTheMessage(): void
    {
        // Arrange

        // Act
        $exception = InvalidDocumentException::noPages('docs');

        // Assert
        self::assertSame('No Markdown files found in docs.', $exception->getMessage());
    }
}
