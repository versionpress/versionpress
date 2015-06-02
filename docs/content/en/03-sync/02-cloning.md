# Cloning a Site #

This process creates a clone of a site which is an entirely separate WordPress installation that just contains the same data. Do any updates you wish in the clone and when done, [merge](./merging) them back to the original site.

VersionPress supports two methods of cloning – either from command line using WP-CLI or from the admin pages ("GUI" method).


## WP-CLI method ##

This method clones a site from command line using WP-CLI. You need to have [WP-CLI installed and working](../feature-focus/wp-cli) on your computer / server. 


Cloning is executed from the root of your site that you want to clone like this:

    wp vp clone --name=test

Let's say that we executed it in `C:\www\site01` that was using the prefix `wp_` and was served as `http://localhost/site01`. The command did a couple of things based on convention:

 * It created a new folder C:\www\\**test**.
 * It created DB tables with prefix **wp\_test\_**.
 * The tables have been populated with the data.
 * New site is being served at http://localhost/test.

The URL and database settings are all configurable. See `wp help vp clone`.

## GUI method ##

To be implemented