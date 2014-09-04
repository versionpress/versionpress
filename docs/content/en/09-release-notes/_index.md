# Release Notes #

The current release is **1.0-alpha2**, released on 4-Sep-2014. Our versioning scheme is described below in the *Release versioning* section.

Release notes of all the versions shipped so far:

* [1.0-alpha2](./release-notes/1.0-alpha2)
* [1.0-alpha1](./release-notes/1.0-alpha1)

You can also view our [roadmap](./release-notes/roadmap).


## Release versioning ##

We version our releases using the following scheme:

**MAJOR.PATCH[-prereleaseX]**

We have no MINOR part so generally, our releases will be version 1 quickly followed by version 2 quickly followed by version 3 etc., similarly to what for example browsers do these days.

Before we release version 1.0, there will be a couple of testing releases alpha or beta followed by a number. For example, the first release is `1.0-alpha1` which will be followed by `1.0-alpha2`, then possibly `1.0-beta1`, `1.0-beta2` etc. and finally leading to the general `1.0` release. We release "point" releases such as `1.1` or `2.13` only as bug fix releases and never add any functionality in them.

Please make sure you understand what alpha and beta means:

* **Alpha** means that we are not feature complete yet and that there *will* be bugs. Never use an alpha release on a production site or with production database. This is strictly for testing only.
* **Beta** means that we are feature complete and relatively stable but running such version with production data is still not recommended. Never operate VersionPress beta without proper backups.