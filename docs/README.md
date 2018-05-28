# VersionPress docs

## Developer documentation

- [Plugin-Support.md](./en/developer/plugin-support.md)
- [Dev-Setup.md](./en/developer/setup.md)
- [Development-Process.md](./en/developer/development-process.md)

## User documentation

User documentation is authored in the `content` folder and published to [docs.versionpress.net](http://docs.versionpress.net/en), a site powered by [`versionpress/docs-site`](https://github.com/versionpress/docs-site).

### Overview

VersionPress uses a Python based docs system called [MkDocs](https://www.mkdocs.org/). The content is authored as a set of Markdown files in this repo, built through `mkdocs build` and eventually published at [docs.versionpress.net](http://docs.versionpress.net/en).

Documentation is authored in **Markdown**, specifically in the [MarkdownDeep dialect](http://www.toptensoftware.com/markdowndeep/) with extra mode switched on. This makes it pretty close to GitHub Flavored Markdown (GFM) although there might be some differences. <small>(We will switch to GFM one day.)</small> See [authoring tips below](#authoring-documentation).

Content is organized in **the `content` directory**:

![Content structure](https://cloud.githubusercontent.com/assets/101152/14105777/ee4fc5da-f5ad-11e5-86b1-ec73ac35419e.png)

**URLs** map to this structure pretty closely. `index.md` are special files representing section homepages. Some examples:

| File on disk                       | URL                |
| ---------------------------------- | ------------------ |
| `content/en/sync/cloning.md`       | `/en/sync/cloning` |
| `content/en/sync/index.md`         | `/en/sync`         |
| `content/en/index.md`              | `/en`              |

**Site navigation** also reflects the file / folder structure, both in the sidebar and the "Next / Previous" links at the bottom of each topic. **Documents' H1** determine the texts rendered.

We **don't really use docs versioning** via URL like "/latest" or "/v2", the state of the documentation in `master` should reflect all versions. If something has been deprecated or is new, just indicate it in the text.

> **Power user tip**:

### Authoring documentation

 - **Start each file with an H1 header** (`# Some Title`). This MUST be the first non-front-matter line of the document; the navigation system depends on it.
 - Use **'Title Case' for H1** headers, **'Sentence case' for H2** and below.
 - **Images**: max 700px wide, optimize them, paste to GitHub and copy the URL. Include them into Markdown like this:
    ```
    <figure style="width: 80%;">
      <img src="../../media/image.png" alt="Alt text" />
      <figcaption>Image caption</figcaption>
    </figure>
    ```
 - **Notes / warnings / tips** can be written in special boxes. Supported CSS classes are `note` (green), `tip` (blue), `important` (orange) and `warning` (red), the syntax is:
    ```
    <div class="note">
      <p><strong>Note title</strong></p>
      <p>This will be rendered in a highlighted box.</p>
    </div>
    ```
 - **TODO markers** can be written as `[TODO]` or `[TODO some arbitrary text]`. They will be highlighted in yellow and should be used rarely, possibly in alpha / beta versions of the doc topic.


### Deploying docs

When a PR is merged into `master`, it is automatically deployed to [docs.versionpress.net](http://docs.versionpress.net/en).


### Redirects

URL redirects can be specified in `content/config.yml` like so:

```
redirects:
  'en/getting-started/old-name': 'en/getting-started/new-name'
```

Note that there is no leading slash. This leads to HTTP 301 Moved Permanently.
