# Configuration

Some technical aspects of VersionPress can be configured by defining constants in `wp-config.php` or using an associated WP-CLI command. Furthermore, VersionPress introduces a `wp-config.common.php` file that is required by `wp-config.php` and stores common (shared) configuration.


## Local vs. shared configuration

Most of the constant in `wp-config.php` are local, specific to a given environment. For example, database connection needs to be different on staging and production sites. Because of that, VersionPress omits `wp-config.php` from version control, however, some options should – or even must – be shared. For example, `WP_CONTENT_DIR` or `WP_PLUGIN_DIR` must be shared as the site structure needs to be the same on all environments.

To support this, VersionPress 3.0 introduced a `wp-config.common.php` file which is version-controlled and `require`'d by the built-in `wp-config.php` file. The whole system looks like this:

**wp-config.php** (comes with WordPress, .gitignored):


```
<?php
include_once __DIR__ . '/wp-config.common.php';

define('DB_NAME', 'dbname');
define('DB_USER', 'dbuser');
// etc.
```

**wp-config.common.php** (created when VersionPress is activated, version-controlled):

```
<?php
define('WP_CONTENT_DIR', 'custom_dir');
```

You can define all the common WordPress config constants in these files (e.g., `WP_DEBUG`, `AUTOSAVE_INTERVAL` and [others](https://codex.wordpress.org/Editing_wp-config.php)) but VersionPress also comes with its own config constants.


## VersionPress config constants

The constants below influence some technical aspects of how VersionPress works. You should not typically need to update them but if you do, we strongly recommend using a WP-CLI command `wp vp config` to do it. For example, to set a custom Git path, run:

```
wp vp config VP_GIT_BINARY '/custom/path/to/git'
```

This will work if you have [WP-CLI installed](https://github.com/wp-cli/wp-cli/wiki/Alternative-Install-Methods) and VersionPress activated.

Below are listed some of the supported constants. To get an always-up-to-date list, run `wp help vp config`.


<span id="git-binary"></span>
### VP_GIT_BINARY

*Default: `git`*  
*Configuration file: `wp-config.php`*

By default, VersionPress calls just `git` which leaves the path resolution up to the operating system. That might be problematic on some server configurations which use different `PATH` for different users (the web server user might not be the same user under which you are logged in), there might be some `PATH` caching involved, etc. If VersionPress cannot detect Git for some reason, use this option.

### VP\_VPDB\_DIR

*Default: `WP_CONTENT_DIR . '/vpdb'`*  
*Configuration file: `wp-config.common.php`*

By default, VersionPress saves all its content into the `vpdb` directory under `WP_CONTENT_DIR`. You can change it by setting this constant. `VP_VPDB_DIR` must be under the `VP_PROJECT_ROOT`.



### VP_PROJECT_ROOT

*Default: `ABSPATH`*  
*Configuration file: `wp-config.common.php`*

By default, VersionPress creates the repository (and the `.git` directory) in the `ABSPATH` directory. If you [move WordPress into its own directory](../feature-focus/custom-project-structure#giving-wordpress-its-own-directory), you have to define this constant to point to the original `ABSPATH` location. For example, if you have the site at `/var/www/my-site` and you move WordPress into `/var/www/my-site/wordpress`, the `VP_PROJECT_ROOT` needs to be set to `/var/www/my-site` (where the `.git` directory is).


### VP_VPDB_DIR

[TODO]
