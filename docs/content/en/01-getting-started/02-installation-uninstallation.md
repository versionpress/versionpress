# Installation and Uninstallation

VersionPress ships as a standard plugin but there are two important things to be aware of:

1. VersionPress has **stricter system requirements** than usual
2. Its **activation is a two-step process**

Both things are important, please read on.


## System requirements

The server environment must currently match these requirements:

  * WordPress 3.8 or higher
  * PHP 5.3 or higher
  * Git 1.7 or higher installed on the server 
  * Safe mode turned off
  * The `proc_open()` function enabled

In practice, this means that you need to have control over your server as these will typically not be available in a common hosting scenario. We are aware that this is an issue and will be lowering these requirements in some future release. 


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

* Once VersionPress is deactivated, **it cannot be fully reactivated again on the same repository**. This means that while you can initialize VersionPress again and the presence of the old repository will not be a problem, features like Undo or Rollback will only be available for the *new* commits, created by the current activation of VersionPress.
* **On uninstallation, the Git repository itself is moved to a backup folder** under `wp-content`. This means that after VersionPress uninstallation the site will no longer be considered Git-version-controlled but at the same time, if you need to restore or download the repository you still can.


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
 
<sup>1)</sup> The repo might exist if you created it manually or if VersionPress was previously installed. It is not a problem – VersionPress will happily add commits to the existing repo. But a common scenario is that there is no default Git repo in which case VersionPress will create one.


