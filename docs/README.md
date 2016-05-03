# VersionPress Documentation

Content of user documentation, eventually published to [docs.versionpress.net](http://docs.versionpress.net/en).


## Overview

VersionPress uses a docs system inspired by [Composer Docs](https://github.com/composer/composer/tree/master/doc), [Git Book](https://github.com/progit/progit) or [Azure Docs](https://github.com/Azure/azure-content/). The content is authored as a set of Markdown files in this repo and eventually published at [docs.versionpress.net](http://docs.versionpress.net/en).

Documentation is written in **Markdown**, specifically in the [MarkdownDeep dialect](http://www.toptensoftware.com/markdowndeep/) with extra mode switched on. This makes it pretty close to GitHub Flavored Markdown (GFM) although there might be some differences. <small>(We will switch to GFM one day.)</small>

Content is organized in **the `content` directory**:

![Content structure](https://cloud.githubusercontent.com/assets/101152/14105777/ee4fc5da-f5ad-11e5-86b1-ec73ac35419e.png)

**URLs** map to this structure pretty closely, basically just omitting the two-digit prefixes (the purpose of which is just to order things) and the file extensions. For example, `content/en/03-sync/02-cloning.md` is available at `/en/sync/cloning`.

**Section home pages** are represented by special `_index.md` files. For example, `content/en/03-sync/_index.md` is available at `/en/sync`.

**Site navigation** also reflects the file / folder structure, both in the sidebar and the "Next / Previous" links at the bottom of each topic. Documents' H1 determine the texts rendered.


## Docs versioning

We use the [Feature Toggle approach](http://martinfowler.com/bliki/FeatureToggle.html) to maintain multiple versions of the documentation. There are no branches like `versionpress-1.0` or `versionpress-2.0`, all is committed into the `master` and rendered based on *version toggles*.

The system works like this:

 - Doc topics optionally specify which version they apply to using the `since:` tag. For example, the topic on WP-CLI commands is available since VersionPress 2.0 and [the file](https://raw.githubusercontent.com/versionpress/docs/3ad7f2728b7134d2d7fd19b753b210d0c7b38871/content/en/02-feature-focus/10-wp-cli.md) indicates this with `since: 2.0` in its [front matter](http://jekyllrb.com/docs/frontmatter/).
 - The global configuration indicates which version to render, and for example if the shown version should be 1.0, the topic on WP-CLI is excluded from the navigation.

The `since:` tag can be specified either for a specific page at the top of the Markdown document or for the whole section in its `config.yml`, see e.g. [the sync section's config.yml](https://github.com/versionpress/docs/blob/9738af100e640a525c2ae0119bc3060f175b65a9/content/en/03-sync/config.yml).



## Authoring documentation

 - **Start each file with an H1 header** (`# Some Title`). This MUST be the first non-front-matter line of the document; the navigation system depends on it.
 - Use **'Title Case' for H1** headers, **'Sentence case' for H2** and below.
 - **Images**: put them in the `content/media` folder, max 700px wide, optimize them and reference relatively. Include them into Markdown like this:

```
<figure style="width: 80%;">
  <img src="../../media/image.png" alt="Alt text" />
  <figcaption>Image caption</figcaption>
</figure>
```

 - **Notes / warnings / tips** can be written in special boxes. Supported CSS classes are `note` (green), `tip` (blue), `important` (orange) and `warning` (red), the syntax is:

```
<div class="note">
  <strong>Note title</strong>
  <p>This will be rendered in a highlighted box.</p>
</div>
```

 - **TODO markers** can be written as `[TODO]` or `[TODO some arbitrary text]`. They will be highlighted in yellow and should be used rarely, possibly in alpha / beta versions of the doc topic.


## Deploying docs

When a PR is merged into `master`, it is automatically deployed to [docs.versionpress.net](http://docs.versionpress.net/en).


## Localization

We currently only have an English version living in the `content/en` directory. In the future, localized versions will live in sibling directories. Contributions welcome.

## Redirects

Simple redirects can be added project's `config.yml` placed in repository root. Currently only plain strings (see below) can be used. If some rules are found in `redirects` section, exact match on requested URL (without starting `/` and tailing query string) is performed. URLs used in matching rules should contain language and slug of parsed markdown document file. User is redirect to new url with HTTP status code `301` without query string preserved.

```
redirects:
  'en/getting-started/concepts': 'en/feature-focus'
```
