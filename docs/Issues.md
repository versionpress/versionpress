# Issue management

On this page:

- [Labels](#labels)
- [About the imported issues 1..522](#imported-issues-1522)

## Labels

We use these labels to tag GitHub issues:


### Issue type

 - `bug` – yes, that. If it's a major bug, it has additionally the `major` label.
 - `feature` – something new in a release.
 - `improvement` – an improvement of an existing feature.
 - `task` – e.g. to write a documentation; rarer than the previous types.


### Importance

- `minor` marks relatively unimportant issues.
- `major` is only used with bugs, forming the "major bug" pseudo-label.
- `significant` is used to highlight issues that are worth mentioning in the release notes or are otherwise significant.

We also use **[overv.io](https://overv.io/versionpress/versionpress/) for priorities** – cards placed higher have higher priority. The board is not always in sync with reality though.


### Issue states

1. **Open issues** don't have any label, they are just 'Open' on GitHub. Nobody is working on them currently. `discussion` label can be used when a decision needs to be made before coding starts.
2. `in progress` marks issues that are being worked on.
3. `in review` marks issues that are considered done by the assignee and are undergoing a review.
4. **Closed issues** again have no label, they just use the 'Closed' GitHub state. There are some special labels marking the closed issues:
    - `won't fix`
    - `duplicate`


### Scopes (areas of work)

Areas of work (scopes, components) are prefixed `scope:`. We use these:

 - `scope: core`
 The core versioning functionality. Technically, most of the PHP code of the plugin falls into this category, just the workflow features (cloning, merging etc.) are marked with the `scope: workflows` label.
 - `scope: gui` UI things, i.e., what users click on. Technically, the 'frontend' project.
 - `scope: integrations` Integrations with 3<sup>rd</sup> party plugins, themes, hosts etc.
 - `scope: workflows` Things like branching, merging, cloning, pulling etc.
 - `scope: tests` Automated tests.
 - `scope: dev-infrastructure` Things regarding development environment like IDE settings, build scripts etc.

We also historically have labels like `scope: website`, `scope: docs`, `scope: blog` etc. but those are managed via separate repositories now.


### Sizes

The effort is sometimes estimated using the `size:` labels using T-shirt-like sizes of L, M, S etc.

Note: sizes are not priorities.

### Other

- `needs-migration` – such issues change the storage format and require proper migration between two VersionPress versions. (Currently, we do not have migrations which means that if a release contains one or more `needs-migration` issues, full deactivation and re-activation is required. See [#275](https://github.com/versionpress/versionpress/issues/275).)
- `WP 4.7` – compatibility with WordPress 4.7.
- `support` – issue that should have been opened in the [support repo](https://github.com/versionpress/support).
- `plugin-support` – label for issues around plugin support in VP 4.0.

## Imported issues 1..522

In the early days, we used JIRA and the Czech language to track the project (*bad* decision in retrospect :sweat_smile:), with the earliest issues not even up to the common standards as we were a team of two and discussed many things face to face.

In October 2015, we decided to move to GitHub and take the project **history** with us, both on the repo level (no "initial commit" with thousands of lines of code) and the issues. The issues were not fun as we needed to write a migration script, fight the GitHub API limitations (e.g., dates cannot be set properly) and eventually translate the issues to English. But there's valuable information in there so we didn't want to throw that part of the project history away.

Still, please consider **issues #1 through #522 "quick and dirty"** – the translation may be poor, the issues may not explain everything in detail, etc.

For newer issues, we try to make them useful and high-quality; they are one of our key artifacts.
