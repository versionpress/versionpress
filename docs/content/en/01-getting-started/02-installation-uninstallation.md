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

 - WordPress 4.1 or higher (3.9+ should work but is not thoroughly tested)
 - PHP 5.3 or higher
 - Git 1.9 or higher installed on the server
 - Apache or IIS 7+ web server
 - Safe mode turned off
 - `proc_open()` enabled
 - Write permissions in the site root and everywhere under it
 - No `wp-content/db.php` on the disk
 - No path customizations (e.g., custom location for `wp-content`)

In practice, this means that you need to have a lot of control over your server environment for the current version of VersionPress. We will be adding support for common shared hosting over time.

Here are notes on some of the requirements:


### Git

VersionPress takes a strategic dependency on [Git](http://git-scm.com/) which provides [many benefits](../feature-focus/git) but also requires this tool to be installed on the server and accessible from PHP. Make sure that `proc_open()` is enabled on the server and that the Git installation is in the PATH (if it's not, you can tell VersionPress where to find the binary via [Configuration](./configuration)).

Git **1.9** and newer are supported. Do not attempt to make VersionPress run with older releases (1.7 and 1.8 are still quite popular), there are known issues with them.

### PHP 5.3

WordPress can run on an old and [long unsupported](http://php.net/eol.php) PHP 5.2. We also started with this version but eventually dropped it so that we could use the newer language features and some 3<sup>rd</sup> party libraries that are 5.3+ only. We recommend using one of the [actively supported](http://php.net/supported-versions.php) PHP versions.

Note: VersionPress is currently not being tested on HHVM.


### The db.php drop-in

VersionPress currently uses the `wp-content/db.php` file to hook into some WordPress actions for the lack of better extensibility points (see [WP issue #29710](https://core.trac.wordpress.org/ticket/29710) and [this suggestion](https://wordpress.org/ideas/topic/multiple-dbphp-files-for-plugins)). This means that VersionPress will conflict with other plugins that want to use db.php which are usually some debug or caching plugins.

There is no easy way around this. We hope that WordPress will provide suitable hooks for us – you can go vote on the aforementioned ticket if this issue is important to you. 


### Path customizations

Some advanced users like having their plugins directory and other folders outside of the document root. This is currently not supported by VersionPress.


### Supported web servers

Apache 2.2+, IIS 7+ and nginx are supported. 

For Apache and IIS we automatically install access rules to protect direct access to certain locations. For nginx, please include `wp-content/plugins/versionpress/versionpress-nginx.conf` to your virtual host config.

If the locations cannot be protected automatically (e.g., due to global configuration), make sure direct access is denied for the following locations:

 - `/wp-content/vpdb`
 - `/wp-content/vpbackups`
 - `/wp-content/plugins/versionpress`
 - `/.git` 


### External libraries

VersionPress depends on several external libraries for launching Git processes, working with the file system, etc. The nature of PHP is such that if some other plugin happens to include the same library but in an incompatible version, it might cause issues to VersionPress – or vice versa. There is not much we can do about it so here's just the list of those dependencies so that you know:

 - `tracy/tracy` 2.2
 - `nette/utils` 2.2
 - `nette/robot-loader` 2.2
 - `nette/neon` 2.2
 - `symfony/process` 2.5
 - `symfony/filesystem` 2.5
 - `michelf/php-markdown` 1.4
 - `ifsnop/mysqldump-php` 1.x


## Installation

VersionPress can be obtained via the main [versionpress.net website](http://versionpress.net/). When you have the ZIP file, the installation is pretty standard:

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

The update process is manual at the moment and always described in the **release notes** for the new version. We generally strive for updatable releases but in some cases, especially during the EAP period, you might need to disable the plugin, remove it and install it fresh. Again, [release notes](../release-notes) will tell you what to do. 


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


