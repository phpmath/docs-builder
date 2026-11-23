<?php
/**
 * @var \PHPMath\DocsBuilder\Page $page
 * @var list<\PHPMath\DocsBuilder\Page> $pages
 * @var \PHPMath\DocsBuilder\BuildOptions $options
 * @var string $logo
 * @var \PHPMath\DocsBuilder\Page|null $previous
 * @var \PHPMath\DocsBuilder\Page|null $next
 */
$e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES);
$groups = [];
foreach ($pages as $item) {
    $groups[$item->group][] = $item;
}
$library = $options->library;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $e($page->title) ?> | phpmath/<?= $e($library) ?></title>
<meta name="description" content="<?= $e($page->intro) ?>">
<meta name="theme-color" content="#4f46e5">
<meta property="og:type" content="website">
<meta property="og:site_name" content="phpmath">
<meta property="og:title" content="<?= $e($page->title) ?> | phpmath/<?= $e($library) ?>">
<meta property="og:description" content="<?= $e($page->intro) ?>">
<meta property="og:image" content="https://phpmath.github.io/assets/og.png">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="/assets/style.css">
<link rel="stylesheet" href="/assets/docs.css">
</head>
<body>
<header class="site"><div class="wrap">
  <a class="brand" href="/" aria-label="phpmath home"><?= $logo ?></a>
  <nav aria-label="Main"><a href="/#libraries">Libraries</a><a href="https://packagist.org/packages/phpmath/<?= $e($library) ?>">Packagist</a><a href="https://github.com/<?= $e($options->repository) ?>">GitHub</a></nav>
</div></header>
<div class="docs">
  <aside class="sidebar" id="sidebar">
    <button class="navtoggle" type="button" aria-expanded="false" aria-controls="navbody">Menu</button>
    <div class="navbody" id="navbody">
      <a class="lib" href="index.html">phpmath/<?= $e($library) ?></a>
      <label class="sr" for="version">Documentation version</label>
      <select class="version" id="version" data-base="/<?= $e($library) ?>/" data-current="<?= $e($options->version) ?>" data-file="<?= $e($page->fileName()) ?>" disabled><option><?= $e($options->version) ?></option></select>
<?php foreach ($groups as $name => $items): ?>
      <h4><?= $e((string)$name) ?></h4><ul>
<?php foreach ($items as $item): ?>
        <li><a href="<?= $e($item->fileName()) ?>"<?= $item->name === $page->name ? ' aria-current="page"' : '' ?>><?= $e($item->title) ?></a></li>
<?php endforeach; ?>
      </ul>
<?php endforeach; ?>
    </div>
  </aside>
  <main class="content">
    <h1><?= $e($page->title) ?></h1>
<?php if ($page->intro !== ''): ?>
    <p class="intro"><?= $e($page->intro) ?></p>
<?php endif; ?>
    <?= $page->html ?>
    <div class="pager">
<?php if ($previous !== null): ?>
      <a href="<?= $e($previous->fileName()) ?>"><small>Previous</small><?= $e($previous->title) ?></a>
<?php else: ?>
      <span></span>
<?php endif; ?>
<?php if ($next !== null): ?>
      <a href="<?= $e($next->fileName()) ?>" style="text-align:right"><small>Next</small><?= $e($next->title) ?></a>
<?php else: ?>
      <span></span>
<?php endif; ?>
    </div>
  </main>
</div>
<footer><div class="wrap">© phpmath contributors · Released under the MIT license · <a href="https://github.com/<?= $e($options->repository) ?>/edit/<?= $e($options->editBranch) ?>/<?= $e($options->editPath) ?>/<?= $e($page->name) ?>.md">Edit this page</a></div></footer>
<script src="/assets/docs.js"></script>
</body>
</html>
