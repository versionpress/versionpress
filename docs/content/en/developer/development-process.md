# Development process

Here is a set of tools and approaches we use during VersionPress development.

## Overview

[**Issues**](https://github.com/versionpress/versionpress/issues) are the most important tool to plan and manage almost everything around VersionPress. They are described in more detail in a [separate section below](#issues).

[**Milestones**](https://github.com/versionpress/versionpress/milestones) are used to assign issues to major releases like 4.0 or 5.0 (we don't use minor releases like 4.1 or 4.2, see [below](#release-versioning)).

[**Projects**](https://github.com/versionpress/versionpress/projects) are then used for more granular planning, e.g., to assign issues to various alpha, beta or final releases.

!!! Note "Backlog"
    Issues not assigned to any milestone are in a backlog – we want to do them one day but there are no immediate plans.

[**Pull requests**](https://github.com/versionpress/versionpress/pulls) implement issues. Commonly, a piece of functionality starts as an issue but quickly transitions into a PR where most of the technical discussion happens. In other words, issues are the original ideas of how to improve or fix something, PR's are how it was actually done.

## Development workflow

We use the [GitHub flow](https://guides.github.com/introduction/flow/):

![GitHub Flow](https://guides.github.com/activities/hello-world/branching.png)

Some tips:

- Development setup is described in [setup.md](setup.md).
- Branches are commonly named `<issue_number>-<short_description>`, e.g., `123-row-filtering`.
- All branches start from `master`.
- We care about small and focused commits with good commit messages.
- Pull request should contain a link to the parent issue (if applicable) and a summary of the change. Every pull request is reviewed.

## Issues

Some more details on our issues:

### Labels

We use [these labels](https://github.com/versionpress/versionpress/labels) to tag GitHub issues:

- Issue type:
    - `bug` – a major bug has an additional `major` label
    - `feature` – something new in a release
    - `improvement` – an improvement of an existing feature
    - `task`
    - `question`
    - `support` – issue that should have been opened in the [support repo](https://github.com/versionpress/support)
- Importance:
    - `minor`
    - `major` – only used with bugs, see above
    - `significant` – used to highlight issues that are worth mentioning in release notes or otherwise significant
- Scopes (areas of work):
    - `scope: core` – the core VersionPress functionality like tracking actions, creating Git commits etc.
    - `scope: workflows` – things like cloning, pulling, pushing, etc.
    - `scope: gui` – issue for the 'frontend' React app and other UI things
    - `scope: tests`
    - `scope: dev-infrastructure` – IDE settings, build scripts, etc.
    - `scope: docs`
    - `scope: integrations` – integrations with WordPress plugins, themes, hosts etc.
    - Some historic labels like `scope: website`, `scope: blog` etc. Those are commonly managed via separate repositories now.
- Effort, roughly:
    - `size: xs` – 1 to 2 hours
    - `size: s` – about half a day
    - `size: m` – day or two
    - `size: l` – three to five days
    - `size: xl` – multiple weeks
- Resolution:
    - Most issues are just closed when done without any additional label. They are also moved to the _Done_ column in a GitHub project.
    - `duplicate` – issue is resolved by some other ticket
    - `invalid` – incorrectly reported, not an actual bug etc.
    - `obsolete` – no longer valid
    - `won't fix` – we don't plan to implement this
- Other:
    - `needs-migration` – such issues change a storage format and require migration between two VersionPress versions. (Currently, we do not have migrations which means that if a release contains one or more `needs-migration` issues, full deactivation and re-activation is required. See [#275](https://github.com/versionpress/versionpress/issues/275).)
    - `WP 4.7` – compatibility with WordPress 4.7.
    - `plugin-support` – issues implementing the plugin support in VersionPress 4.0.

### Note on imported issues 1..522

In the early days, we used JIRA and the Czech language to track the project (*bad* decision in retrospect 😅), with the earliest issues not even up to the common standards as we were a team of two and discussed many things face to face.

In October 2015, we decided to move to GitHub and take the project **history** with us, both on the repo level (no "initial commit" with thousands of lines of code) and the issues. The issues were not fun as we needed to write a migration script, fight the GitHub API limitations (e.g., dates cannot be set properly) and eventually translate the issues to English. But there's valuable information in there so we didn't want to throw that part of the project history away.

Still, please consider **issues #1 through #522 "quick and dirty"** – the translation may be poor, the issues may not explain everything in detail, etc.

For newer issues, we try to make them useful and high-quality; they are one of our key artifacts.


## Release versioning

We bump major version with every release like browsers do so VersionPress quickly advances from `4.0` to `5.0` to `6.0` etc. We do not use minor versions like `4.1` or `4.2`. We do, however, use patch releases like `4.0.1` or `4.0.2`.

Preview versions are marked e.g. `4.0-alpha` or `4.0-beta2`, as per [semver](http://semver.org/).

## Branching model

The current release being worked on is **`master`**. All tests should be passing before any code is merged to `master`.

There are **long-running branches** for every release named `1.x`, `2.x` etc. For bug fixes, always merge from older to newer, e.g., `1.x` -> `2.x` -> `master`, never the other way around, see [this blog post](http://blogs.atlassian.com/2013/11/the-essence-of-branch-based-workflows/). With that being said, during the Developer Preview program, we mostly care about the "latest and greatest" only.
