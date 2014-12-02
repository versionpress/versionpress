# Cloning a Site #

This process creates a clone of a site which is an entirely separate WordPress installation that just contains the same data. Do any updates you wish in the clone and when done, [merge](./merging) them back to the original site.

VersionPress supports two methods of cloning – either from command line using WP-CLI or from the admin pages ("GUI" method).


## WP-CLI method ##

This method clones a site from command line using WP-CLI. You need to have [WP-CLI installed and working](../feature-focus/wp-cli) on your computer / server. 


Cloning is executed from the root of your site that you want to clone like this:

    wp vp clone --name=test

Let's say that we executed it in `C:\www\site01` that was using the database called `site01db` and was served as `http://localhost/site01`. The command did a couple of things based on convention:

 * It created a new folder C:\www\\**site01_test**
 * It created a new database **site01db_test**
     * Note: the user specified in `wp-config.php` for database connection *must have a permission* to create new databases
 * New Git branch **test** was created
     * Git branches are just symbolic names that are useful when merging the work back together (amongst other things).
 * New database has been populated with the data
 * New site is being served at http://localhost/site01_test

## GUI method ##

To be implemented