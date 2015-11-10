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
 - No path customizations at the moment (e.g., custom location for `wp-content`)

On top of that, if you want to use the [multi-instance / sync features](../sync) of VersionPress 2.0, probably even more control over the requirement will be required. In practice, this means that a custom server / VPS is your best bet. See also the section on [hosting providers](../integrations/hosts).

Here are notes on some of the requirements:


### Git

VersionPress takes a strategic dependency on [Git](http://git-scm.com/) which provides [many benefits](../feature-focus/git) but also requires this tool to be installed on the server and accessible from PHP. Make sure that `proc_open()` is enabled on the server and that the Git installation is in the PATH (if it's not, you can tell VersionPress where to find the binary via [Configuration](./configuration)).

Git **1.9** and newer are supported. Do not attempt to make VersionPress run with older releases (1.7 and 1.8 are still quite popular), there are known issues with them.


### Supported web servers

We recommend Apache or nginx (as [WordPress itself](https://wordpress.org/about/requirements/)) but almost any web server should work. Just pay attention to two things:

 1. **Write permissions**. The user that runs PHP and the eventual Git process needs to have write access into the locations listed below and the `sys_get_temp_dir()`. Initialization page checks this automatically and the [system info page](../troubleshooting/system-info-page) has a dedicated section on permissions if you need more info.
     - IIS users, please [read this page](../troubleshooting/iis).
 2. **Access rules**. The locations listed below should be protected against direct requests.

The sensitive locations are:

 - `/wp-content/vpdb`
 - `/wp-content/vpbackups`
 - `/wp-content/plugins/versionpress`
 - `/.git`

We ship `.htaccess` rules for Apache, `web.config` rules for IIS and `wp-content/plugins/versionpress/versionpress-nginx.conf` template for nginx but please confirm manually that direct access to e.g. `yoursite/.git/config` is prevented.


### PHP 5.3

WordPress can run on an old and [long unsupported](http://php.net/eol.php) PHP 5.2. We also started with this version but eventually dropped it so that we could use the newer language features and some 3<sup>rd</sup> party libraries that are 5.3+ only. We recommend using one of the [actively supported](http://php.net/supported-versions.php) PHP versions.

Note: VersionPress is currently not being tested on HHVM.


### Path customizations

Some advanced users like having their plugins directory and other folders outside of the document root. This is currently not supported by VersionPress (it will be one day).


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

The update process is manual at the moment and always described in the [release notes](../release-notes) for a specific version. We will have an automated upgrade method in the future.

### Manual upgrade

Manual upgrade is always described in the release notes for the release, including info about how to do it, which versions are upgradable etc. Generally, do this:

 1. Put the site in a [maintenance mode](http://www.hongkiat.com/blog/wordpress-maintenance/)
 2. Replace the `wp-content/plugins/versionpress` directory on the server
 3. Do any version-specific changes that are described in the [release notes](../release-notes)
 4. Disable the maintenance mode

This keeps the VersionPress repository in place and active which means that you will be able to revert the older changes, go back to the states done by the previous version of VersionPress, etc. If you decide to deactivate, delete and re-upload VersionPress it will create a new repository for you which is typically not something you'd want. (See the end of this page for an overview of possible VersionPress states.)


### Automated upgrade

This is coming in a future update of VersionPress. See the [roadmap](../release-notes/roadmap).

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


