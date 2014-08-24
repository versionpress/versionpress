# User Documentation for VersionPress #

## How to author

See [wiki](http://wiki.agilio.cz/versionpress:dokumentace#uzivatelska-dokumentace) on how to author the documentation.

## Deployment

Use the `copy-to-docssite.bat` script. Basically, two things need to be done:

1. **Copy the `content` directory** to the `App_Data` folder or the [VersionPress-docssite project](https://bitbucket.org/agilio/versionpress-docssite).
2. **Create a `.changed` file** in `App_Data\content`. The docs site use this file to invalidate e.g. its navigation, cached files etc.