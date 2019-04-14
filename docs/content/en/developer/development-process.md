# Development Process

If you want to contribute, this is an overview of how we manage the project.

## Workflow on GitHub

All usually starts with a [GitHub issue](https://github.com/versionpress/versionpress/issues) which is [labeled](https://github.com/versionpress/versionpress/labels) and put to a certain [milestones](https://github.com/versionpress/versionpress/milestones). [GitHub projects](https://github.com/versionpress/versionpress/projects) are used to more granularly track issue states.

[Pull requests](https://github.com/versionpress/versionpress/pulls) implement the functionality. We use [the GitHub flow](https://guides.github.com/introduction/flow/):

![GitHub Flow](https://guides.github.com/activities/hello-world/branching.png)

Some guidelines:

- Name branches `123-some-feature` where `123` is an issue reference.
- Never force-push or otherwise amend published commits. For example, don't rebase or squash commits.
- Feel free to open a pull request early to gather feedback.
- When the development is done, please update the PR description to be a good overview of the change for anyone reading it in the future.

## Release process

It slightly evolves over time, it's best to find the most recent [release issue](https://github.com/versionpress/versionpress/labels/release) and start from there. General steps will involve:

- Writing [release notes](#release-notes).
- Preparing a build.
- Publishing a GitHub release.
- Announcing the release.

### Release versioning

We use semver-like version numbers but the versioning is more like what web browsers do, i.e., bump major versions pretty frequently. We barely use minor versions so a typical sequence might look like this:

`3.0` → `3.0.1` → `4.0-alpha` → `4.0-beta` → `4.0` etc.

### Release notes

Release notes are primarily written in `docs/content/en/release-notes` and [published here](http://docs.versionpress.net/en/release-notes/). They are also copied to [GitHub releases](https://github.com/versionpress/versionpress/releases) with some slight formatting modifications.
