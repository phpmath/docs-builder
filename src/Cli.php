<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder;

use PHPMath\DocsBuilder\Exception\InvalidDocumentException;

use function array_filter;
use function array_map;
use function array_shift;
use function array_values;
use function count;
use function explode;
use function fwrite;
use function implode;
use function str_contains;
use function str_starts_with;
use function substr;

use const STDERR;
use const STDOUT;

/**
 * The command line interface of the builder.
 */
final readonly class Cli
{
    private const string USAGE = <<<'TEXT'
        Usage:
          docs-builder build --library=NAME --version=VERSION --source=DIR --out=DIR
                             [--repository=OWNER/REPO] [--edit-path=PATH] [--edit-branch=master]
          docs-builder build-all --library=NAME --out=DIR [--repo-path=DIR] [--docs-dir=docs] [--branch=master]
                                 [--repository=OWNER/REPO]
          docs-builder index --out=DIR --latest=VERSION --versions=V1,V2 [--development=VERSION]

        build       Builds the pages of one directory into DIR/VERSION.
        build-all   Builds the branch and every tag of a git repository, and the version index.
        index       Writes versions.json and the redirect to the latest version into DIR.

        TEXT;

    /**
     * @param resource $stdout Where normal output is written.
     * @param resource $stderr Where errors are written.
     */
    public function __construct(private mixed $stdout = STDOUT, private mixed $stderr = STDERR)
    {
    }

    /** @param list<string> $arguments The arguments without the script name. */
    public function run(array $arguments): int
    {
        $command = array_shift($arguments);
        $options = $this->parseOptions($arguments);

        try {
            return match ($command) {
                'build' => $this->build($options),
                'build-all' => $this->buildAll($options),
                'index' => $this->index($options),
                default => $this->fail(self::USAGE),
            };
        } catch (InvalidDocumentException $exception) {
            return $this->fail($exception->getMessage() . "\n");
        }
    }

    /** @param array<string, string> $options */
    private function build(array $options): int
    {
        $library = $this->require($options, 'library');
        $source = $this->require($options, 'source');

        $files = (new Builder(new BuildOptions(
            $library,
            $this->require($options, 'version'),
            $options['repository'] ?? 'phpmath/' . $library,
            $source,
            $this->require($options, 'out'),
            $options['edit-path'] ?? $source,
            $options['edit-branch'] ?? 'master',
        )))->build();

        fwrite($this->stdout, 'Built ' . count($files) . " pages.\n");

        return 0;
    }

    /** @param array<string, string> $options */
    private function buildAll(array $options): int
    {
        $library = $this->require($options, 'library');

        $versions = (new SiteBuilder(
            new GitRepository($options['repo-path'] ?? '.'),
            $library,
            $options['repository'] ?? 'phpmath/' . $library,
            $options['docs-dir'] ?? 'docs',
            $options['branch'] ?? 'master',
            $this->require($options, 'out'),
        ))->build();

        fwrite($this->stdout, 'Built the versions ' . implode(', ', $versions) . ".\n");

        return 0;
    }

    /** @param array<string, string> $options */
    private function index(array $options): int
    {
        $names = array_map('trim', explode(',', $this->require($options, 'versions')));
        $versions = array_values(array_filter($names, static fn (string $version): bool => $version !== ''));

        VersionIndex::write(
            $this->require($options, 'out'),
            $this->require($options, 'latest'),
            $versions,
            $options['development'] ?? null,
        );

        fwrite($this->stdout, 'Wrote the version index for ' . implode(', ', $versions) . ".\n");

        return 0;
    }

    /**
     * @param list<string> $arguments
     *
     * @return array<string, string>
     */
    private function parseOptions(array $arguments): array
    {
        $options = [];

        foreach ($arguments as $argument) {
            if (!str_starts_with($argument, '--') || !str_contains($argument, '=')) {
                continue;
            }

            [$key, $value] = explode('=', substr($argument, 2), 2);
            $options[$key] = $value;
        }

        return $options;
    }

    /** @param array<string, string> $options */
    private function require(array $options, string $key): string
    {
        if (!isset($options[$key]) || $options[$key] === '') {
            throw InvalidDocumentException::invalidOption('Missing the required option --' . $key . '.');
        }

        return $options[$key];
    }

    private function fail(string $message): int
    {
        fwrite($this->stderr, $message);

        return 1;
    }
}
