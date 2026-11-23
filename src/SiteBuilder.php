<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder;

use PHPMath\DocsBuilder\Exception\InvalidDocumentException;

use function array_diff;
use function array_filter;
use function array_map;
use function array_values;
use function basename;
use function is_dir;
use function ltrim;
use function mkdir;
use function preg_match;
use function rmdir;
use function rtrim;
use function scandir;
use function str_contains;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use function usort;
use function version_compare;

/**
 * Builds the documentation of a whole repository: the development branch and every tag.
 *
 * The version of a page is the name of the git reference it was built from. Other branches are ignored, and so
 * are tags that do not contain a documentation directory.
 */
final readonly class SiteBuilder
{
    private const string TAG_PATTERN = '~^v?\d+\.\d+\.\d+(?:[-+].+)?$~';

    public function __construct(
        private GitRepository $git,
        private string $library,
        private string $repository,
        private string $docsDirectory,
        private string $branch,
        private string $output,
        private ?string $baseUrl = null,
    ) {
    }

    /**
     * Builds all versions, the version index and the sitemap of the latest version.
     *
     * @return list<string> The built versions: the development branch first, then the tags from new to old.
     */
    public function build(): array
    {
        if (!$this->git->hasBranch($this->branch) || !$this->git->hasDirectory($this->branch, $this->docsDirectory)) {
            throw InvalidDocumentException::missingBranch($this->branch);
        }

        $tags = array_values(array_filter(
            $this->sortedTags(),
            fn (string $tag): bool => $this->git->hasDirectory($tag, $this->docsDirectory),
        ));

        $versions = [$this->branch, ...$tags];

        $pages = [];

        foreach ($versions as $version) {
            $pages[$version] = $this->buildVersion($version);
        }

        $stable = array_values(array_filter($tags, static fn (string $tag): bool => !str_contains($tag, '-')));
        $latest = $stable[0] ?? $this->branch;

        VersionIndex::write($this->output, $latest, $versions, $this->branch);
        Sitemap::write(
            $this->output,
            $this->baseUrl ?? 'https://phpmath.github.io/' . $this->library . '/',
            $latest,
            $pages[$latest],
        );

        return $versions;
    }

    /** @return list<string> The release tags, newest first. */
    private function sortedTags(): array
    {
        $tags = array_values(array_filter(
            $this->git->tags(),
            static fn (string $tag): bool => preg_match(self::TAG_PATTERN, $tag) === 1,
        ));

        usort(
            $tags,
            static fn (string $a, string $b): int => version_compare(ltrim($b, 'v'), ltrim($a, 'v')),
        );

        return $tags;
    }

    /** @return list<string> The file names of the pages of the version. */
    private function buildVersion(string $ref): array
    {
        $directory = sys_get_temp_dir() . '/docs-builder-' . uniqid('', true);

        // @codeCoverageIgnoreStart
        if (!mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw InvalidDocumentException::cannotWrite($directory);
        }

        // @codeCoverageIgnoreEnd

        try {
            $this->git->exportDirectory($ref, $this->docsDirectory, $directory);

            $written = (new Builder(new BuildOptions(
                $this->library,
                $ref,
                $this->repository,
                rtrim($directory, '/') . '/' . $this->docsDirectory,
                $this->output,
                $this->docsDirectory,
                $this->branch,
            )))->build();

            return array_map(basename(...), $written);
        } finally {
            $this->remove($directory);
        }
    }

    private function remove(string $directory): void
    {
        foreach (array_diff(scandir($directory) ?: [], ['.', '..']) as $entry) {
            $path = $directory . '/' . $entry;

            if (is_dir($path)) {
                $this->remove($path);
            } else {
                unlink($path);
            }
        }

        rmdir($directory);
    }
}
