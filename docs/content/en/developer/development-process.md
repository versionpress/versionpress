# Development Process

If you want to contribute, this is an overview of how we manage the project.

## Workflow on GitHub

All usually starts with a [GitHub issue](https://github.com/versionpress/versionpress/issues) – someone reports a bug or requests a feature.

The issue is [labeled](https://github.com/versionpress/versionpress/labels) and put to a certain [milestone](https://github.com/versionpress/versionpress/milestones) if we have a rough idea when we'd like to work on it. [GitHub projects](https://github.com/versionpress/versionpress/projects) are then used to track the issues' progress.

[Pull requests](https://github.com/versionpress/versionpress/pulls) implement the functionality. We use [the GitHub flow](https://guides.github.com/introduction/flow/):

![GitHub Flow](https://guides.github.com/activities/hello-world/branching.png)

If you're contributing, please note the following:

- We appreciate small and focused commits with [good commit messages](https://chris.beams.io/posts/git-commit/).
- Base your branch on `master` and name it `123-some-feature` where `123` is an issue reference.
- Use merging, not rebasing. More broadly, never overwrite _published_ commits by rebasing, squashing or force-pushing them.

Feel free to open a pull request early to gather feedback. When the development is done, please update the PR description to be a good overview of the change for anyone reading it in the future.

## Release process

### Version numbers

We bump major version with every release like browsers do, so you'll typically see a sequence like `2.0` → `3.0` → `3.0.1` (a bugfix release) → `4.0` etc.

Major versions typically go through alphas and betas which are tagged e.g. `4.0-beta2`.

### Release notes

Release notes are primarily written in `docs/content/en/release-notes` and [published here](http://docs.versionpress.net/en/release-notes/). They are also copied to [GitHub releases](https://github.com/versionpress/versionpress/releases) with some slight formatting modifications.
