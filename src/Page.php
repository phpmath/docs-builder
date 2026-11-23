<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder;

/**
 * A rendered documentation page.
 */
final readonly class Page
{
    /**
     * @param string $name  The file name without extension, for example "arithmetic".
     * @param string $title The page title.
     * @param string $intro The short introduction shown under the title.
     * @param string $group The sidebar group the page belongs to.
     * @param int $order The sort position of the page.
     * @param string $html  The rendered body.
     */
    public function __construct(
        public string $name,
        public string $title,
        public string $intro,
        public string $group,
        public int $order,
        public string $html,
    ) {
    }

    public function fileName(): string
    {
        return $this->name . '.html';
    }
}
