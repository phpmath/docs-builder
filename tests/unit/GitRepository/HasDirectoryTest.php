<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder\Tests\GitRepository;

use PHPMath\DocsBuilder\GitRepository;
use PHPMath\DocsBuilder\Tests\TemporaryGitRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversClass(GitRepository::class)]
#[CoversMethod(GitRepository::class, 'hasDirectory')]
final class HasDirectoryTest extends TestCase
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

    public function testFindsADirectoryAtARef(): void
    {
        // Arrange
        $git = new GitRepository($this->repository->directory->path);

        // Act
        $result = $git->hasDirectory('master', 'docs');

        // Assert
        self::assertTrue($result);
    }

    public function testDoesNotFindAMissingDirectory(): void
    {
        // Arrange
        $git = new GitRepository($this->repository->directory->path);

        // Act
        $result = $git->hasDirectory('master', 'other');

        // Assert
        self::assertFalse($result);
    }
}
