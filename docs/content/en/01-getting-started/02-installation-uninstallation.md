# Installation and Uninstallation

VersionPress ships as a standard plugin but there are two important things to be aware of:

1. VersionPress has **stricter system requirements** than usual
2. Its **activation is a two-step process**

Both things are important, please read on.


## System requirements

The server environment must currently match these requirements:

  * WordPress 3.8 or higher
  * PHP 5.3 or higher
  * Git 1.9 or higher installed on the server
  * Safe mode turned off
  * The `proc_open()` function enabled

In practice, this means that you need to have control over your server as these will typically not be available in a common hosting scenario. We are aware that this is an issue and will be removing these requirements in some future release. 


## Installation

The basic installation is the same as with any other plugin, however, note the last step:

1. Log in to the admin screens
2. Go to *Plugins > Add New > Upload*
3. Choose or drag&drop `versionpress.zip` to that page
4. Click *Install Now*
5. Activate the plugin
6. **Finish the activation process** by going into the new VersionPress section in the administration and clicking the *Activate* button

The last step is important, otherwise VersionPress wouldn't be able to track changes. The on-screen instructions will guide you. 

## Uninstallation

Uninstallation is a two-step process as with any other plugin:

1. You first **deactivate** the plugin on the Plugins admin screen. The repository still exists after this step.
2. You then **delete** the plugin to get rid of all its files *and the repository*. Be careful before you do this.

Note that once VersionPress is deactivated, **it cannot be properly reactivated again with the same repository**. It will work partly – the reactivation process can create new commits in the old repository but commands like Undo or Rollback will not be available for the old commits.


## VersionPress states at a glance

| State | Git repo exists? | VersionPress tracking changes? |
| :------------- | :-----: | :-----: |
| WP site without VersionPress | No | No |
| Installed  | No | No |
| Activated on plugin screen | **Still not** | **Still not** |
| Activation finished on VersionPress screen - the plugin is *active* | **Yes** | **Yes** |
| Deactivated (on plugin admin screen) | Yes | **No** |
| Reactivated (similar to state 3) | Yes (but obsolete) | **Still not** |
| Fully active again (similar to step 4) | Yes (new repo or continued old one) | **Yes** |
| Uninstalled | **No** | No |
 
Especially note that the uninstallation also removes the repository so be careful before you do that.

