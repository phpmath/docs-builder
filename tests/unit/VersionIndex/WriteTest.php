<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder\Tests\VersionIndex;

use PHPMath\DocsBuilder\Exception\InvalidDocumentException;
use PHPMath\DocsBuilder\Tests\TemporaryDirectory;
use PHPMath\DocsBuilder\VersionIndex;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function json_decode;
use function mkdir;
use function restore_error_handler;
use function set_error_handler;

#[CoversClass(VersionIndex::class)]
#[CoversMethod(VersionIndex::class, 'write')]
final class WriteTest extends TestCase
{
    private TemporaryDirectory $directory;

    protected function setUp(): void
    {
        $this->directory = new TemporaryDirectory();
    }

    protected function tearDown(): void
    {
        $this->directory->remove();
    }

    public function testWritesVersionsJson(): void
    {
        // Arrange
        $output = $this->directory->path . '/site';

        // Act
        VersionIndex::write($output, '3.x', ['3.x', '2.x']);

        // Assert
        $data = json_decode((string)file_get_contents($output . '/versions.json'), true);
        static::assertSame(['latest' => '3.x', 'versions' => ['3.x', '2.x']], $data);
    }

    public function testWritesRedirectToTheLatestVersion(): void
    {
        // Arrange
        $output = $this->directory->path . '/site';

        // Act
        VersionIndex::write($output, '3.x', ['3.x', '2.x']);

        // Assert
        $html = (string)file_get_contents($output . '/index.html');
        static::assertStringContainsString('url=3.x/', $html);
    }

    public function testThrowsWhenTheLatestVersionIsNotListed(): void
    {
        // Arrange
        $output = $this->directory->path . '/site';

        // Assert
        $this->expectException(InvalidDocumentException::class);
        $this->expectExceptionMessage('The latest version "4.x" is not in the version list.');

        // Act
        VersionIndex::write($output, '4.x', ['3.x']);
    }

    public function testWritesTheDevelopmentVersionWhenGiven(): void
    {
        // Arrange
        $output = $this->directory->path . '/site';

        // Act
        VersionIndex::write($output, '1.0.0', ['master', '1.0.0'], 'master');

        // Assert
        $data = json_decode((string)file_get_contents($output . '/versions.json'), true);
        static::assertSame(
            ['latest' => '1.0.0', 'versions' => ['master', '1.0.0'], 'development' => 'master'],
            $data,
        );
    }

    public function testThrowsWhenTheOutputDirectoryCannotBeCreated(): void
    {
        // Arrange
        $this->directory->write('blocker', 'a file, not a directory');

        // Assert
        $this->expectException(InvalidDocumentException::class);
        $this->expectExceptionMessage('Cannot write to');

        // Act
        set_error_handler(static fn (): bool => true);

        try {
            VersionIndex::write($this->directory->path . '/blocker/site', '3.x', ['3.x']);
        } finally {
            restore_error_handler();
        }
    }

    public function testThrowsWhenAFileCannotBeWritten(): void
    {
        // Arrange
        $output = $this->directory->path . '/site';
        mkdir($output . '/versions.json', 0755, true);

        // Assert
        $this->expectException(InvalidDocumentException::class);
        $this->expectExceptionMessage('Cannot write to');

        // Act
        set_error_handler(static fn (): bool => true);

        try {
            VersionIndex::write($output, '3.x', ['3.x']);
        } finally {
            restore_error_handler();
        }
    }
}
