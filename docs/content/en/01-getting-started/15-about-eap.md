# Early Access

Currently, VersionPress is an **Early Access software**. It is not recommended for production use yet.

> Note that we rapidly increase major version numbers so for example at the time of writing this, VersionPress is v3.0. **This does not mean it is production-ready.** As long as this Early Access notice is here, you have to be careful.


## Recommendations

 - **Ideally, use VersionPress for testing / dev purposes only**. Local, throw-away sites and workflows are ideal.

 - **If you're going to run VersionPress on a live site, <span style="color:red;">keep backup at all times</span>**. We really mean this. VersionPress manipulates the database during some operations and can break it if it interferes with some other plugin or has a bug in it.

 - **Controlled hosting environment** is recommended. VersionPress requires Git on the server and `proc_open()` enabled which only some hosts allow (see [hosting](../integrations/hosts) and [system requirements](./installation-uninstallation)).

 - **Be familiar with WordPress and Git**. While the big promise of VersionPress is that it will be usable by everyone one day, at this stage knowledge of Git is often necessary.

 - **Approach VersionPress with care**, especially when it comes to complex third-party plugins like e-commerce solutions, "page builders" etc. This is explained on the [external plugins](../feature-focus/external-plugins) page in more detail.

You can tell whether you are using an Early Access release of VersionPress from the top admin bar where there is a clear warning.



## EAP (discontinued)

In the early days, VersionPress used to be available through a program called *Early Access Program* (EAP). It was discontinued when VersionPress moved to a fully open-source development model in March 2016.

We're leaving this note here in case you encounter "EAP" somewhere, e.g., in this page's URL :)