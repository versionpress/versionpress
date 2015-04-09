# Release Notes

The current release is **1.0-rc3**, released on 09-Apr-2015, which is an [EAP release](getting-started/about-eap). VersionPress can be obtained via the main **[versionpress.net](http://versionpress.net/)** site.

Release notes of all the versions shipped so far:

* [1.0-rc3](./release-notes/1.0-rc3)
* [1.0-rc2](./release-notes/1.0-rc2)
* [1.0-rc1](./release-notes/1.0-rc1)
* [1.0-beta2](./release-notes/1.0-beta2)
* [1.0-beta1](./release-notes/1.0-beta1)
* [1.0-alpha3](./release-notes/1.0-alpha3)
* [1.0-alpha2](./release-notes/1.0-alpha2)
* [1.0-alpha1](./release-notes/1.0-alpha1)

You can also view the [project roadmap](./release-notes/roadmap).


## Release versioning

VersionPress releases are marked so that it's easy to understand what to expect of the release. Here is a couple of rules we follow:

 - VersionPress generally **bumps a major version number with every release**, so while WordPress uses a sequence like `4.1`, `4.2`, `4.3` etc., we will generally use `4.0`, `5.0`, `6.0` etc., similarly to modern web browsers.
 - **Minor releases**, e.g., `4.1`, with minor feature changes can happen but we generally aim to avoid them
 - **Patch releases** use the third segment of version identifier, e.g., `4.0.1`. We aim to avoid them too :-)
 - **Preview versions** are marked with labels like `4.0-alpha1`, `4.0-beta1`, `4.0-rc` and similar. Prerelease names have their well-defined meaning:
     - **Alpha** means that we are not feature complete yet and that there *will* be bugs, possibly even severe ones. Never use an alpha release on a production site, with production database or generally with data you care for.
     - **Beta** means that we are feature complete for the given release and relatively stable but running such version with production data is still not recommended. Never operate VersionPress beta without proper backups.
     - **RC (Release Candidate)** is close to the final / stable version 

Versions are compared and ordered by the same rules that [semver](http://semver.org/) uses (versions with two segments only like 1.0 are effectively 1.0.0 for comparison's sake).