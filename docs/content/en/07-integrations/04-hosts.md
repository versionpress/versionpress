# Hosts

As described on the [installation page](../getting-started/installation-uninstallation), VersionPress currently has system requirements that are stricter than those of vanilla WordPress and are not commonly met on shared hosts. Most commonly, it is the requirement of Git being installed and `proc_open()` enabled to interact with it.

Many of our early users run VersionPress on dedicated / virtual servers that allow complete control, however, we also want to make VersionPress available on a common shared hosts and for that, we partner with several companies that are VersionPress friendly. There are also hosts that are not supported at the moment, for reasons listed below.


## Supported hosts

### SiteGround

[SiteGround](https://www.siteground.com/) is a popular WordPress host and it fully supports VersionPress.

### FastComet

[FastComet](http://www.fastcomet.com/) is an SSD cloud hosting with CloudFlare & 24/7 Premium Support. VersionPress works fine there without needing to do anything else.

### Uberspace

[Uberspace](https://uberspace.de/) is a German hosting company that supports VersionPress fine. The only small issue is that Git 1.7 is installed by default and VersionPress requires 1.9+ but that is fixed easily:

1. Install newer Git in your Uberspace, e.g. by using `toast`:

        $ toast arm https://www.kernel.org/pub/software/scm/git/git-2.4.0.tar.gz

2. Update Git path in `vpconfig.neon` to e.g. `git-binary: /home/vptest/.toast/armed/bin/git` ([learn more about VersionPress configuration](../getting-started/configuration))

All other requirements are met. 


## Unsupported hosts

### WP Engine

WP Engine will not allow `proc_open()` on their service. They might provide another hook in the future but it's not a priority for them at the moment.

### DreamHost

No details here but a user reported that DreamHost doesn't support VersionPress.


## More

Do you use VersionPress on a host that is not listed here? Let us know at info@versionpress.net. Thanks!