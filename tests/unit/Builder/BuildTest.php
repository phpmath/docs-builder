<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder\Tests\Builder;

use PHPMath\DocsBuilder\Builder;
use PHPMath\DocsBuilder\BuildOptions;
use PHPMath\DocsBuilder\Exception\InvalidDocumentException;
use PHPMath\DocsBuilder\Tests\TemporaryDirectory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

use function chmod;
use function file_get_contents;
use function mkdir;
use function restore_error_handler;
use function set_error_handler;
use function strpos;

#[CoversClass(Builder::class)]
#[CoversMethod(Builder::class, 'build')]
final class BuildTest extends TestCase
{
    private TemporaryDirectory $directory;

    protected function setUp(): void
    {
        $this->directory = new TemporaryDirectory();
    }

    protected function tearDown(): void
    {
        @chmod($this->directory->path . '/src/index.md', 0644);
        $this->directory->remove();
    }

    public function testWritesOneHtmlFilePerMarkdownFile(): void
    {
        // Arrange
        $this->directory->write('src/index.md', "---\ntitle: Introduction\n---\n\nHello");
        $this->directory->write('src/guide.md', "---\ntitle: Guide\n---\n\nSteps");
        $builder = new Builder($this->options());

        // Act
        $written = $builder->build();

        // Assert
        static::assertCount(2, $written);
        static::assertFileExists($this->directory->path . '/out/3.x/index.html');
        static::assertFileExists($this->directory->path . '/out/3.x/guide.html');
    }

    public function testRendersTitleIntroAndBody(): void
    {
        // Arrange
        $markdown = "---\ntitle: Introduction\nintro: A short intro.\n---\n\nHello *world*";
        $this->directory->write('src/index.md', $markdown);
        $builder = new Builder($this->options());

        // Act
        $builder->build();

        // Assert
        $html = (string)file_get_contents($this->directory->path . '/out/3.x/index.html');
        static::assertStringContainsString('<h1>Introduction</h1>', $html);
        static::assertStringContainsString('<p class="intro">A short intro.</p>', $html);
        static::assertStringContainsString('<em>world</em>', $html);
        static::assertStringContainsString('<title>Introduction | phpmath/biginteger</title>', $html);
    }

    public function testOrdersPagesAndBuildsSidebarAndPager(): void
    {
        // Arrange
        $this->directory->write('src/b.md', "---\ntitle: Second\ngroup: Guides\norder: 2\n---\n\nB");
        $this->directory->write('src/a.md', "---\ntitle: First\ngroup: Start\norder: 1\n---\n\nA");
        $this->directory->write('src/c.md', "---\ntitle: Third\ngroup: Guides\norder: 3\n---\n\nC");
        $builder = new Builder($this->options());

        // Act
        $builder->build();

        // Assert
        $html = (string)file_get_contents($this->directory->path . '/out/3.x/b.html');
        static::assertLessThan(
            (int)strpos($html, '<h4>Guides</h4>'),
            (int)strpos($html, '<h4>Start</h4>'),
        );
        static::assertStringContainsString('<a href="b.html" aria-current="page">Second</a>', $html);
        static::assertStringContainsString('<small>Previous</small>First', $html);
        static::assertStringContainsString('<small>Next</small>Third', $html);
    }

    public function testWrapsThirdLevelHeadingsOfReferencePages(): void
    {
        // Arrange
        $markdown = "---\ntitle: API\nlayout: reference\n---\n\n## Group\n\n### add()\n\nAdds.";
        $this->directory->write('src/api.md', $markdown);
        $builder = new Builder($this->options());

        // Act
        $builder->build();

        // Assert
        $html = (string)file_get_contents($this->directory->path . '/out/3.x/api.html');
        static::assertStringContainsString('<div class="method"><h3', $html);
    }

    public function testEditLinkPointsToTheMarkdownSource(): void
    {
        // Arrange
        $this->directory->write('src/index.md', "---\ntitle: Introduction\n---\n\nHello");
        $builder = new Builder($this->options());

        // Act
        $builder->build();

        // Assert
        $html = (string)file_get_contents($this->directory->path . '/out/3.x/index.html');
        static::assertStringContainsString(
            'https://github.com/phpmath/biginteger/edit/master/docs/3.x/index.md',
            $html,
        );
    }

