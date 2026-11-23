<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder\Tests\GitRepository;

use PHPMath\DocsBuilder\Exception\InvalidDocumentException;
use PHPMath\DocsBuilder\GitRepository;
use PHPMath\DocsBuilder\Tests\TemporaryGitRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

use function file_get_contents;

#[CoversClass(GitRepository::class)]
#[CoversMethod(GitRepository::class, 'exportDirectory')]
final class ExportDirectoryTest extends TestCase
{
    private TemporaryGitRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new TemporaryGitRepository();
        $this->repository->write('docs/index.md', "---\ntitle: One\n---\n\nFirst");
        $this->repository->commit('First');
    }

    protected function tearDown(): void
    {
        $this->repository->remove();
    }

    public function testExportsTheDirectoryAsItWasAtTheRef(): void
    {
        // Arrange
        $git = new GitRepository($this->repository->directory->path);
        $this->repository->tag('1.0.0');
        $this->repository->write('docs/index.md', 'changed');
        $this->repository->commit('Second');
        $target = $this->repository->directory->path . '/export';

        // Act
        $git->exportDirectory('1.0.0', 'docs', $target);

        // Assert
        self::assertStringContainsString('First', (string)file_get_contents($target . '/docs/index.md'));
    }

    public function testThrowsForAnUnknownRef(): void
    {
        // Arrange
        $git = new GitRepository($this->repository->directory->path);
        $target = $this->repository->directory->path . '/export';

        // Assert
        $this->expectException(InvalidDocumentException::class);
        $this->expectExceptionMessage('The git command "git archive nope" failed');

        // Act
        $git->exportDirectory('nope', 'docs', $target);
    }
}
