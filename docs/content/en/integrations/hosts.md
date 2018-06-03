# Hosts

As described on the [installation page](../getting-started/installation-uninstallation.md), VersionPress has stricter system requirements than vanilla WordPress – specifically, we require Git on the server and `proc_open()` enabled. This is for [good reasons](../feature-focus/git.md) but also means that hosting is a bit of a challenge. Most of our early users run VersionPress on dedicated servers or VPS's that allow complete control and that's still recommended, however, there are also a couple of shared hosts that fully support VersionPress today.

!!! Important
    The info here largely applies to the single-site features of VersionPress only. Version 2.0 added support for [sync / team workflows](../sync/index.md) that are even more tricky to support on a shared hosting. For those scenarios, VPS or a custom server is strongly recommended.

## Supported hosts

Here are a couple of hosts that we or our users confirmed work fine with VersionPress:

 - [x] [FastComet](http://www.fastcomet.com/) – SSD cloud hosting with CloudFlare & 24/7 Premium Support
 - [x] [Byte](https://www.byte.nl/) – performance webhosting in Netherlands
 - [x] [WebFaction](https://www.webfaction.com/) – hosting for developers
 - [x] [Uberspace](https://uberspace.de/) – hosting space in Germany (Git 1.7 pre-installed, upgradable to 1.9+)
 - [x] [Pair Shared Hosting](https://www.pair.com/hosting/shared/) – by pair Networks
 - [x] [Elbia Hosting (SK)](http://www.elbiahosting.sk/) – Slovak hosting company
 - [x] [SiteGround](https://www.siteground.com/) – VersionPress can be installed but [some issues have been reported](https://github.com/versionpress/support/issues/46)
 - [x] [WebHostFace](https://www.webhostface.com/) – SSD-powered hosting with Free SSLs & 24/7 Expert WordPress Support
 - [x] [45AIR](https://www.45air.com/) – Low Cost WordPress Hosting - Git 2.10+, full shell access, and VersionPress pre-installed.

!!! check "Help us improve this list"
    If you know of a host that supports VersionPress please **send a pull request**.


## Unsupported hosts

Here are some unsupported hosts for the moment. If you are their customer, please let them know that you'd be interested in VersionPress support – if they see more demand for it they might add support for it.

 - [ ] **WP Engine** – they have intentionally restricted environment that doesn't allow `proc_open()`. We acknowledge that WPE is important to our users and are actively looking into how to support it.
 - [ ] **DreamHost**
 - [ ] **FlyWheel** – they don't have Git installed

