# VersionPress docs

The documentation at [`docs.versionpress.net`](https://docs.versionpress.net/) is authored in Markdown and built using [MkDocs](https://www.mkdocs.org/).

## Updating documentation

1. Edit Markdown files in the `content` directory.
2. Live-preview the changes by starting up Docker, running `npm start` and visiting <http://localhost:8000>.
3. Submit a pull request with your changes.

If you want to update the site visuals, please see [theme info](#theme-info).

## Authoring tips

### Site navigation

Site navigation is defined in `mkdocs.yml`. This file must be manually updated whenever a new file is added or an existing file moved.

### Links

Links should be written as **relative** and **ending with .md**, for example, `[configuration](../getting-started/configuration.md)`. This form ensures that links work both on GitHub and rendered on `docs.versionpress.net`.

### Title casing

- Use **Title Case** for H1 headers.
- Use **Sentence case** for H2 and below.

### Images

- Try to avoid large images, e.g., screenshots taken on retina displays.
- Optimize via [TinyPNG](https://tinypng.com/) or similar.
- Paste to a GitHub comment field which produces a Markdown like `![image](https://user-images.githubusercontent.com/image-id-1234.png)`
- Either use that piece of Markdown directly, or this snippet:

```html
<figure style="width: 80%;">
    <img src="https://user-images.githubusercontent.com/image-id-1234.png" alt="Alt text" />
    <figcaption>Some caption</figcaption>
</figure>
```

### Notes, warnings, tips

Various boxes ("admonitions") can be used, for example:

```
!!! tip
    This will be rendered in a highlighted box.
```

Common keywords are `tip`, `note`, `info` or `warning`, see the [full list](https://squidfunk.github.io/mkdocs-material/extensions/admonition/).

### Other MkDocs extensions

See `mkdocs.yml` for a list of enabled extensions.

### Redirects

Redirects are not handled very well by MkDocs at this point, just keep the old page and add a note about the new location, or use the `<meta http-equiv="refresh" content="0; url=new" />` tag.

### Documenting different versions of VersionPress

We don't use a URL scheme like `/latest` or `/v2`, the documentation always reflects the current version and if something has been deprecated or added, just indicate it in the text.

### Markdown linting

Pre-commit hook is set up to run [markdownlint](https://github.com/DavidAnson/markdownlint) automatically on staged files.

If you want to lint all Markdown files in the repository, run `npm run lint:markdown`.

## Deployment

The docs site is automatically deployed to Netlify under info@versionpress.com account (credentials are in LastPass). It deploys a preview for PRs and updates the live site after merge to master.

The deployment is configured directly in the Netlify's UI. Command used for build:

```
export PIPENV_IGNORE_VIRTUALENVS=1
echo -e "[requires]\npython_version = '3.6'\n\n[packages]\nmkdocs-material = '~=3.2.0'\nmkdocs = '>=1'\nPygments = '>=2.2'\npymdown-extensions = '>=4.11'" > Pipfile
pipenv install
pipenv run mkdocs build
```

## Theme info

The theme is a slightly customized [mkdocs-material](https://squidfunk.github.io/mkdocs-material/), see `mkdocs.yml` and the `theme-mods` directory for customizations. The theme itself has [awesome documentation](https://squidfunk.github.io/mkdocs-material/).
