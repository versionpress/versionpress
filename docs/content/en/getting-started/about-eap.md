# Developer Preview

Currently, VersionPress is a **"Developer Preview"**. It can be tried on simpler sites for development purposes but it's not production-ready yet.


## Considerations

 - A safe bet is to use VersionPress for testing / dev purposes only. Local, throw-away sites and workflows are ideal.
 - Production deployment is strongly discouraged but if you attempt it despite the warning, at least **<span style="color:red;">keep backup at all times</span>**.
 - Compatibility with WordPress plugins and themes is often problematic, see [3rd Party Integrations section](../integrations/index.md).
 - Compatibility with hosts is often problematic as Git and `proc_open()` are required on the server. See [Hosting](../integrations/hosts.md) and [System requirements](./installation-uninstallation.md).
 - Be familiar with WordPress and Git.

You can tell whether you are using a Developer Preview / Early Access release of VersionPress from the top admin bar.

!!! note "Note on 'Early Access' and 'EAP'"
    Between January 2015 and March 2016, VersionPress used to be available through the *Early Access Program (EAP)*. It was discontinued when VersionPress [moved](https://blog.versionpress.net/2016/04/going-open-source/moved) to a fully open development model in April 2016.

    Between April 2016 and May 2017, the term "Early Access" was used. We then switched to "Developer Preview" which better indicates the project status. See [issue #1201](https://github.com/versionpress/versionpress/issues/1201)
