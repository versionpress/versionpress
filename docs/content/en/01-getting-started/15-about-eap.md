# About EAP

Early Access Program (EAP) is a program under which we release VersionPress for the first couple of versions. It explicitly marks the plugin as relatively immature, although we try to do as much testing before every release as possible. You can find the program details on the main [versionpress.net](http://versionpress.net/) website, from the technical perspective, you only need to know this:

 - **Keep site backup at all times**. Seriously. VersionPress revert operations manipulate the database, and if there are issues with it the site may be left in a broken state. Again, we try to do as much as possible to prevent that but for EAP releases, backups are mandatory.
 - **Be familiar with WordPress and Git**. While the big promise of VersionPress is that it will be great versioning solution for masses, during the EAP releases it will be useful if you are well-versed with WordPress and have at least some knowledge of Git and its command-line tools.
 - **Approach VersionPress with care**, especially when it comes to complex third-party plugins like e-commerce solutions, etc. This is explained on the [external plugins](../feature-focus/external-plugins) page.

You can tell whether you are using an EAP release of VersionPress from the top admin area where there will be a clear EAP warning.