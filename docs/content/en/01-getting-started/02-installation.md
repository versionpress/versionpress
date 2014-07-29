# Installing VersionPress

VersionPress ships as a standard plugin but there are two important things to be aware of:

* VersionPress has **stricter system requirements** than is usual
* Its **activation is a two-step process**

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

VersionPress activation is a two-step process. The first step is same as with any other plugin but at that point, VersionPress hasn't yet scanned the website or built its internal repository. **It won't do anything useful until you finish the activation.**

## Uninstalling VersionPress

TODO

