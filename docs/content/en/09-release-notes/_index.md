# Release Notes #

The current release is **1.0-beta1**, released on 28-Nov-2014. Our versioning scheme is described below in the *Release versioning* section.

Release notes of all the versions shipped so far:

* [1.0-beta1](./release-notes/1.0-beta1)
* [1.0-alpha3](./release-notes/1.0-alpha3)
* [1.0-alpha2](./release-notes/1.0-alpha2)
* [1.0-alpha1](./release-notes/1.0-alpha1)

You can also view the [roadmap](./release-notes/roadmap).


## Release versioning ##

We use a **rapid release cycle** which means that *major versions* (1.0, 2.0 etc.) are shipped relatively often and we generally don't have *minor versions*, very much like browsers these days. We still have *patch releases* which are indicated by an optional third portion of the version number. Prerelease versions are marked by the `-alphaX`, `-betaX` or `-rcX` suffix.

An example of consequent releases might be:


    1.0-alpha1      <-- prerelease versions
    1.0-alpha2
    1.0-beta1
    1.0-beta2
    1.0-rc
    1.0             <-- STABLE RELEASE (major version)
    1.0.1           <-- patch releases
    1.0.2
    2.0-beta1
    2.0
    3.0
    3.0.1
    4.0
    ...


The prerelease markers have their well-defined meaning:

* **Alpha** means that we are not feature complete yet and that there *will* be bugs, possibly even severe ones. Never use an alpha release on a production site, with production database or generally with data you care for.
* **Beta** means that we are feature complete for the given release and relatively stable but running such version with production data is still not recommended. Never operate VersionPress beta without proper backups.
* **RC (Release Candidate)** is close to the final / stable version 