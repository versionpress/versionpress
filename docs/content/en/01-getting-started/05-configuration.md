# Configuration

Some technical aspects of VersionPress can be configured via a `vpconfig.neon` file or an associated WP-CLI command. This page discusses the configuration system and lists all the supported options.

<div class="important">
  <strong>Note</strong>
  <p>You shouldn't typically need to update this configuration. Do it only if you know what you are doing.</p>
</div>


## Configuration system overview

There are two files created during the installation in the `wp-content/plugins/versionpress` directory:

 - `vpconfig.neon`
 - `vpconfig.defaults.neon`

VersionPress first looks for a value in the `vpconfig.neon` file and falls back to the `vpconfig.defaults.neon` file if it doesn't find one. The `vpconfig.neon` file is meant to be edited, the `vpconfig.defaults.neon` file is meant to be read-only.

The files are in the [NEON file format](http://ne-on.org/) which is pretty simple and similar to YAML. You can manually copy and paste lines from the `vpconfig.defaults.neon` file to the `vpconfig.neon` file to change VersionPress configuration.

<div class="note">
  <p>As mentioned above, always modify the `vpconfig.neon` file, not the defaults file.</p>
</div>


### WP-CLI command

As an alternative to manual editing, you can use the **`vp config`** WP-CLI command like this:

    wp vp config <option> <value>
    
    # e.g., set custom Git path:
    wp vp config git-binary /custom/path/to/git

This will work if you have [WP-CLI installed](https://github.com/wp-cli/wp-cli/wiki/Alternative-Install-Methods) and VersionPress plugin activated. Run `wp help vp config` to see the help.


## Config options

This section lists all the supported config options.

### git-binary

*Default: `git`*

By default, VersionPress calls just `git` which leaves the path resolution up to the operating system. That might be problematic on some server configurations which use different `PATH` for different users (the web server user might not be the same user under which you are logged in), there might be some `PATH` caching involved, etc. If VersionPress cannot detect Git for some reason, use this option.

Example:

    # regarding paths, please read notes below!
    
    git-binary: /path/to/git            # Linux / Mac OS
    git-binary: 'C:\path to\git.exe'    # Windows


Important notes about paths:

 - Putting **single quotes around the path makes it safe**, however, it's not strictly necessary if the path is simple. This is usually the case on Linux / MacOS where the path typically looks like `/usr/bin/git` or similar.
 - **On Windows**, the Git binary is usually `C:\Program Files (x86)\Git\bin\git.exe`. Here, **quoting is required** as `(...)` has a special meaning in NEON. Here are examples of valid and invalid paths on Windows:


        # OK:
        git-binary: 'C:\Program Files (x86)\Git\bin\git.exe'  # recommended
        git-binary: 'C:/Program Files (x86)/Git/bin/git.exe'  # forward slashes work as well
        git-binary: "C:/Program Files (x86)/Git/bin/git.exe"  # even with doublequotes
        git-binary: "C:\\Program Files\\Git\\bin\\git.exe"    # double quotes + escaping
        git-binary: C:\Git\git.exe                            # simpler paths without quotes
        git-binary: C:\My Git\git.exe                         # spaces are not a problem
    
        # Will not work:
        git-binary: C:\Program Files (x86)\Git\bin\git.exe    # because of (x86)
        git-binary: "C:\Program Files (x86)\Git\bin\git.exe"  # unescaped backslashes


    You can test whether the path will be parsed correctly in [NEON playground](http://ne-on.org/).