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
- **We care about code quality**. Hacked-together code without tests is a liability as soon as it reaches the repo. We try to write good, clean code.
- **We try to be pragmatic**. While we care about quality, the main thing for VersionPress and its users is to move forward. We're always looking for the right balance.


### Our development process

**Major versions** (1.0, 2.0 etc.) are released every few months. Each major version has a [corresponding milestone](https://github.com/versionpress/versionpress/milestones/) and issues are assigned to it by the core team.

**Issues** are the most important tool to plan and manage almost everything around VersionPress:

- We create them for new features, bugs, improvements or even larger things like planning documents. **We strongly prefer issues over wiki** or other documents as they are actionable and time-framed.
- [This set of labels](https://github.com/versionpress/versionpress/wiki/Issues#labels) is used to categorize issues.

**Branches**: The current release being worked on is `master`. It is hence inherently unsafe, even though we do our best to keep it in a good shape.

**For every major release, there's a long-running branch** named `1.x`, `2.x` etc. in case a fix needs to go there. Merging / cherry picking between `master` and these branches is a bit tricky, see e.g. [this blog post](http://blogs.atlassian.com/2013/11/the-essence-of-branch-based-workflows/); generally, merge from older to newer (`1.x` -> `2.x` -> `master`), never the other way around. At the same time, we generally only want to support the latest and greatest and especially during the Early Access period, we don't care that much about the older releases.

We have quite a large **test suite** and every major feature usually has some tests around it, from small unit tests to large, Selenium-based functional tests. Please see [Testing](./docs/Testing.md) for more info.


### Development workflow

We use the [GitHub flow](https://guides.github.com/introduction/flow/):

![GitHub Flow](https://guides.github.com/activities/hello-world/branching.png)

Here are the details:


1. When you start working on an issue, **create a new feature branch** for it. Name it `<issue number>-<short description>`, e.g., `123-row-filtering`.

    - **Every feature branch should branch off of master**, not another feature branch, even if it depends on it. **For dependent feature branches, simply merge between them.** This is mainly because when you're goint to open a PR for it, you will need to select the target branch (GitHub doesn't let you to change this later) and `master` is the only sensible choice there.
    
2. **Commit to this branch**. We appreciate good commits, here are some tips:

    - **Keep commits small and focused**. There are many articles on version control best practices, e.g., [this one](http://www.git-tower.com/learn/git/ebook/command-line/appendix/best-practices) is good. To sum it up, commit small logical changes, prefer smaller commits over large ones and keep project in a workable state at all times.
    - **Write good commit messages**. We don't have strict rules like [this](http://chris.beams.io/posts/git-commit/), e.g., we don't enforce short subject lines. The main thing for us is that the commit messages are *useful*. Do they make it clear what happened in a commit? Do they reference related commits, if applicable? Good.
        - We most commonly use past tense ("Added tests") or present tense describing the new situation ("IniSerializer now has tests") but we're not religious about it.
    - **Link to an issue from the commit message**. Most of our commit messages look like this:
    
        ```
        [#123] Implemented xyz
        ```
        
        It means that the commit belongs to issue #123. It makes looking up issues from commits easier.   


3. When ready, push the branch and **open a pull request** for it. You can do this early to gather feedback, no worries. Branch can be push-forced if necessary, it is a "sandbox" to make the branch great.

    This is an example of a good pull request: [versionpress/versionpress#744](https://github.com/versionpress/versionpress/pull/744). As a template, this is what the PR body usually contains:
    
        Resolves #123.
        
        Some notes on the implementation here if it's not obvious from the code
        or the list of commits.
        
        Reviewers:
        
        - [ ] @JanVoracek 
        - [ ] @borekb 
    
    Actually something like this will be pre-filled automatically for you.
    
4. **Core team reviews the PR**. Expect feedback – it is uncommon to receive none at all.

    All checkboxes checked means that the PR is OK to merge.
    
    > This is an important nuance because the checkbox can have two meanings: "PR is OK to merge" or "I am done with the review (regardless of whether I still see issues with the code or not)". The former is useful for the one who will eventually perform the merge, the latter is more convenient for a reviewer. We use the first meaning which means that I, as a reviewer, will only check the checkbox after I reported some issues with the code **and they have been fixed**.   
    
5. Someone from the core team **merges the pull request**, issue is closed and the branch can be deleted.

A couple of side notes:

- Markdown files (README, docs etc.) can be committed directly into `master` if the changes don't need to be reviewed.
- We used to use rebasing in the past – you can still see that in commits before April 2015 – but left it in favor of merging which is much more natural on GitHub. Plus, rebases [have their own issues](http://geekblog.oneandoneis2.org/index.php/2013/04/30/please-stay-away-from-rebase). 


### License

VersionPress is licensed under [GNU General Public License v3](http://www.gnu.org/licenses/gpl-3.0.txt).

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


## Improving docs

Public docs (docs.versionpress.net) are managed via [versionpress/docs](https://github.com/versionpress/docs). We're happy to accept pull requests with improvements, fixes for spelling errors etc. Thank you!


---

Other ideas of how to contribute? Tell us [on Gitter](https://gitter.im/versionpress/versionpress). 