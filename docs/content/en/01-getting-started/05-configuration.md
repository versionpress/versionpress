# Configuration

Some technical aspects of VersionPress can be configured via a `vpconfig.neon` file or, alternatively, a set of WP-CLI commands. This topic discusses the configuration system and lists all the supported options.

<div class="important">
  <strong>Note</strong>
  <p>You shouldn't typically need to update this configuration. Do it only if you know what you are doing.</p>
</div>

## Configuration system overview

VersionPress looks for two files in its directory (`wp-content/plugins/versionpress`):

 - `vpconfig.neon`
 - `vpconfig.defaults.neon`

VersionPress first looks for a value in the `vpconfig.neon` file and falls back to the `vpconfig.defaults.neon` file if it doesn't find it. The `vpconfig.neon` file is meant to be edited, the `vpconfig.defaults.neon` file is meant to be left untouched.

The files are in the [NEON file format](http://ne-on.org/) which is pretty simple and similar to YAML. You can manually copy and paste lines from the `vpconfig.defaults.neon` file to the `vpconfig.neon` file to change VersionPress configuration.

<div class="warning">
  <p>As mentioned above, always modify the `vpconfig.neon` file and leave the defaults file untouched.</p>
</div>


### WP-CLI command

As an alternative, you can use the **`vp config`** WP-CLI command like this:

    wp vp config <option> <value>
    
    # e.g., set custom Git path:
    wp vp config git-binary custom/path/to/git

This will work if you have [WP-CLI installed](https://github.com/wp-cli/wp-cli/wiki/Alternative-Install-Methods) and VersionPress plugin activated. Run `wp help vp config` to see the help.


## Config options

This section lists all the supported config options.

### git-binary

*Default: `git`*

By default, VersionPress calls just `git` which leaves the path resolution up to the operating system. If you want to use specific Git binary, or have trouble configuring `PATH` for the web server user, use this option.

Example:

    git-binary: /path/to/git         # Linux / Mac OS
    git-binary: C:\path\to\git.exe   # On Windows, use single backslashes 