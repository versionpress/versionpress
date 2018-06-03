# WP-CLI Commands #

For advanced usage, VersionPress comes with several [WP-CLI](http://wp-cli.org/) commands. They are useful in two main scenarios:

 1. You prefer doing some actions from the command line
 2. You need to interact with VersionPress when the admin backed is not available for some reason, for example, after a failed update

As a note, we absolutely love WP-CLI. If you haven't come across this project before we recommend you familiarize yourself with what it can do at [wp-cli.org](http://wp-cli.org/).


## Installing WP-CLI ##

WP-CLI runs best on UNIX-like systems (Linux, Mac OS X, Cygwin..) but we test our commands on Windows as well, and they work fine.

Here is an [overview of all the supported installation methods](https://github.com/wp-cli/wp-cli/wiki/Alternative-Install-Methods), we'll just assume for the rest of this page that the `wp` command is available in the console and executing `wp --info` prints a standard output.


## Working with VersionPress commands ##

When VersionPress is installed *and activated* on the plugins screen, `cd` into the site root and run:

```bash
$ wp vp <command> <parameters>
```

If VersionPress is not active or cannot be active, for example, in case of a broken site, use `--require` to load a specific WP-CLI command. For example, `restore-site` will usually need this so it will be called like this:

```bash
$ wp vp restore-site --siteurl='http://localhost/mysite' --require=wp-content/plugins/versionpress/src/Cli/vp.php
```

## Command reference ##

Generally, **use `wp help` as the primary source of information** as that will always be 100% up to date. Below is a descriptive overview of the commands available.

### vp config

Configures VersionPress. See [configuration](../getting-started/configuration.md).


### vp undo

Undoes a commit.

It is the same action as clicking the *Undo this* button in the admin screen, and it can fail for the same reasons: invalid referential integrity (e.g., trying to restore a comment for which the post no longer exists), conflict (undoing something conflicts with a newer update of the same entity) and working directory not being clean (possible loss of user changes). In that cases, `undo` will simply do nothing.

Takes a commit SHA-1 as an argument and which can either be a full SHA1 like `4dadc69147fd19c8f6d9451aea1ded0de56cccf3` or a shorter one like the first 7 chars only. It follows the [same rules as Git](https://git-scm.com/book/en/v2/Git-Tools-Revision-Selection#Short-SHA-1).

*Examples:*

```bash
$ wp vp undo a34bc28
```


### vp rollback

Reverts site to a previous state.

It is the same action as clicking the *Roll back to this* button in the admin screen.

Takes a commit SHA-1 as an argument and which can either be a full SHA1 like `4dadc69147fd19c8f6d9451aea1ded0de56cccf3` or a shorter one like the first 7 chars only. It follows the [same rules as Git](https://git-scm.com/book/en/v2/Git-Tools-Revision-Selection#Short-SHA-1).


*Examples:*

```bash
$ wp vp rollback a34bc28
```

### vp restore-site

Restores site from a Git repository.

You will typically use this command in two main situations:

 1. Something **went really wrong with the database** and you want to restore it
 2. You want to restore a site **just from a Git repository**, e.g., after a fresh clone from GitHub on a new machine

Let's focus on the first scenario now. In the worst case, you completely lost the database and running `wp vp restore-site` then will basically work like a restore of a backup, i.e., it will re-create the database tables (for tables that VersionPress can restore; it will leave other tables in the database alone so for example it will *not* do anything with tables that are not WordPress related) and fill it with the site's data. This is useful also when the database was not completely lost, just something went wrong with it, e.g. by some plugin bug, human error and so on. Again, `wp vp restore-site` will just bring it back to an OK state.

Note that with this command, you will **need to include the `--require=...` parameter** because on a broken or non-existent site, WP-CLI will not be able to automatically detect the `vp` command. The complete command invocation will usually look like this:

```bash
$ wp vp restore-site --siteurl='http://localhost/mysite' --require=wp-content/plugins/versionpress/src/Cli/vp.php
```

The other, **development scenario** assumes that the only thing you have is a Git clone of a site. That is quite extreme because not only you don't have a database at all but you also don't have these two vital things due to the fact that VersionPress doesn't store them in the Git repo (for good reasons [described here](../feature-focus/change-tracking.md#whats-not-tracked)):

 * `wp-config.php`
 * VersionPress itself, i.e., `wp-content/plugins/versionpress`

The second point means that you can't immediately run `wp vp restore-site` because there is no such thing as a `vp` command yet on the site. Follow these steps:

 1. Manually put VersionPress into to `wp-content/plugins/versionpress` folder.
 2. Run `wp core config` from the site root to setup the database.
 3. Run `wp vp restore-site` with `siteurl` and `require` parameters.
 4. Enjoy your up and running site.


### vp clone

Clones site to a new folder, database and Git branch. See [Cloning a site](../sync/cloning.md).


### vp pull

Pulls changes from another site instance and creates a merge if necessary. See [merging](../sync/merging.md).


### vp push

Pushes changes to another site instance. Does not create a merge; see [merging](../sync/merging.md).


### vp apply-changes

Applies changes found on the disk to the database. Useful e.g. after resolving merge conflicts. See [merging](../sync/merging.md).

### vp check-requirements

Checks if all requirements for using VersionPress are met on the target environment.

This command is useful when you make changes on the server and want to check whether VersionPress requirements are still met.

Note that you will mostly **need to include the `--require=...` parameter**.
