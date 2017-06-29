# Issue management

The project is managed via GitHub [issues](https://github.com/versionpress/versionpress/issues). They are assigned to [milestones](https://github.com/versionpress/versionpress/milestones) for each major version (3.0, 4.0 etc.) and more granularly managed in GitHub [projects](https://github.com/versionpress/versionpress/projects) (4.0-alpha, 4.0-beta etc.).

## Labels

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
    
## Imported issues 1..522

In the early days, we used JIRA and the Czech language to track the project (*bad* decision in retrospect 😅), with the earliest issues not even up to the common standards as we were a team of two and discussed many things face to face.

In October 2015, we decided to move to GitHub and take the project **history** with us, both on the repo level (no "initial commit" with thousands of lines of code) and the issues. The issues were not fun as we needed to write a migration script, fight the GitHub API limitations (e.g., dates cannot be set properly) and eventually translate the issues to English. But there's valuable information in there so we didn't want to throw that part of the project history away.

Still, please consider **issues #1 through #522 "quick and dirty"** – the translation may be poor, the issues may not explain everything in detail, etc.

For newer issues, we try to make them useful and high-quality; they are one of our key artifacts.
