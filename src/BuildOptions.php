<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder;

/**
 * The settings for building the documentation of one library version.
 */
final readonly class BuildOptions
{
    /**
     * @param string $library    The library name, for example "biginteger".
     * @param string $version    The documentation version, for example "3.x".
     * @param string $repository The GitHub repository, for example "phpmath/biginteger".
     * @param string $source     The directory with the Markdown files.
     * @param string $output     The directory the site is written to. The version gets its own subdirectory.
     * @param string $editPath   The path of the Markdown files in the repository, used for the edit links.
     * @param string $editBranch The branch the edit links point to.
     */
    public function __construct(
        public string $library,
        public string $version,
        public string $repository,
        public string $source,
        public string $output,
        public string $editPath,
        public string $editBranch = 'master',
    ) {
    }
}
