# User Documentation for VersionPress #

## How to author

See [wiki](http://wiki.agilio.cz/versionpress:dokumentace#uzivatelska-dokumentace) on how to author the documentation.

## Testing and deployment

1. Make sure the in the local folder structure, the `VersionPress-docs` and `VersionPress-docssite` folders are beside each other (and that they are named this way).
2. Run the `copy-to-docssite.bat` script.
    * It will move the Markdown files to docs site's App_Data folder and create a `.changed` file which invalidate e.g. its navigation, cached files etc.
3. Open the docssite project in Visual Studio
4. Test it locally (Ctrl+F5)
5. **Run the copy-to-docssite.bat again.** This is so that the `.changed` file is there before the last step - the deployment.
6. Use the *Publish...* wizard with the preconfigured `versionpress-docs` profile to push the contents  to the server.