# Development Process

If you want to contribute, this is an overview of how we manage the project.

## Workflow on GitHub

All usually starts with a [GitHub issue](https://github.com/versionpress/versionpress/issues) which is [labeled](https://github.com/versionpress/versionpress/labels) and put to a certain [milestone](https://github.com/versionpress/versionpress/milestones). [GitHub projects](https://github.com/versionpress/versionpress/projects) are used to more granularly track issue states.

[Pull requests](https://github.com/versionpress/versionpress/pulls) implement the functionality. We use [the GitHub flow](https://guides.github.com/introduction/flow/):

![GitHub Flow](https://guides.github.com/activities/hello-world/branching.png)

Guidelines we follow:

- Branches are commonly named `123-some-feature` where `123` references an issue.
- We never force-push or otherwise amend published commits, for example, we don't rebase or squash commits. Work-in-progress commits are absolutely fine.
- It's often useful to open a pull request early so that it can be discussed.
- When the development is done, we try to update the PR description so that it's a good overview of the change for anyone reading it in the future.

## Release process

It evolves over time, it's best to find the most recent [release issue](https://github.com/versionpress/versionpress/labels/release) and start from there. General steps involve:

- Generate a list of pull requests between the latest released version and `master` by running e.g. `npm run changelog -- 4.0..master`, see `scripts/README.md`. Review the pull requests, possibly update their titles, add 'noteworthy' label to those that should be highlighted, etc.
- [Write release notes](#release-notes).
- Prepare a build.
- Publish a GitHub release.
- Announce the release.

### Release versioning

We use semver-like versioning with several differences:

- If there's no patch version yet, we simply call it `X.0` instead of `X.0.0`.
- We bump major versions frequently and don't use minor versions too much.

A sequence of releases might look like this:

`3.0` → `3.0.1` → `4.0-alpha` → `4.0-beta` → `4.0` etc.

### Writing release notes

Release notes are primarily written in `docs/content/en/release-notes` and [published here](http://docs.versionpress.net/en/release-notes/). They are also copied to [GitHub releases](https://github.com/versionpress/versionpress/releases) with some slight formatting modifications.
