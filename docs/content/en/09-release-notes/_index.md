# Release Notes

The current release is **[2.2](./release-notes/2.2)**, released on 15 Dec 2015, and it is an [EAP release](getting-started/about-eap). VersionPress can be obtained via the main **[versionpress.net](http://versionpress.net/)** site.


## Release versioning

VersionPress releases are marked so that it's easy to understand what to expect of them. Here is a couple of rules we follow:

 - VersionPress generally **bumps a major version with every release**, so while WordPress uses a sequence like `4.1` → `4.2` → `4.3` etc., we generally use `4.0` → `5.0` → `6.0` etc., similarly to modern web browsers.
 - **Minor releases**, e.g., `4.1`, with minor feature changes can happen but we generally aim to avoid them.
 - **Patch releases** use the third segment of version identifier, e.g., `4.0.1`. We aim to avoid them too :-)
 - **Preview versions** are marked with labels like `4.0-alpha1`, `4.0-beta1`, `4.0-rc2` and similar. Pre-release names have their well-defined meaning:
     - **Alpha** means that we are not feature complete yet and that there *will* be bugs, possibly even severe ones. Never use an alpha release on a production site, with production database or generally with any data that you care for.
     - **Beta** means most if not all features are complete and the stability is quite good, however, running such version in production is still strongly discouraged.
     - **RC (Release Candidate)** is close to the final / stable version.

Versions are compared and ordered by the same rules that [semver](http://semver.org/) uses – versions with two segments assume the ".0" at the end so 1.0 is effectively the same as 1.0.0.

## Roadmap

See [roadmap here](./release-notes/roadmap).
