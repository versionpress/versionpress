# Early Access

Currently, VersionPress is in an **Early Access phase**. We increase major version numbers quite rapidly, e.g., VersionPress is at 3.0 at the time of writing this, but **that does not mean it is production-ready.** As long as this Early Access notice is here, you have to be careful.


## Recommendations

 - **Ideally, use VersionPress for testing / dev purposes only**. Local, throw-away sites and workflows are ideal.

 - **If you're going to run VersionPress on a live site, <span style="color:red;">keep backup at all times</span>**. We really mean this. VersionPress manipulates the database during some operations and can break it if it interferes with some other plugin or has a bug in it.

 - **Controlled hosting environment** is recommended. VersionPress requires Git on the server and `proc_open()` enabled which only some hosts allow (see [hosting](../integrations/hosts) and [system requirements](./installation-uninstallation)).
 
 - **Compatibility with 3rd party plugins** and themes is often problematic. Generally, plugins with custom database data need special attention and are a long-term challenge. This is explained on the [external plugins](../feature-focus/external-plugins) page in more detail.

 - **Be familiar with WordPress and Git**. VersionPress at the Early Access stage is not suitable for non-technical users.

You can tell whether you are using an Early Access release of VersionPress from the top admin bar where there is a clear warning.



## EAP (discontinued)

Between January 2015 and March 2016, VersionPress used to be available through *Early Access Program* (EAP). It was discontinued when VersionPress moved to a fully open development model on GitHub.

We're leaving this note here in case you encounter "EAP" somewhere, e.g., in this page's URL :smile:.
