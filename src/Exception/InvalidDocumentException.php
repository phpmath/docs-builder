<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder\Exception;

use RuntimeException;

use function sprintf;

/**
 * Thrown when a documentation source file or option is invalid.
 */
final class InvalidDocumentException extends RuntimeException
{
    public static function missingFrontMatter(string $file, string $key): self
    {
        return new self(sprintf('%s: the front matter is missing the "%s" key.', $file, $key));
    }

    public static function invalidFrontMatter(string $file, string $key, string $type): self
    {
        return new self(sprintf('%s: the front matter key "%s" must be %s.', $file, $key, $type));
    }

    public static function noPages(string $directory): self
    {
        return new self(sprintf('No Markdown files found in %s.', $directory));
    }

    public static function cannotWrite(string $path): self
    {
        return new self(sprintf('Cannot write to %s.', $path));
    }

    public static function cannotRead(string $path): self
    {
        return new self(sprintf('Cannot read %s.', $path));
    }

    public static function gitFailed(string $command, string $output): self
    {
        return new self(sprintf('The git command "%s" failed: %s', $command, $output));
    }

    public static function missingBranch(string $branch): self
    {
        return new self(sprintf('The branch "%s" does not exist or has no documentation.', $branch));
    }

    public static function invalidOption(string $message): self
    {
        return new self($message);
    }
}
