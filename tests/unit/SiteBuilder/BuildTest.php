<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder\Tests\SiteBuilder;

use PHPMath\DocsBuilder\Exception\InvalidDocumentException;
use PHPMath\DocsBuilder\GitRepository;
use PHPMath\DocsBuilder\SiteBuilder;
use PHPMath\DocsBuilder\Tests\TemporaryGitRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function json_decode;

#[CoversClass(SiteBuilder::class)]
#[CoversMethod(SiteBuilder::class, 'build')]
final class BuildTest extends TestCase
{
    private TemporaryGitRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new TemporaryGitRepository();
        $this->repository->write('docs/index.md', "---\ntitle: Doc\n---\n\nFirst");
        $this->repository->commit('First');
    }

    protected function tearDown(): void
    {
        $this->repository->remove();
    }

    public function testBuildsTheBranchAndEveryTag(): void
    {
        // Arrange
        $out = $this->repository->directory->path . '/out';
        $this->repository->tag('1.0.0');
        $this->repository->write('docs/index.md', "---\ntitle: Doc\n---\n\nSecond");
        $this->repository->commit('Second');
        $this->repository->tag('1.1.0');
        $this->repository->write('docs/index.md', "---\ntitle: Doc\n---\n\nThird");
        $this->repository->commit('Third');
        $builder = new SiteBuilder(
            new GitRepository($this->repository->directory->path),
            'biginteger',
            'phpmath/biginteger',
            'docs',
            'master',
            $out,
        );

        // Act
        $versions = $builder->build();

        // Assert
        self::assertSame(['master', '1.1.0', '1.0.0'], $versions);
        self::assertStringContainsString('First', (string)file_get_contents($out . '/1.0.0/index.html'));
        self::assertStringContainsString('Second', (string)file_get_contents($out . '/1.1.0/index.html'));
        self::assertStringContainsString('Third', (string)file_get_contents($out . '/master/index.html'));
    }

    public function testWritesTheVersionIndex(): void
    {
        // Arrange
        $out = $this->repository->directory->path . '/out';
        $this->repository->tag('1.0.0');
        $builder = new SiteBuilder(
            new GitRepository($this->repository->directory->path),
            'biginteger',
            'phpmath/biginteger',
            'docs',
            'master',
            $out,
        );

        // Act
        $builder->build();

        // Assert
        $data = json_decode((string)file_get_contents($out . '/versions.json'), true);
        self::assertSame(['latest' => '1.0.0', 'versions' => ['master', '1.0.0'], 'development' => 'master'], $data);
        self::assertStringContainsString('url=1.0.0/', (string)file_get_contents($out . '/index.html'));
    }

    public function testLatestIsTheHighestStableTag(): void
    {
        // Arrange
        $out = $this->repository->directory->path . '/out';
        $this->repository->tag('1.2.0');
        $this->repository->tag('1.10.0');
        $this->repository->tag('2.0.0-beta1');
        $builder = new SiteBuilder(
            new GitRepository($this->repository->directory->path),
            'biginteger',
            'phpmath/biginteger',
            'docs',
            'master',
            $out,
        );

        // Act
        $versions = $builder->build();

        // Assert
        self::assertSame(['master', '2.0.0-beta1', '1.10.0', '1.2.0'], $versions);
        $data = json_decode((string)file_get_contents($out . '/versions.json'), true);
        self::assertIsArray($data);
        self::assertSame('1.10.0', $data['latest']);
    }

    public function testLatestIsTheBranchWhenThereAreNoTags(): void
    {
        // Arrange
        $out = $this->repository->directory->path . '/out';
        $builder = new SiteBuilder(
            new GitRepository($this->repository->directory->path),
            'biginteger',
            'phpmath/biginteger',
            'docs',
            'master',
            $out,
        );

        // Act
        $builder->build();

        // Assert
        $data = json_decode((string)file_get_contents($out . '/versions.json'), true);
        self::assertIsArray($data);
        self::assertSame('master', $data['latest']);
    }

    public function testIgnoresOtherBranches(): void
    {
        // Arrange
        $out = $this->repository->directory->path . '/out';
        $this->repository->branch('feature');
        $this->repository->write('docs/index.md', "---\ntitle: Doc\n---\n\nFeature");
        $this->repository->commit('Feature');
        $this->repository->checkout('master');
        $builder = new SiteBuilder(
            new GitRepository($this->repository->directory->path),
            'biginteger',
            'phpmath/biginteger',
            'docs',
            'master',
            $out,
        );

        // Act
        $versions = $builder->build();

        // Assert
        self::assertSame(['master'], $versions);
        self::assertDirectoryDoesNotExist($out . '/feature');
        self::assertStringNotContainsString('Feature', (string)file_get_contents($out . '/master/index.html'));
    }

    public function testIgnoresTagsThatAreNotVersions(): void
    {
        // Arrange
        $out = $this->repository->directory->path . '/out';
        $this->repository->tag('nightly');
        $builder = new SiteBuilder(
            new GitRepository($this->repository->directory->path),
            'biginteger',
            'phpmath/biginteger',
            'docs',
            'master',
            $out,
        );

        // Act
        $versions = $builder->build();

        // Assert
        self::assertSame(['master'], $versions);
    }

    public function testSkipsTagsWithoutDocumentation(): void
    {
        // Arrange
        $out = $this->repository->directory->path . '/out';
        $this->repository->delete('docs/index.md');
        $this->repository->commit('Remove docs');
        $this->repository->tag('0.9.0');
        $this->repository->write('docs/index.md', "---\ntitle: Doc\n---\n\nBack");
        $this->repository->commit('Add docs');
        $builder = new SiteBuilder(
            new GitRepository($this->repository->directory->path),
            'biginteger',
            'phpmath/biginteger',
            'docs',
            'master',
            $out,
        );

        // Act
        $versions = $builder->build();

        // Assert
        self::assertSame(['master'], $versions);
        self::assertDirectoryDoesNotExist($out . '/0.9.0');
    }

    public function testMarksThePageWithItsVersion(): void
    {
        // Arrange
        $out = $this->repository->directory->path . '/out';
        $this->repository->tag('1.0.0');
        $builder = new SiteBuilder(
            new GitRepository($this->repository->directory->path),
            'biginteger',
            'phpmath/biginteger',
            'docs',
            'master',
            $out,
        );

        // Act
        $builder->build();

        // Assert
        $html = (string)file_get_contents($out . '/1.0.0/index.html');
        self::assertStringContainsString('data-current="1.0.0"', $html);
        self::assertStringContainsString('/edit/master/docs/index.md', $html);
    }

    public function testThrowsWhenTheBranchDoesNotExist(): void
    {
        // Arrange
        $out = $this->repository->directory->path . '/out';
        $builder = new SiteBuilder(
            new GitRepository($this->repository->directory->path),
            'biginteger',
            'phpmath/biginteger',
            'docs',
            'main',
            $out,
        );

        // Assert
        $this->expectException(InvalidDocumentException::class);
        $this->expectExceptionMessage('The branch "main" does not exist or has no documentation.');

        // Act
        $builder->build();
    }
}
