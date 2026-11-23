<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder\Tests\Exception\InvalidDocumentException;

use PHPMath\DocsBuilder\Exception\InvalidDocumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidDocumentException::class)]
#[CoversMethod(InvalidDocumentException::class, 'gitFailed')]
final class GitFailedTest extends TestCase
{
    public function testCreatesTheMessage(): void
    {
        // Arrange

        // Act
        $exception = InvalidDocumentException::gitFailed('git archive nope', 'bad ref');

        // Assert
        self::assertSame('The git command "git archive nope" failed: bad ref', $exception->getMessage());
    }
}
