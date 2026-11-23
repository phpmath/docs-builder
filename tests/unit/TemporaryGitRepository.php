<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder\Tests;

use RuntimeException;

use function array_map;
use function escapeshellarg;
use function exec;
use function implode;
use function unlink;

/**
 * A temporary git repository for tests.
 */
final readonly class TemporaryGitRepository
{
    public TemporaryDirectory $directory;

    public function __construct()
    {
        $this->directory = new TemporaryDirectory();
        $this->git('init', '-q', '-b', 'master');
    }

    public function write(string $relativePath, string $content): void
    {
        $this->directory->write($relativePath, $content);
    }

    public function delete(string $relativePath): void
    {
        unlink($this->directory->path . '/' . $relativePath);
    }

    public function commit(string $message): void
    {
        $this->git('add', '-A');
        $this->git('-c', 'user.name=Test', '-c', 'user.email=test@example.com', 'commit', '-q', '-m', $message);
    }

    public function tag(string $name): void
    {
        $this->git('tag', $name);
    }

    public function branch(string $name): void
    {
        $this->git('checkout', '-q', '-b', $name);
    }

    public function checkout(string $name): void
    {
        $this->git('checkout', '-q', $name);
    }

    public function remove(): void
    {
        $this->directory->remove();
    }

    private function git(string ...$arguments): void
    {
        $command = 'git -C ' . escapeshellarg($this->directory->path) . ' '
            . implode(' ', array_map(escapeshellarg(...), $arguments)) . ' 2>&1';

        exec($command, $output, $status);

        if ($status !== 0) {
            throw new RuntimeException($command . ': ' . implode("\n", $output));
        }
    }
}
