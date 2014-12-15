# VersionPress-docs #

Content of user documentation.


## Note about docs versions

We no longer use Git branches to manage multiple versions of this documentation, "version toggles" are used instead – all versions available via the website are built from the single master branch.

There are a couple of simple conventions that make this work:

 - The main configuration (`content/config.yaml`) specifies **displayVersion**, e.g., `1.0`
 - Doc topics specify since which version they apply, e.g., `since: 2.0`.
     - This can be done on file level or whole section (folder) level, see below
     - Missing `since` is the same as `since: 0.0`
 - The docs site only renders topics that are valid for the displayVersion 

Versions are compared using **semver**, the first 2 digits are usually enough for our purposes.


### Example

The default configuration specifies which version should be displayed to the user:

    # content/config.yaml
    displayVersion: 1.0

And every doc topic specifies whether it should be visible for this version using the **`since`** metadata., e.g. in :

    ---
    since: 2.0
    ---
    
    # WP-CLI Commands
    
    Rest of the Markdown here...

The `since` tag can appear in two places:

 1. In file's "front matter" (see [Jekyll](http://jekyllrb.com/docs/frontmatter/))
 2. In section's `config.yaml` file. For instance, the whole `sync` section (folder) is only available since the 2.0 release.

This concept will probably expand to the file-heading / paragraph level as well, for instance, using something like:

    ## Subheading         [since: 2.0]

but this hasn't been needed / implemented yet.


## How to author the docs

See the [wiki](http://wiki.agilio.cz/versionpress:dokumentace#uzivatelska-dokumentace).


## How to test and deploy

Authoring and visually testing the documentation is simple:

1. Make sure that in the local folder structure, the `VersionPress-docs` and `VersionPress-docssite` folders sit side by side (and that they are named exactly this).
2. Install Node.js + NPM + Gulp + run `npm install` inside `VersionPress-docs`
3. Run `gulp watch`
4. Open the *VersionPress-docssite* project in Visual Studio and run it (Ctrl+F5)
5. Change the Markdown files as you wish, the content will be automatically copied to the test site (as long as `gulp watch` is running).

When the site is ready for deployment:

1. Stop gulp watch
2. Run plain `gulp` (it creates the `.changed` file in the destination again which is vital)
3. Use the *Publish...* wizard with the preconfigured `versionpress-docs` profile to push the contents to the server

Also, when the publish accompanies a **new version**, pay attention to [all the things that should be done on version bump](http://wiki.agilio.cz/versionpress:dokumentace#pripravit-veci-pro-dalsi-verzi).