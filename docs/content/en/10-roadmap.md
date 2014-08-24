# Roadmap #

VersionPress is a young project and it is important to understand where we are to have the right set of expectations.

## Release versioning ##

We version our releases according to the following scheme:

**MAJOR.PATCH[-prereleaseX]**

We have no *minor* part so generally, our releases will be version 1 quickly followed by version 2 quickly followed by version 3 etc., similarly to what browsers do these days.

Before we release version 1.0, there will be a couple of testing releases alpha or beta followed by a number. For example, the first release is `1.0-alpha1` which will be followed by `1.0-alpha2`, then possibly `1.0-beta1`, `1.0-beta2` etc. and finally leading to the general `1.0` release.

* **Alpha** means that we are not feature complete yet and that there *will* be bugs. Never use an alpha release on a production site or with production database.
* **Beta** means that we are feature complete and relatively stable but running such version with production data is still not recommended. 