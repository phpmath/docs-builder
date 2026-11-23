<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder\Tests\GitRepository;

use PHPMath\DocsBuilder\GitRepository;
use PHPMath\DocsBuilder\Tests\TemporaryGitRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversClass(GitRepository::class)]
#[CoversMethod(GitRepository::class, 'hasBranch')]
final class HasBranchTest extends TestCase
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

    public function testFindsAnExistingBranch(): void
    {
        // Arrange
        $git = new GitRepository($this->repository->directory->path);

        // Act
        $result = $git->hasBranch('master');

        // Assert
        self::assertTrue($result);
    }

    public function testDoesNotFindAMissingBranch(): void
    {
        // Arrange
        $git = new GitRepository($this->repository->directory->path);

        // Act
        $result = $git->hasBranch('nope');

        // Assert
        self::assertFalse($result);
    }

    public function testDoesNotTreatATagAsABranch(): void
    {
        // Arrange
        $git = new GitRepository($this->repository->directory->path);
        $this->repository->tag('1.0.0');

        // Act
        $result = $git->hasBranch('1.0.0');

        // Assert
        self::assertFalse($result);
    }
}
