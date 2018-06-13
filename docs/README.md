# VersionPress docs

The documentation at [`docs.versionpress.net`](https://docs.versionpress.net/) is authored in Markdown and built using [MkDocs](https://www.mkdocs.org/).

## Updating documentation

1. Edit some Markdown files in the `content` directory.
2. _(Optional)_ Live-preview the changes by starting up Docker and running `npm run start` – the site will be ready for you at <http://localhost:8000>.
3. Submit a pull request with your changes.

## Authoring tips

### Site navigation

Site navigation is defined in `mkdocs.yml`. This file must be manually updated whenever a new file is added or an existing file moved. This structure will automatically generate the navigation on the site in both the sidebar and in the "next" / "previous" links in the footer.

### Links

Links should be written as **relative** and **ending with .md**, for example, `[configuration](../getting-started/configuration.md)`. Only this form ensures that links work both on GitHub and rendered on docs.versionpress.net.

### Title casing

- Use **Title Case** for H1 headers.
- Use **Sentence case** for H2 and below.

### Images

- Recommended maximum width is 700 px.
- Optimize via [TinyPNG](https://tinypng.com/) or similar.
- Paste to GitHub comment which produces a Markdown like `![image](https://user-images.githubusercontent.com/image-id-1234.png)`
- Either use that piece of Markdown directly, or use this snippet:

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

Common keywords are `tip`, `note` or `warning`, see the [full list](https://squidfunk.github.io/mkdocs-material/extensions/admonition/).

### Other MkDocs extensions

See `mkdocs.yml` for a list of enabled extensions.

### Redirects

- [ ] TODO: how to make redirects work in MkDocs
  * one way is to leave old file but add `<meta http-equiv="refresh" content="0; url=new" />` to redirect it
  * discussions on board about plugins (if someone could port https://github.com/jekyll/jekyll-redirect-from that would be awesome)


## Build

Run `npm run build` to build the site into the `site` directory.

## Deployment

The docs site is hosted on GitHub Pages, via the [`versionpress/docs.versionpress.net`](https://github.com/versionpress/docs.versionpress.net) repo. To deploy:

1. Build the site to the `site` directory:
    ```
    npm run build
    ```
2. Optionally verify locally, e.g., `cd site && php -S localhost:1234`.
3. Clone the [`docs.versionpress.net`](https://github.com/versionpress/docs.versionpress.net) repo next to your `versionpress` directory (they should be sibling folders).
4. Copy the built site into it:
    ```
    cp -r site/* ../../docs.versionpress.net
    ```
5. Commit the new build. The commit message doesn't matter too much, use e.g. _New build of docs.versionpress.net_.
6. Push the repo.

Verify the updated docs at <https://docs.versionpress.net/>.

## Theme info

The theme is built on [mkdocs-material](https://squidfunk.github.io/mkdocs-material/). You can customize it by updating the following files:

* `content/stylesheets/extra.css`
* `content/javascript/extra.js`

You can also put files in `content/wp_theme` to [override the base theme](https://www.mkdocs.org/user-guide/styling-your-docs/#using-the-theme-custom_dir).

## Documenting different versions of VersionPress

We don't use a URL scheme like `/latest` or `/v2`, the documentation always reflects the current version and if something has been deprecated or added, just indicate it in the text.