    public function testThrowsWhenTheTitleIsMissing(): void
    {
        // Arrange
        $this->directory->write('src/index.md', 'No front matter');
        $builder = new Builder($this->options());

        // Assert
        $this->expectException(InvalidDocumentException::class);
        $this->expectExceptionMessage('the front matter is missing the "title" key');

        // Act
        $builder->build();
    }

    public function testThrowsWhenTheOrderIsNotAnInteger(): void
    {
        // Arrange
        $this->directory->write('src/index.md', "---\ntitle: Intro\norder: first\n---\n\nHello");
        $builder = new Builder($this->options());

        // Assert
        $this->expectException(InvalidDocumentException::class);
        $this->expectExceptionMessage('the front matter key "order" must be an integer');

        // Act
        $builder->build();
    }

    public function testThrowsWhenThereAreNoMarkdownFiles(): void
    {
        // Arrange
        $this->directory->write('src/readme.txt', 'Not markdown');
        $builder = new Builder($this->options());

        // Assert
        $this->expectException(InvalidDocumentException::class);
        $this->expectExceptionMessage('No Markdown files found');

        // Act
        $builder->build();
    }

    private function options(): BuildOptions
    {
        return new BuildOptions(
            'biginteger',
            '3.x',
            'phpmath/biginteger',
            $this->directory->path . '/src',
            $this->directory->path . '/out',
            'docs/3.x',
        );
    }

    public function testThrowsWhenTheTitleIsNotAString(): void
    {
        // Arrange
        $this->directory->write('src/index.md', "---\ntitle: [a]\n---\n\nBody");
        $builder = new Builder($this->options());

        // Assert
        $this->expectException(InvalidDocumentException::class);
        $this->expectExceptionMessage('the front matter key "title" must be a string');

        // Act
        $builder->build();
    }

    public function testThrowsWhenTheIntroIsNotAString(): void
    {
        // Arrange
        $this->directory->write('src/index.md', "---\ntitle: Doc\nintro: [a]
---\n\nBody");
        $builder = new Builder($this->options());

        // Assert
        $this->expectException(InvalidDocumentException::class);
        $this->expectExceptionMessage('the front matter key "intro" must be a string');

        // Act
        $builder->build();
    }

    public function testThrowsWhenTheGroupIsNotAString(): void
    {
        // Arrange
        $this->directory->write('src/index.md', "---\ntitle: Doc\ngroup: [a]
---\n\nBody");
        $builder = new Builder($this->options());

        // Assert
        $this->expectException(InvalidDocumentException::class);
        $this->expectExceptionMessage('the front matter key "group" must be a string');

        // Act
        $builder->build();
    }

    public function testThrowsWhenAPageCannotBeWritten(): void
    {
        // Arrange
        $this->directory->write('src/index.md', "---\ntitle: Doc\n---\n\nBody");
        mkdir($this->directory->path . '/out/3.x/index.html', 0755, true);
        $builder = new Builder($this->options());

        // Assert
        $this->expectException(InvalidDocumentException::class);
        $this->expectExceptionMessage('Cannot write to');

        // Act
        set_error_handler(static fn (): bool => true);

        try {
            $builder->build();
        } finally {
            restore_error_handler();
        }
    }

    public function testThrowsWhenASourceFileCannotBeRead(): void
    {
        // Arrange
        $this->directory->write('src/index.md', "---\ntitle: Doc\n---\n\nBody");
        chmod($this->directory->path . '/src/index.md', 0000);
        $builder = new Builder($this->options());

        // Assert
        $this->expectException(InvalidDocumentException::class);
        $this->expectExceptionMessage('Cannot read');

        // Act
        set_error_handler(static fn (): bool => true);

        try {
            $builder->build();
        } finally {
            restore_error_handler();
        }
    }

    public function testThrowsWhenTheOutputDirectoryCannotBeCreated(): void
    {
        // Arrange
        $this->directory->write('src/index.md', "---\ntitle: Doc\n---\n\nBody");
        $this->directory->write('blocker', 'a file, not a directory');
        $builder = new Builder(new BuildOptions(
            'biginteger',
            '3.x',
            'phpmath/biginteger',
            $this->directory->path . '/src',
            $this->directory->path . '/blocker/out',
            'docs/3.x',
        ));

        // Assert
        $this->expectException(InvalidDocumentException::class);
        $this->expectExceptionMessage('Cannot write to');

        // Act
        set_error_handler(static fn (): bool => true);

        try {
            $builder->build();
        } finally {
            restore_error_handler();
        }
    }
}
