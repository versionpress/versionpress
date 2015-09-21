# Hosts

As described on the [installation page](../getting-started/installation-uninstallation), VersionPress has stricter system requirements than vanilla WordPress – specifically, we require Git on the server and `proc_open()` enabled. This is for [good reasons](../feature-focus/git) but also means that hosting is a bit of a challenge. Most of our early users run VersionPress on dedicated servers or VPS's that allow complete control and that's still recommended, however, there are also a couple of shared hosts that fully support VersionPress today.

<div class="important">
  <strong>Important</strong>
  <p>The info here largely applies to the single-site features of VersionPress only. Version 2.0 added support for [sync / team workflows](../sync) that are even more tricky to support on a shared hosting. For those scenarios, VPS or a custom server is strongly recommended.</p> 
</div>

## Supported hosts

Here are a couple of hosts that we or our users confirmed work fine with VersionPress:

 - [FastComet](http://www.fastcomet.com/) – SSD cloud hosting with CloudFlare & 24/7 Premium Support
 - [SiteGround](https://www.siteground.com/) – a popular WordPress host
 - [Byte](https://www.byte.nl/) – performance webhosting in Netherlands
 - [WebFaction](https://www.webfaction.com/) – hosting for developers
 - [Uberspace](https://uberspace.de/) – hosting space in Germany (Git 1.7 pre-installed, upgradable to 1.9+) 

<div class="note">
  <strong>Help us improve this list</strong>
  <p>If you know of a host that supports VersionPress please let us know at **info@versionpress.net**.</p> 
</div>



## Unsupported hosts

Here are some unsupported hosts for the moment. If you are their customer, please let them know that you'd be interested in VersionPress support – if they see more demand for it they might add support for it.

 - **WP Engine** – they have intentionally restricted environment that doesn't allow `proc_open()`. We acknowledge that WPE is important to our users and are actively looking into how to support it.
 - **DreamHost**
 - **FlyWheel** – they don't have Git installed

