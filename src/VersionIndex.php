<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder;

use PHPMath\DocsBuilder\Exception\InvalidDocumentException;

use function array_keys;
use function file_put_contents;
use function htmlspecialchars;
use function in_array;
use function is_dir;
use function json_encode;
use function mkdir;
use function rtrim;
use function sprintf;

use const ENT_QUOTES;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

/**
 * Writes the files that tie the documentation versions together.
 *
 * versions.json feeds the version selector. The root index.html redirects to the latest version.
 */
final class VersionIndex
{
    /**
     * @param list<string> $versions
     * @param string|null $development The version that holds the unreleased documentation, if there is one.
     *
     * @return list<string> The written files.
     */
    public static function write(string $output, string $latest, array $versions, ?string $development = null): array
    {
        if (!in_array($latest, $versions, true)) {
            throw InvalidDocumentException::invalidOption(
                sprintf('The latest version "%s" is not in the version list.', $latest),
            );
        }

        $output = rtrim($output, '/');

        if (!is_dir($output) && !mkdir($output, 0755, true) && !is_dir($output)) {
            throw InvalidDocumentException::cannotWrite($output);
        }

        $data = ['latest' => $latest, 'versions' => $versions];

        if ($development !== null) {
            $data['development'] = $development;
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n";
        $target = htmlspecialchars($latest, ENT_QUOTES);
        $redirect = '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Documentation</title>'
            . '<meta http-equiv="refresh" content="0; url=' . $target . '/">'
            . '<link rel="canonical" href="' . $target . '/"></head>'
            . '<body><p><a href="' . $target . '/">Go to the latest documentation</a></p></body></html>' . "\n";

        $files = [$output . '/versions.json' => $json, $output . '/index.html' => $redirect];

        foreach ($files as $path => $content) {
            if (file_put_contents($path, $content) === false) {
                throw InvalidDocumentException::cannotWrite($path);
            }
        }

        return [...array_keys($files)];
    }
}
