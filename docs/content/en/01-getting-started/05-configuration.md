# Configuration

Some technical aspects of VersionPress can be configured via "vpconfig" files which are **.neon* files in the plugin root (the [NEON file format](http://ne-on.org/) is pretty simple, similar to YAML). There is also a set of WP-CLI commands as an alternative to manual editing.

<div class="important">
  <strong>Note</strong>
  <p>You shouldn't typically need to update this configuration; only do it if you know what you are doing. Standard user settings can be accessed via WP admin pages as usual.</p>
</div>

## Config files

VersionPress looks for two files in the plugin root directory:

 - `vpconfig.defaults.neon`
 - `vpconfig.neon`

VersionPress first looks for a config value in the `vpconfig.neon` file and falls back to `vpconfig.defaults.neon` if it doesn't find one. You can manually copy and paste lines from the defaults file to the vpconfig file to modify some of the behavior.    


## WP-CLI commands

You can also utilize WP-CLI commands to avoid hand-editing files. For example, this is how to set a custom path to Git binary:

    wp vp config git-binary custom/path/to/git

This will work if you have [WP-CLI installed](https://github.com/wp-cli/wp-cli/wiki/Alternative-Install-Methods) and VersionPress plugin activated. Use `wp help vp config` to print out the help.


## Config options


### git-binary

By default, VersionPress just runs `git` when it needs to execute Git commands. That means that the binary will be sought for in the PATH (note that the web server might run under a different user account so seeing Git in your path doesn't necessarily mean that the web server will see it as well). If you encounter problems or need to use other version of Git than what is installed in web server's PATH, you can use this option.

Example:

    git-binary: /path/to/git         # Linux / Mac OS
    git-binary: C:\path\to\git.exe   # Windows - note the single backslashes 