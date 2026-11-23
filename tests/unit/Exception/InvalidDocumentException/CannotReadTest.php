<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder\Tests\Exception\InvalidDocumentException;

use PHPMath\DocsBuilder\Exception\InvalidDocumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidDocumentException::class)]
#[CoversMethod(InvalidDocumentException::class, 'cannotRead')]
final class CannotReadTest extends TestCase
{
    public function testCreatesTheMessage(): void
    {
        // Arrange

        // Act
        $exception = InvalidDocumentException::cannotRead('docs/index.md');

        // Assert
        self::assertSame('Cannot read docs/index.md.', $exception->getMessage());
    }
}
