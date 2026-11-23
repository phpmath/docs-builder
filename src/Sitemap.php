<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder;

use PHPMath\DocsBuilder\Exception\InvalidDocumentException;

use function file_put_contents;
use function htmlspecialchars;
use function implode;
use function rtrim;

use const ENT_QUOTES;
use const ENT_XML1;

/**
 * Writes the sitemap of the documentation of one library.
 *
 * Only the pages of one version are listed, normally the latest release, so that search engines index one
 * version of every page.
 */
final class Sitemap
{
    /**
     * @param string $baseUrl   The address of the documentation, for example https://phpmath.github.io/matrix/.
     * @param string $version   The version whose pages are listed.
     * @param list<string> $pageFiles The file names of the pages, for example index.html.
     *
     * @return string The path of the written file.
     */
    public static function write(string $output, string $baseUrl, string $version, array $pageFiles): string
    {
        $urls = [];

        foreach ($pageFiles as $file) {
            $location = rtrim($baseUrl, '/') . '/' . $version . '/' . $file;
            $urls[] = '  <url><loc>' . htmlspecialchars($location, ENT_QUOTES | ENT_XML1) . '</loc></url>';
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n"
            . implode("\n", $urls) . "\n"
            . '</urlset>' . "\n";

        $path = rtrim($output, '/') . '/sitemap.xml';

        if (file_put_contents($path, $xml) === false) {
            throw InvalidDocumentException::cannotWrite($path);
        }

        return $path;
    }
}
