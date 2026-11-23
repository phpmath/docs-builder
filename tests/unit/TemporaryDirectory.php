<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder\Tests;

use function array_diff;
use function dirname;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function rmdir;
use function scandir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

/**
 * A temporary directory for tests that write files.
 */
final readonly class TemporaryDirectory
{
    public string $path;

    public function __construct()
    {
        $this->path = sys_get_temp_dir() . '/docs-builder-' . uniqid('', true);
        mkdir($this->path, 0755, true);
    }

    public function write(string $relativePath, string $content): void
    {
        $path = $this->path . '/' . $relativePath;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $content);
    }

    public function remove(): void
    {
        $this->removeRecursive($this->path);
    }

    private function removeRecursive(string $directory): void
    {
        foreach (array_diff(scandir($directory) ?: [], ['.', '..']) as $entry) {
            $path = $directory . '/' . $entry;

            if (is_dir($path)) {
                $this->removeRecursive($path);
            } else {
                unlink($path);
            }
        }

        rmdir($directory);
    }
}
