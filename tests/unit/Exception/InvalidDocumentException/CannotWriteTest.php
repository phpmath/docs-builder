<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder\Tests\Exception\InvalidDocumentException;

use PHPMath\DocsBuilder\Exception\InvalidDocumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidDocumentException::class)]
#[CoversMethod(InvalidDocumentException::class, 'cannotWrite')]
final class CannotWriteTest extends TestCase
{
    public function testCreatesTheMessage(): void
    {
        // Arrange

        // Act
        $exception = InvalidDocumentException::cannotWrite('out/index.html');

        // Assert
        self::assertSame('Cannot write to out/index.html.', $exception->getMessage());
    }
}
