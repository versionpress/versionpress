# VersionPress-docs #

Content of user documentation.

## How to author

See [wiki](http://wiki.agilio.cz/versionpress:dokumentace#uzivatelska-dokumentace) on how to author the documentation.

## How to test and deploy

Authoring and visually testing the documentation is simple:

1. Make sure that in the local folder structure, the `VersionPress-docs` and `VersionPress-docssite` folders sit side by side (and that they are named exactly this).
2. Install Node.js + NPM + Gulp + run `npm install`
3. Run `gulp watch`
4. Open the VersionPress-docssite project in Visual Studio and run it (Ctrl+F5)
5. Change the Markdown files as you wish, the content will be automatically copied to the test site (as long as `gulp watch` is running).

When the site is ready for deployment:

1. Stop gulp watch
2. Run plain `gulp` (it creates the `.changed` file in the destination again which is vital)
3. Use the *Publish...* wizard with the preconfigured `versionpress-docs` profile to push the contents to the server

Also, when the publish accompanies a **new version**, pay attention to [all the things that should be done on version bump](http://wiki.agilio.cz/versionpress:dokumentace#pripravit-veci-pro-dalsi-verzi).