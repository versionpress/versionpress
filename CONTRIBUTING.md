# Contributing

You're awesome!

- [Reporting bugs](#reporting-bugs)
- [Feature ideas](#feature-ideas)
- [Contributing code](#contributing-code)
- [Improving docs](#improving-docs)


## Reporting bugs

1. Support issues (white screen of death, unsupported host etc.) should go to the [**support repo**](https://github.com/versionpress/support). Make sure you're reporting a true VersionPress issue here.
2. [**Search** the issues](https://github.com/versionpress/versionpress/issues) first.
3. [Open a new issue](https://github.com/versionpress/versionpress/issues/new).
    - You can also **discuss** it first [on Gitter](https://gitter.im/versionpress/versionpress). 


What makes the issue really helpful:

- You articulate the problem clearly and provide **steps to reproduce** the problem.
- **Screenshots or GIFs** are appreciated.


## Feature ideas

Ideas are great. VersionPress needs them. There are so many difficult problems still to solve, and so many opportunities to make the project better. :bulb: :bulb: :bulb:

The best place to start is [our Gitter room](https://gitter.im/versionpress/versionpress). You'll get some initial feedback and eventually, it will turn into an issue (ticket) here.


## Contributing code

Generally:

1. Open a new issue / pick an existing one
2. Fork the repo, create a branch, commit to it 
3. Push the branch, open a pull request
4. The core team will review it and work with you if necessary
5. Someone from the core team will merge the PR
6. :tada:

Smaller changes like updating README's etc. don't need to use the full workflow, a direct PR or sometimes even a commit into `master` is fine. However, most code changes undergo the suggested workflow which is described in more detail [below](#development-workflow).

> Note: VersionPress is not the easiest WordPress project to contribute to at the moment. Some things are hard in nature (VersionPress is a lot more complex than most other WP plugins), some we try to continually improve (simplifying [Dev-Setup](./docs/Dev-Setup.md), marking issues as `good-first-bug`'s etc.). Any help on this front is always appreciated! 

The following discusses some of the important details if you want to contribute.


### Core values

- **We care about user / dev experience**. Everything that is outward-facing, be it a user interface, developer API or a file format, must be carefully designed for usability and usefulness. We invest our energy to save it for the others.
- **We care about code quality**. Bad code is a liability, not an asset. We value tests, review each other's code and try to make it good and clean.
- **We try to be pragmatic**. While we care about quality, the main thing for VersionPress and its users is to move forward. We're always looking for the right balance.


### Our development process

[**Issues**](https://github.com/versionpress/versionpress/issues) are the most important tool to plan and manage almost everything around VersionPress. We create them for new features, bugs, improvements or even larger things like planning documents. **We strongly prefer issues over wiki** or other documents as they are actionable and time-framed. The issues we use are described in [docs/Issues.md](./docs/Issues.md).

[**Milestones**](https://github.com/versionpress/versionpress/milestones) are used to assign issues to major releases like 4.0 or 5.0 (we don't use minor releases like 4.1 or 4.2, see our [release versioning](https://docs.versionpress.net/en/release-notes#release-versioning)). [**Projects**](https://github.com/versionpress/versionpress/projects) are then used for more granular planning, e.g., to assign issues to various alpha, beta or final releases.

> Issues not assigned to any milestone are in a backlog – we want to do them one day but there are no immediate plans.

[**Pull requests**](https://github.com/versionpress/versionpress/pulls) implement issues. Commonly, a piece of functionality starts as an issue but quickly transitions into a PR where most of the technical discussion happens. In other words, issues are the original ideas of how to improve or fix something, PR's are how it was actually done. 

Regarding **branches**, the current release being worked on is **`master`**. We do our best to keep it in a good shape but it might be unstable at times.

**There's a long-running branch** for every major release named `1.x`, `2.x` etc. in case a fix needs to go there. Merging / cherry picking between `master` and long-running branches is always a bit tricky, see e.g. [this blog post](http://blogs.atlassian.com/2013/11/the-essence-of-branch-based-workflows/); generally, merge from older to newer (`1.x` -> `2.x` -> `master`), never the other way around. At the same time, we generally only want to support the latest and greatest and especially during the Developer Preview period, we don't care that much about the older releases.

We have quite a large **test suite** and every major feature usually has some tests around it, from small unit tests to large, Selenium-based functional tests. Please see [Testing](./docs/Testing.md) for more info.


### Development workflow

For small / "safe" changes like updating a README or other Markdown files, quick pull request or even commit into `master` is acceptable. However, for most new code, we use the [GitHub flow](https://guides.github.com/introduction/flow/):

![GitHub Flow](https://guides.github.com/activities/hello-world/branching.png)

Here are the details:


1. When you start working on an issue, **move it to the 'in progress' state** (either visually on the [overv.io board](https://overv.io/versionpress/versionpress/) or by assigning the `in progress` label to the issue) and **create a new feature branch** for it. Name it `<issue number>-<short description>`, e.g., `123-row-filtering`.

    - **Every feature branch should branch off of master**, not another feature branch, even if it depends on it. For dependent feature branches, simply merge between them. This is mainly because when you're going to open a PR for it, you will need to select the target branch (GitHub doesn't let you to change this later) and `master` is the only sensible choice there.
    
2. **Commit to this branch**. We appreciate good commits, here are some tips:

    - **Keep commits small and focused**. There are many articles on version control best practices, e.g., [this one](http://www.git-tower.com/learn/git/ebook/command-line/appendix/best-practices) is good. To sum it up, commit small logical changes, prefer smaller commits over large ones and keep project in a workable state at all times.
    - **Write good commit messages**. We don't have strict rules like [this](http://chris.beams.io/posts/git-commit/), e.g., we don't enforce short subject lines. The main thing for us is that the commit messages are *useful*. Do they make it clear what happened in a commit? Do they reference related commits, if applicable? Good.
        - We most commonly use past tense ("Added tests") or present tense describing the new situation ("IniSerializer now has tests") but we're not religious about it.
    - **Link to an issue from the commit message**. Most of the commit messages look like this:
    
        ```
        [#123] Implemented xyz
        ```
        
        It means that the commit belongs to issue `#123`. It makes looking up issues from commits easier.   


3. When ready, push the branch, **open a pull request** for it and **move the issue to the 'in review' state** (again, either visually in [overv.io](https://overv.io/versionpress/versionpress/) or by removing the `in progress` label and adding the `in review` one). You can open a PR early to gather feedback, no worries, you can always add commits to it later. The branch can be push-forced if necessary, it is a "sandbox" to make it great.

    This is an example of a good pull request: [versionpress/versionpress#744](https://github.com/versionpress/versionpress/pull/744). The body usually contains something like:
    
        Resolves #123.
        
        Some notes on the implementation here if it's not obvious from the code
        or the list of commits.
        
        Reviewers:
        
        - [ ] @JanVoracek 
        - [ ] @borekb 
    
    It will be pre-filled for you automatically via GitHub templates, just with a different reviewer (`@versionpress/core-devs` will be there by default, someone from the core team will update it to the actual list of people).
    
4. **Core team reviews the PR**. Expect feedback – it is uncommon to receive none – and be open to it. The team will happily work with you to make the code contribution great.

    All checkboxes checked means that the PR is OK to merge.
    
    > This is an important nuance because the checkbox can have two meanings: "PR is OK to merge" or "I am done with the review (regardless of whether I still see issues with the code or not)". The former is useful for the one who will eventually perform the merge, the latter is more convenient for a reviewer. We use the first meaning which means that I, as a reviewer, will only check the checkbox after I reported some issues with the code **and they have been fixed**.   
    
5. Someone from the core team **merges the pull request**, issue is closed and the branch can be deleted.

A couple of notes:

- As noted above, small / safe changes don't need to undergo this whole process. For example, Markdown files can be **committed directly into `master`** if the changes don't need to be reviewed.
- We used to use **rebasing** in the past – you can still see that in commits before April 2015 – but left it in favor of merging which is much more natural on GitHub. Plus, rebases [have their own issues](http://geekblog.oneandoneis2.org/index.php/2013/04/30/please-stay-away-from-rebase).
- **Issues vs. pull requests**: most of the new improvements and features start as issues as they are quick to create and don't require a Git branch. Then there's usually a single PR against the issue (sometimes more but that's relatively rare). However, issues and pull requests are almost the same thing on GitHub and it's not a problem to start something (possibly simpler) directly as a PR.


### License

VersionPress is licensed under [GNU General Public License v3](http://www.gnu.org/licenses/gpl-3.0.txt). By contributing, you agree that your contributions will also be licensed under the same license.

### Style guides

#### PHP style guide

Most of our PHP code follows [**PSR-2**](http://www.php-fig.org/psr/psr-2/), *not* WordPress coding standards. This is deliberate, see [#698](https://github.com/versionpress/versionpress/issues/698). Basically, it's mainly because most of VersionPress is a relatively separate, object oriented system developed recently, where anything but PSR-2 doesn't feel right.

There are a couple of cases where some parts of our code do not adhere to PSR-2 strictly:

- **Code interacting with WordPress** (hooking into it, providing global functions etc.) follows some of WP conventions. For example, global functions are called like `vp_register_hooks()`, not `registerHooks()`.
- **WP-CLI commands** use filenames similar to the built-in commands, so for instance `VPCommand` lives in a `vp.php` file, not `VPCommand.php`.

Generally, try to follow what's already in place. The project ships with PhpStorm settings, `.editorconfig` etc. so if you use PhpStorm as recommended, it should serve as a good guidance. 

#### JavaScript style guide

VersionPress' GUI is a separate React application, developed in TypeScript.

The styleguide is under construction. :construction:


### Get help

Feel free to reach the devs in the [Gitter room](https://gitter.im/versionpress/versionpress) if you need help with anything.

[![Gitter](https://img.shields.io/gitter/room/nwjs/nw.js.svg)](https://gitter.im/versionpress/versionpress)


## Improving docs

Public docs at [docs.versionpress.net](https://docs.versionpress.net/en) are exported from the `docs/content` directory. We're always happy to accept pull requests with clarifications, grammar fixes, etc., more in [docs/README.md](./docs/README.md). Thank you!


---

Other ideas of how to contribute? Tell us [on Gitter](https://gitter.im/versionpress/versionpress). 
