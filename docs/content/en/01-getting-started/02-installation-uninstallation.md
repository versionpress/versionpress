# Installation and Uninstallation

VersionPress ships as a standard plugin but there are two important things to be aware of:

1. VersionPress has **stricter system requirements** than usual
2. Its **activation is a two-step process**

Both things are important, please read on.


## System requirements

The server environment must match certain requirements, some of which are checked automatically on VersionPress activation. We also recommend some other setup steps below.

<div class="important">
  <strong>Important</strong>
  <p>VersionPress is a lot more involved than most other WordPress plugins. Please pay attention to this section before proceeding with installation.</p> 
</div>

Minimum system requirements are:

 - WordPress 3.9 or higher
 - PHP 5.3 or higher
 - Git 1.9 or higher installed on the server *(checked automatically before activation)*
 - Safe mode turned off *(checked automatically before activation)*
 - `proc_open()` enabled *(checked automatically before activation)*
 - Write permissions in the site root and everywhere under it *(checked automatically before activation)*
 - No `wp-content/db.php` on the disk *(checked automatically before activation)*
 - No path customizations (e.g., custom location for `wp-content`)

As a general rule, we recommend using the latest versions of everything. Furthermore, we recommend some other things for the current version of VersionPress:

 - Apache as a web server
 - Review the dependencies of other plugins (see below)

In practice, this means that you need to have a lot of control over your server environment. We are aware that this is an issue and will be lowering these requirements over time.

Here are notes on some of the requirements:


### PHP 5.3

WordPress itself can run even on the old and [long unsupported](http://php.net/eol.php) PHP 5.2. We also started with this version but eventually dropped it so that we could use the newer language features and some 3<sup>rd</sup> party libraries that are 5.3+ only.

Back-porting VersionPress to PHP 5.2 will probably never happen.


### The db.php "hook"

VersionPress currently uses the `wp-content/db.php` file to hook into some WordPress actions for the lack of better extensibility points (see [issue #29710](https://core.trac.wordpress.org/ticket/29710)). This means that VersionPress will be in a conflict with some other plugins like those for caching, debugging etc. We will have a solution to this in some future release.


### Path customizations

Some advanced users like having their plugins directory and other folders outside of the site root. This is not possible with VersionPress because it couldn't track changes in those files.


### Apache web server

Apache web server is recommended because VersionPress does e.g. some `.htaccess` modifications to make sure the site is secure and other web servers may (and typically will) ignore those.

If you use a web server other than Apache, please make sure you protect some files like the `wp-content/vpdb` content or `<root>/.git` from direct access. 


### External libraries

VersionPress depends on external libraries for launching Git processes, working with the file system, etc. The nature of PHP is such that if some other plugin happens to include the same library but in an incompatible version, it might cause issues to VersionPress (or vice versa). There is not much we can do about it so here's just the list of those dependencies so that you can troubleshoot yourself if needs be:

 - `nette/nette-minified`
 - `symfony/process`
 - `symfony/filesystem`
 - `michelf/php-markdown`


## Installation

The basic installation is the same as with any other plugin, however, note the last step:

1. Log in to the admin screens
2. Go to *Plugins > Add New > Upload*
3. Choose or drag&drop `versionpress-<version>.zip` to that page
4. Click *Install Now*
5. Activate the plugin
6. **Finish the activation process** by going into the new VersionPress section in the administration and clicking the *Activate* button

The last step is important, otherwise VersionPress wouldn't be able to track changes. The on-screen instructions will guide you through it. 


## Update / upgrade

The update process is manual at the moment, and actually very simple:

 1. Get the latest version of VersionPress
 2. Replace the folder `wp-content/plugins/versionpress` with the new version

If some version-specific rules apply, they will be described in the [release notes](../release-notes).




## Uninstallation

Uninstallation is a two-step process as with any other plugin:

1. You first **deactivate** the plugin on the *Plugins* admin screen
2. You then **delete** the plugin to get rid of all its files
3. *Optional:* Manually download or delete a repository backup which was created under `wp-content/backup`. See more about this backup below. 

There are two important things to note:

* Once VersionPress is deactivated, **it cannot be fully reactivated again on the same repository**. This means that while you can initialize VersionPress again and the presence of the old repository will not be a problem, features like Undo or Rollback will only be available for *new* commits, created by the current activation of VersionPress.
* **On uninstallation, the Git repository itself is moved to a backup folder** under `wp-content/vpbackups`. This means that after VersionPress uninstallation the site will no longer be considered Git-version-controlled but at the same time, if you need to restore or download the repository you still can.
    * Note: VersionPress will only remove / backup the repository if it detects that it was VersionPress-initiated repository. If you created the Git repository manually before you even installed VersionPress the repository will stay there untouched.  


## WP-CLI

VersionPress comes with several WP-CLI commands that allow you to automate some of the work or execute certain features from command line.

Please follow [WP-CLI installation instructions](https://github.com/wp-cli/wp-cli/wiki/Alternative-Install-Methods) and then refer to [this section](../feature-focus/wp-cli) for instructions on how to use the `wp vp` commands.



## VersionPress states at a glance

To sum up the previous text, here are the states that the site can be in:

| State | Git repo exists? | VersionPress tracking changes? |
| :------------- | :-----: | :-----: |
| WP site without VersionPress | No<sup>1)</sup> | No |
| VersionPress installed  | No | No |
| VersionPress activated on plugins screen | **Still no** | **Still no** |
| Activation finished on VersionPress screen - the plugin is *active* | **Yes** | **Yes** |
| Deactivated (on plugin admin screen) | Yes | **No** |
| VersionPress reactivated on plugins screen (similar to step 3) | Yes (but obsolete) | **Still no** |
| Fully active again (similar to step 4) | Yes | **Yes** |
| Uninstalled | **No** (backed up) | No |
 
<sup>1)</sup> The repo might exist if you created it manually or if VersionPress was previously installed. It is not a problem – VersionPress will happily add commits to the existing repository but a common scenario is that there is no default Git repository and VersionPress creates one.


