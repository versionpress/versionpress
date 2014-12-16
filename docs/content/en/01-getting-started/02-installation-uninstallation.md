# Installation and Uninstallation

VersionPress ships as a standard plugin but there are two important things to be aware of:

1. VersionPress has **stricter system requirements** than usual
2. Its **activation is a two-step process**

Both things are important, please read on.


## System requirements

The server environment must currently match these requirements, some of which are checked automatically before VersionPress activation:

  * WordPress 3.8 or higher
  * PHP 5.3 or higher
  * Git 1.9 or higher installed on the server *(checked automatically before activation)*
  * Safe mode turned off *(checked automatically before activation)*
  * The `proc_open()` function enabled *(checked automatically before activation)*
  * There must be no `wp-content/db.php` file on the disk *(checked automatically before activation)*

In practice, this means that you need to have control over your server as these will typically not be available in a common hosting scenario. We are aware that this is an issue and will be lowering these requirements in some future release.

For some advanced features, you might also need WP-CLI installed, see a separate section below.

<div class="note">
  <strong>Note about PHP 5.3</strong>
  <p>WordPress itself can run even on the old and now <a href="http://php.net/eol.php">long unsupported</a> PHP version 5.2. We also started with this version in mind but eventually dropped it so that we could use the newer language features and some 3rd party libraries that are 5.3+ only. Back-porting VersionPress to PHP 5.2 is currently not planned.</p>
</div>

<div class="note">
  <strong>Note about <code>db.php</code></strong>
  <p>VersionPress currently uses the <code>db.php</code> file to hook into some of the WordPress' actions that don't have other good extensibility points. We know that having a dependency on <code>db.php</code> is a problem in some server environments (e.g., there might be a collision with some other plugin also requiring <code>db.php</code>) and will have a solution to this at some point in the future.</p>
</div>


## Installation

The basic installation is the same as with any other plugin, however, note the last step:

1. Log in to the admin screens
2. Go to *Plugins > Add New > Upload*
3. Choose or drag&drop `versionpress-<version>.zip` to that page
4. Click *Install Now*
5. Activate the plugin
6. **Finish the activation process** by going into the new VersionPress section in the administration and clicking the *Activate* button

The last step is important, otherwise VersionPress wouldn't be able to track changes. The on-screen instructions will guide you through it. 


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


