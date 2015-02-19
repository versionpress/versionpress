# Installation and Uninstallation

VersionPress ships as a standard plugin but there are two important things to be aware of:

1. VersionPress has **stricter system requirements** than usual
2. Its **activation is a two-step process**

Both things are important, please read on.


## System requirements

The server environment must match certain requirements, some of which are checked automatically on VersionPress activation. We also recommend some other setup steps below.

<figure style="width: 80%;">
  <img src="../../media/requirements-checker.png" alt="Pre-activation check" /> 
  <figcaption>Pre-activation check performed by VersionPress</figcaption>
</figure>

<div class="important">
  <strong>Important</strong>
  <p>VersionPress is a lot more involved than most other WordPress plugins. Please pay attention to this section before proceeding with installation.</p> 
</div>

Minimum system requirements are (as a general rule, **we recommend using the latest versions of everything**):

 - WordPress 3.9 or higher
 - PHP 5.3 or higher
 - Git 1.9 or higher installed on the server *(checked automatically before activation)*
 - Apache or IIS 7+ web server *(checked automatically before activation)*
 - Safe mode turned off *(checked automatically before activation)*
 - `proc_open()` enabled *(checked automatically before activation)*
 - Write permissions in the site root and everywhere under it *(checked automatically before activation)*
 - No `wp-content/db.php` on the disk *(checked automatically before activation)*
 - No path customizations (e.g., custom location for `wp-content`) *(checked automatically before activation)*

In practice, this means that you need to have a lot of control over your server environment for the current version of VersionPress. We will be adding support for common shared hosting over time.

Here are notes on some of the requirements:


### Git

VersionPress takes a strategic dependency on [Git](http://git-scm.com/) which provides [many benefits](../feature-focus/git) but also requires this tool to be installed on the server and accessible from PHP. Make sure that `proc_open()` is enabled on the server and that the Git installation is in the PATH. (Since version 1.0-rc2, you can also explicitly tell VersionPress where to find the binary, see [Configuration](./configuration).)

### PHP 5.3

WordPress can run on an old and [long unsupported](http://php.net/eol.php) PHP 5.2. We also started with this version but eventually dropped it so that we could use the newer language features and some 3<sup>rd</sup> party libraries that are 5.3+ only.


### The db.php "hook"

VersionPress currently uses the `wp-content/db.php` file to hook into some WordPress actions for the lack of better extensibility points (see [issue #29710](https://core.trac.wordpress.org/ticket/29710)). This means that VersionPress will conflict with other plugins that want to use this single extensibility point – thankfully there are not that many of them.


### Path customizations

Some advanced users like having their plugins directory and other folders outside of the document root. This is currently not supported by VersionPress.


### Supported web servers

Apache or IIS 7+ web servers are supported out of the box, other web server need manual configuration to prevent direct access from these locations:

 - `wp-content/vpdb`
 - `<root>/.git` 


### External libraries

VersionPress depends on several external libraries e.g. for launching Git processes, working with the file system, etc. The nature of PHP is such that if some other plugin happens to include the same library but in an incompatible version, it might cause issues to VersionPress – or vice versa. There is not much we can do about it so here's just the list of those dependencies so that you can troubleshoot yourself if needs be:

 - `nette/nette-minified`
 - `symfony/process`
 - `symfony/filesystem`
 - `michelf/php-markdown`


## Installation

The installation is pretty standard, just note the last step:

1. Log in to the admin screens
2. Go to *Plugins > Add New > Upload*
3. Choose or drag&drop `versionpress-<version>.zip` to that page
4. Click *Install Now*
5. Activate the plugin
6. **Finish the activation process** by going into the new VersionPress section in the administration and clicking the *Activate* button

The last step is important, otherwise VersionPress wouldn't be able to track changes. The on-screen instructions will guide you through it.

Upon successful activation, you should see a screen like this:

<figure style="width: 80%;">
  <img src="../../media/successful-activation.png" alt="VersionPress activated" /> 
  <figcaption>VersionPress successfully activated</figcaption>
</figure>


## Update / upgrade

The update process is manual at the moment, and actually very simple:

 1. Get the latest version of VersionPress
 2. Replace the folder `wp-content/plugins/versionpress` with the new version

However, not that **some versions might require specific steps** so please always consult the [release notes](../release-notes).




## Uninstallation

Uninstallation is a standard two-step process:

1. You first **deactivate** the plugin on the *Plugins* admin screen
2. You then **delete** the plugin to get rid of all its files
3. *Optional:* Manually download or delete a repository backup which was created under `wp-content/backup`. 

There are two important things to note:

* Once VersionPress is deactivated, **it cannot be fully reactivated again on the same repository**. This means that while you can initialize VersionPress again and the presence of the old repository will not be a problem, features like Undo or Rollback will only be available for *new* commits, created by the current activation of VersionPress. This is technical limitation that is not easy to overcome.
* **On uninstallation, the Git repository is moved to a backup folder** under `wp-content/vpbackups`. You can download or recover it from there manually.
    * Note: VersionPress will only remove / backup the repository if it detects that it was VersionPress-initiated repository. If you created the Git repository manually before installing VersionPress the repository will not be touched.  


## VersionPress states at a glance

To sum up the previous text, here are the states that the site can be in:

| State | Git repo exists? | VersionPress tracking changes? |
| :------------- | :-----: | :-----: |
| WP site without VersionPress | No<sup>1)</sup> | No |
| VersionPress installed  | No | No |
| VersionPress activated on the *Plugins* screen | **Still no** | **Still no** |
| Activation finished on VersionPress screen - the plugin is *active* | **Yes** | **Yes** |
| Deactivated (on plugin admin screen) | Yes | **No** |
| VersionPress reactivated on the *Plugins* screen (similar to step 3) | Yes (but obsolete) | **Still no** |
| Fully activated again (similar to step 4) | Yes | **Yes** |
| Uninstalled | **No** (backed up) | No |
 
<sup>1)</sup> The repo might exist if you created it manually or if VersionPress was previously installed. It is not a problem – VersionPress will happily add commits to the existing repository but a common scenario is that there is no default Git repository and VersionPress creates one.


