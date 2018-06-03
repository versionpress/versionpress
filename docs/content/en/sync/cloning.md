# Cloning a Site #

Cloning a site creates a separate WordPress instance that looks like the original one but doesn't share any of its files or database tables. Making changes in the clone doesn't affect the original site in any way, until those changes are [merged](./merging.md) back.


## The 'clone' command

Currently, the cloning functionality is exposed via a WP-CLI command. You need to have [WP-CLI installed and working](../feature-focus/wp-cli.md) on your machine.

Cloning is started from the root of the site by executing the **vp clone** command. In its simplest form, it only needs the `--name` parameter:

``` bash
wp vp clone --name=staging
```

If the site was `C:\www\mysite` and it was served as `http://localhost/mysite`, the command did the following:

 * Created a new folder `C:\www\staging` and cloned the site files there
 * Created database tables prefixed with `wp_staging_...` and filled them with data
 * Made the site available at `http://localhost/staging`

The original site also stored a named reference to the clone so for instance, you can later [pull](./merging.md) from the clone by executing a command like `wp vp pull --from=staging`.

The URL and database settings are all configurable so for example, you could run the command like this:

``` bash
    wp vp clone --name=staging
                --dbname=staging_db
                --dbuser=...
                --siteurl=http://staging.mysite.dev/
```

Full help is available by running `wp help vp clone`.

