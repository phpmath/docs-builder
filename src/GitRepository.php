<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder;

use Phar;
use PharData;
use PHPMath\DocsBuilder\Exception\InvalidDocumentException;

use function explode;
use function fclose;
use function file_put_contents;
use function implode;
use function is_resource;
use function proc_close;
use function proc_open;
use function stream_get_contents;
use function sys_get_temp_dir;
use function trim;
use function uniqid;
use function unlink;

/**
 * Reads documentation from a git repository without touching its working tree.
 */
final readonly class GitRepository
{
    public function __construct(private string $path)
    {
    }

    /** @return list<string> The names of all tags. */
    public function tags(): array
    {
        $output = trim($this->run(['tag', '--list'])['output']);

        return $output === '' ? [] : explode("\n", $output);
    }

    public function hasBranch(string $branch): bool
    {
        return $this->run(['rev-parse', '--verify', '--quiet', 'refs/heads/' . $branch])['status'] === 0;
    }

    public function hasDirectory(string $ref, string $directory): bool
    {
        return $this->run(['cat-file', '-e', $ref . ':' . $directory])['status'] === 0;
    }

    /**
     * Writes the contents of a directory, as it was at the given branch or tag, below the target directory.
     * The directory keeps its name, so docs/ ends up at target/docs.
     */
    public function exportDirectory(string $ref, string $directory, string $target): void
    {
        $archive = $this->run(['archive', '--format=tar', $ref, $directory]);

        if ($archive['status'] !== 0) {
            throw InvalidDocumentException::gitFailed('git archive ' . $ref, $archive['output']);
        }

        $file = sys_get_temp_dir() . '/docs-builder-' . uniqid('', true) . '.tar';

        // @codeCoverageIgnoreStart
        if (file_put_contents($file, $archive['output']) === false) {
            throw InvalidDocumentException::cannotWrite($file);
        }

        // @codeCoverageIgnoreEnd

        try {
            (new PharData($file, 0, null, Phar::TAR))->extractTo($target, null, true);
        } finally {
            unlink($file);
        }
    }

    /**
     * @param list<string> $arguments
     *
     * @return array{status: int, output: string}
     */
    private function run(array $arguments): array
    {
        $process = proc_open(
            ['git', '-C', $this->path, ...$arguments],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
        );

        // @codeCoverageIgnoreStart
        if (!is_resource($process)) {
            throw InvalidDocumentException::gitFailed('git ' . implode(' ', $arguments), 'Cannot start git.');
        }

        // @codeCoverageIgnoreEnd

        $output = (string)stream_get_contents($pipes[1]);
        $errors = (string)stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $status = proc_close($process);

        return ['status' => $status, 'output' => $status === 0 ? $output : $errors];
    }
}
