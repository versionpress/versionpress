---
since: 2.0
---

# WP-CLI Commands #

For advanced usage, VersionPress comes bundled with several [WP-CLI](http://wp-cli.org/) commands. BTW, we absolutely love the WP-CLI project, what a great effort by the WordPress community!


## Installing WP-CLI ##

WP-CLI runs best on UNIX-like systems (Linux, Mac OS X, Cygwin..) but works almost as good on Windows systems.

Here is an [overview of all the supported installation methods](https://github.com/wp-cli/wp-cli/wiki/Alternative-Install-Methods), we'll just assume that in the end, the `wp` command is available in the console and executing `wp --info` returns some output.


## Working with VersionPress commands ##

When VersionPress is installed **and fully activated**, you can `cd` into the root of the site and execute

   `wp help vp`

to see the list of all VersionPress commands available.


## Command reference ##

<div class="note">
 
  **Depend on `wp help`**
 
  Use `wp help` as the primary source of information, the command reference below might not be 100% up to date.
 
</div>

<dl>

<dt>vp clone</dt>
<dd>Clones site to a new folder, database and Git branch. See [Cloning a site](../sync/cloning).</dd>

<dt>vp undo &lt;commit></dt>
<dd>Undos a commit</dd>

<dt>vp rollback &lt;commit></dt>
<dd>Reverts the site to a previous state</dd>


</dl>



