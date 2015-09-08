# Cloning a Site #

Cloning a site creates an entirely separate WordPress installation that just happens to look exactly the same as the original site. Technically, both the files and the database are brand new, not connected to the original site in any way.

Do any updates you wish in the clone and when done, [push](./merging) them back to the original site.


## WP-CLI command

Currently, the cloning functionality is exposed via a WP-CLI command only. You need to have [WP-CLI installed and working](../feature-focus/wp-cli) on your computer / server.

Cloning is started from the root of the original site by executing this command:

    wp vp clone --name=test

For example, if it was run in `C:\www\mysite` on a site that was served as `http://localhost/mysite`, the command did the following:

 * Created a new folder `C:\www\mysite-test`
 * Git-cloned the files there
 * Created database tables prefixed with `wp_test_...`
 * Filled it with data
 * Updated the site URL to be `http://localhost/site01-test`

The URL and database settings are all configurable, run the following command to see the full help:

    wp help vp clone


## GUI method ##

In a future update of VersionPress, we will have a GUI method to achieve the same.