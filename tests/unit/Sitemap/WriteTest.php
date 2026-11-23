<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder\Tests\Sitemap;

use PHPMath\DocsBuilder\Exception\InvalidDocumentException;
use PHPMath\DocsBuilder\Sitemap;
use PHPMath\DocsBuilder\Tests\TemporaryDirectory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function restore_error_handler;
use function set_error_handler;
use function simplexml_load_string;

#[CoversClass(Sitemap::class)]
#[CoversMethod(Sitemap::class, 'write')]
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

    public function testListsThePagesOfTheVersion(): void
    {
        // Arrange
        $output = $this->directory->path;

        // Act
        $path = Sitemap::write($output, 'https://phpmath.github.io/matrix/', '1.0.0', ['index.html', 'api.html']);

        // Assert
        self::assertSame($output . '/sitemap.xml', $path);
        $xml = simplexml_load_string((string)file_get_contents($path));
        self::assertNotFalse($xml);
        self::assertSame(
            [
                'https://phpmath.github.io/matrix/1.0.0/index.html',
                'https://phpmath.github.io/matrix/1.0.0/api.html',
            ],
            [(string)$xml->url[0]->loc, (string)$xml->url[1]->loc],
        );
    }

    public function testAcceptsABaseUrlWithoutTrailingSlash(): void
    {
        // Arrange
        $output = $this->directory->path;

        // Act
        $path = Sitemap::write($output, 'https://example.com/docs', 'master', ['index.html']);

        // Assert
        self::assertStringContainsString(
            '<loc>https://example.com/docs/master/index.html</loc>',
            (string)file_get_contents($path),
        );
    }

    public function testEscapesSpecialCharacters(): void
    {
        // Arrange
        $output = $this->directory->path;

        // Act
        $path = Sitemap::write($output, 'https://example.com/?a=1&b=2', 'master', ['index.html']);

        // Assert
        self::assertStringContainsString('a=1&amp;b=2', (string)file_get_contents($path));
    }

    public function testThrowsWhenTheFileCannotBeWritten(): void
    {
        // Arrange
        $output = $this->directory->path . '/missing';

        // Assert
        $this->expectException(InvalidDocumentException::class);
        $this->expectExceptionMessage('Cannot write to');

        // Act
        set_error_handler(static fn (): bool => true);

        try {
            Sitemap::write($output, 'https://example.com/', 'master', ['index.html']);
        } finally {
            restore_error_handler();
        }
    }
}
