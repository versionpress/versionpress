# Configuration

Some technical aspects of VersionPress can be configured by defining constants in `wp-config.php` or using an associated WP-CLI command.

<div class="important">
  <strong>Note</strong>
  <p>You shouldn't typically need to update the configuration. Do it only if you know what you are doing.</p>
</div>


## Configuration system overview

VersionPress uses two levels of configuration files:

 - `wp-config.php` for environment-specific constants (it's not versioned),
 - `wp-config.common.php` for constants common to all environments (it's part of the Git repository).


It is recommended to use the WP-CLI command, however, it is possible to add/change the constants manually. Just be sure they are in the right file.

### WP-CLI command

As an alternative to manual editing, you can use the **`vp config`** WP-CLI command like this:

    wp vp config <constant> <value>
    
    # e.g., set custom Git path:
    wp vp config VP_GIT_BINARY '/custom/path/to/git'

This will work if you have [WP-CLI installed](https://github.com/wp-cli/wp-cli/wiki/Alternative-Install-Methods) and VersionPress plugin activated. Run `wp help vp config` to see the help.


## Config options

This section lists all the supported constants.

### VP\_GIT\_BINARY

*Default: `git`*  
*Configuration file: `wp-config.php`*

By default, VersionPress calls just `git` which leaves the path resolution up to the operating system. That might be problematic on some server configurations which use different `PATH` for different users (the web server user might not be the same user under which you are logged in), there might be some `PATH` caching involved, etc. If VersionPress cannot detect Git for some reason, use this option.


