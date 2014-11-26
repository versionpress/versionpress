# Release Notes #

The current release is **1.0-beta1**, released on 26-Nov-2014. Our versioning scheme is described below in the *Release versioning* section.

Release notes of all the versions shipped so far:

* [1.0-beta1](./release-notes/1.0-beta1)
* [1.0-alpha3](./release-notes/1.0-alpha3)
* [1.0-alpha2](./release-notes/1.0-alpha2)
* [1.0-alpha1](./release-notes/1.0-alpha1)

You can also view the [roadmap](./release-notes/roadmap).


## Release versioning ##

We use a **rapid release cycle** which means that major versions are shipped relatively often and we generally don't have "minor" versions, very much like browsers these days.

We version our releases using the following scheme:

**`MAJOR.PATCH[-prereleaseX]`**

An example of a couple of consequent releases may be:

    1.0-alpha1  ->  1.0-alpha2  ->  1.0-beta1  ->  1.0  ->  2.0  ->  2.1 (patch release) -> ... 

The prerelease markers have their well-defined meaning:

* **Alpha** means that we are not feature complete yet and that there *will* be bugs, possibly even severe ones. Never use an alpha release on a production site, with production database or generally with data you care for.
* **Beta** means that we are feature complete for the given release and relatively stable but running such version with production data is still not recommended. Never operate VersionPress beta without proper backups.