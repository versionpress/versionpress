# Configuration

Some technical aspects of VersionPress can be configured via a 'vpconfig' file which is a NEON file in the plugin root. There is also a set of WP-CLI commands as an alternative to manual editing.

<div class="important">
  <strong>Note</strong>
  <p>You shouldn't typically need to update this configuration. Do it only if you know what you are doing.</p>
</div>

## Config files

The [NEON file format](http://ne-on.org/) is used for configuration. It is pretty simple, similar to YAML. VersionPress looks for two files in the plugin root directory:

 - `vpconfig.defaults.neon`
 - `vpconfig.neon`

VersionPress first looks for a config value in the `vpconfig.neon` file and falls back to the defaults file if it doesn't find it. You can manually copy and paste lines from the defaults file to the vpconfig file to modify some of the behavior.


## WP-CLI commands

You can also use WP-CLI commands to update config values. Use `vp config` command like this:

    wp vp config <option> <value>
    
For example, this is how you would set a custom Git binary:

    wp vp config git-binary custom/path/to/git

This will work if you have [WP-CLI installed](https://github.com/wp-cli/wp-cli/wiki/Alternative-Install-Methods) and VersionPress plugin activated. Run `wp help vp config` to see help.


## Config options


### git-binary

*Default: `git`*

By default, VersionPress calls `git` to run Git commands which means that the binary needs to be in the PATH. If it is not or you need to use a different binary for whatever reason, use this option.

Example:

    git-binary: /path/to/git         # Linux / Mac OS
    git-binary: C:\path\to\git.exe   # On Windows use single backslashes 