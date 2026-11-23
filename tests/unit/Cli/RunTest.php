<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder\Tests\Cli;

use PHPMath\DocsBuilder\Cli;
use PHPMath\DocsBuilder\Tests\TemporaryDirectory;
use PHPMath\DocsBuilder\Tests\TemporaryGitRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

use function fopen;
use function rewind;
use function stream_get_contents;

#[CoversClass(Cli::class)]
#[CoversMethod(Cli::class, 'run')]
final class RunTest extends TestCase
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

    public function testBuildWritesThePagesAndReportsTheCount(): void
    {
        // Arrange
        $this->directory->write('src/index.md', "---\ntitle: Introduction\n---\n\nHello");
        $stdout = $this->stream();
        $cli = new Cli($stdout, $this->stream());

        // Act
        $status = $cli->run([
            'build',
            '--library=biginteger',
            '--version=3.x',
            '--source=' . $this->directory->path . '/src',
            '--out=' . $this->directory->path . '/out',
        ]);

        // Assert
        static::assertSame(0, $status);
        static::assertSame("Built 1 pages.\n", $this->contents($stdout));
        static::assertFileExists($this->directory->path . '/out/3.x/index.html');
    }

    public function testBuildAllBuildsTheBranchAndTheTags(): void
    {
        // Arrange
        $repository = new TemporaryGitRepository();
        $repository->write('docs/index.md', "---\ntitle: Introduction\n---\n\nHello");
        $repository->commit('First');
        $repository->tag('1.0.0');
        $stdout = $this->stream();
        $cli = new Cli($stdout, $this->stream());

        // Act
        $status = $cli->run([
            'build-all',
            '--library=biginteger',
            '--repo-path=' . $repository->directory->path,
            '--out=' . $this->directory->path . '/out',
        ]);

        // Assert
        static::assertSame(0, $status);
        static::assertSame("Built the versions master, 1.0.0.\n", $this->contents($stdout));
        static::assertFileExists($this->directory->path . '/out/1.0.0/index.html');
        static::assertFileExists($this->directory->path . '/out/versions.json');

        $repository->remove();
    }

    public function testIndexWritesTheVersionFiles(): void
    {
        // Arrange
        $stdout = $this->stream();
        $cli = new Cli($stdout, $this->stream());

        // Act
        $status = $cli->run([
            'index',
            '--out=' . $this->directory->path . '/out',
            '--latest=3.x',
            '--versions=3.x, 2.x',
        ]);

        // Assert
        static::assertSame(0, $status);
        static::assertSame("Wrote the version index for 3.x, 2.x.\n", $this->contents($stdout));
        static::assertFileExists($this->directory->path . '/out/versions.json');
    }

    public function testMissingOptionFailsWithAMessage(): void
    {
        // Arrange
        $stderr = $this->stream();
        $cli = new Cli($this->stream(), $stderr);

        // Act
        $status = $cli->run(['build', '--library=biginteger']);

        // Assert
        static::assertSame(1, $status);
        static::assertSame("Missing the required option --source.\n", $this->contents($stderr));
    }

    public function testUnknownCommandPrintsTheUsage(): void
    {
        // Arrange
        $stderr = $this->stream();
        $cli = new Cli($this->stream(), $stderr);

        // Act
        $status = $cli->run(['nope']);

        // Assert
        static::assertSame(1, $status);
        static::assertStringContainsString('Usage:', $this->contents($stderr));
    }

    /** @return resource */
    private function stream(): mixed
    {
        $stream = fopen('php://memory', 'w+');
        static::assertIsResource($stream);

        return $stream;
    }

    /** @param resource $stream */
    private function contents(mixed $stream): string
    {
        rewind($stream);

        return (string)stream_get_contents($stream);
    }

    public function testIgnoresArgumentsThatAreNotOptions(): void
    {
        // Arrange
        $this->directory->write('src/index.md', "---\ntitle: Introduction\n---\n\nHello");
        $cli = new Cli($this->stream(), $this->stream());

        // Act
        $status = $cli->run([
            'build',
            'positional',
            '--flag',
            '--library=biginteger',
            '--version=3.x',
            '--source=' . $this->directory->path . '/src',
            '--out=' . $this->directory->path . '/out',
        ]);

        // Assert
        static::assertSame(0, $status);
    }
}
