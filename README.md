# docs-builder

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![CI][ico-ci]][link-ci]
[![Total Downloads][ico-downloads]][link-downloads]

Builds the documentation site of a phpmath library from Markdown files. Every library uses the
same builder and the same theme, so all documentation looks and works the same.

## Documentation

The full documentation is at https://phpmath.github.io/docs-builder/. Every release is described on the [releases page](https://github.com/phpmath/docs-builder/releases).

## Install

```bash
composer require phpmath/docs-builder
```

## Requirements

- PHP 8.4 or newer
- `league/commonmark`
- `symfony/yaml`
- `tempest/highlight`

## Usage

Put the Markdown files in `docs/`. There is no version in the path: the version of a page is the name of the git
reference it was built from.

- The `master` branch is the development version. It describes the code as it is now.
- Every tag (`1.2.3` or `v1.2.3`) is its own version, built from the `docs/` folder as it was at that tag.
- Other branches are ignored, and so are tags without a `docs/` folder.
- The latest version is the highest tag without a prerelease suffix, or `master` while there are no tags.

Build all versions of a repository, which is what CI does:

``` bash
$ vendor/bin/docs-builder build-all --library=biginteger --out=build/site
```

This writes `build/site/<version>/*.html` for every version, plus `versions.json` for the version selector, an
`index.html` that redirects to the latest version and a `sitemap.xml` that lists the pages of the latest version
(with the address `https://phpmath.github.io/<library>/` as the base, change it with `--base-url`). The repository needs its tags (in GitHub Actions use
`fetch-depth: 0`). Read the branch and the tags from git, not from the working tree, so uncommitted changes are
not included.

To preview the working tree, for example before the first commit, build one directory as `master`:

``` bash
$ vendor/bin/docs-builder build --library=biginteger --version=master --source=docs --out=build/site --edit-path=docs
$ vendor/bin/docs-builder index --out=build/site --latest=master --versions=master --development=master
```

| Command | Option | Description |
|---|---|---|
| all | `--library` | The library name, for example `biginteger`. |
| all | `--out` | The output folder. |
| `build-all` | `--repo-path` | The git repository. Defaults to the current folder. |
| `build-all` | `--docs-dir` | The documentation folder in the repository. Defaults to `docs`. |
| `build-all` | `--branch` | The development branch. Defaults to `master`. |
| `build`, `build-all` | `--repository` | The GitHub repository. Defaults to `phpmath/<library>`. |
| `build-all` | `--base-url` | The public address of the documentation, used in the sitemap. Defaults to `https://phpmath.github.io/<library>/`. |
| `build` | `--version` | The version, used as the output folder and shown in the selector. |
| `build` | `--source` | The folder with the Markdown files. |
| `build` | `--edit-path` | The folder of the Markdown files in the repository, for the edit links. Defaults to `--source`. |
| `build` | `--edit-branch` | The branch the edit links point to. Defaults to `master`. |
| `index` | `--latest` | The version the root redirects to. |
| `index` | `--versions` | A comma separated list of all versions. |
| `index` | `--development` | The version that holds the unreleased docs. The selector labels it "development". |

## Publishing with GitHub Actions

`.github/workflows/docs.yml` in this repository is a reusable workflow. It checks out the library with all its
tags, runs `build-all` and deploys the result to GitHub Pages. A library calls it with a small workflow of its own
(`.github/workflows/docs.yml`):

``` yaml
name: Documentation

on:
  push:
    branches: [ master ]
    tags: [ '*.*.*', 'v*.*.*' ]
  workflow_dispatch:

permissions:
  contents: read
  pages: write
  id-token: write

concurrency:
  group: pages
  cancel-in-progress: false

jobs:
  docs:
    uses: phpmath/docs-builder/.github/workflows/docs.yml@master
    with:
      library: biginteger
```

In the settings of the library repository, set the Pages source to "GitHub Actions". The site is then served at
`https://phpmath.github.io/<library>/`.

## Writing pages

Each page is a Markdown file that starts with front matter:

``` markdown
---
title: Arithmetic
intro: Add, subtract, multiply and divide.
group: Guides
order: 30
---
```

| Key | Required | Description |
|---|---|---|
| `title` | yes | The page title. |
| `intro` | no | One sentence shown under the title and used as the meta description. |
| `group` | no | The sidebar heading. Groups appear in the order of their first page. |
| `order` | no | The position of the page. Pages without one go last, sorted by file name. |
| `layout` | no | Set to `reference` to render every third level heading as a box. |

What you can use in the body:

- Standard Markdown, GitHub style tables and fenced code blocks. Code is highlighted at build time. Use `php`,
  `bash`, `json`, `html`, `xml` or `yaml` as the language.
- Links to other pages as `other-page.md` or `other-page.md#section`. They become `.html` links.
- Callouts:

  ``` markdown
  > [!NOTE]
  > Something worth knowing.

  > [!WARNING]
  > Something that can go wrong.
  ```

- Raw HTML, for example the link cards on an introduction page.

Second and third level headings get an anchor link automatically.

## The theme

The pages load `/assets/style.css` and `/assets/docs.css` from the organization site
(`phpmath.github.io`), so every library picks up theme changes without a rebuild. Preview locally by
serving a copy of the organization site with the built folder linked in as `/<library>/`.

## Development

```bash
composer test
composer test-coverage
composer analyse
composer check-style
composer fix-style
```

## Contributing

All contributions are welcome. Open an issue or a pull request on GitHub. Please follow the coding standard and add tests.

## Security

If you discover a security issue, please create an issue in the issue tracker.

## Credits

- [Walter Tamboer](https://github.com/waltertamboer)
- [All contributors](https://github.com/phpmath/docs-builder/graphs/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/phpmath/docs-builder.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-ci]: https://img.shields.io/github/actions/workflow/status/phpmath/docs-builder/ci.yml?branch=master&style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/phpmath/docs-builder.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/phpmath/docs-builder
[link-ci]: https://github.com/phpmath/docs-builder/actions/workflows/ci.yml
[link-downloads]: https://packagist.org/packages/phpmath/docs-builder/stats
