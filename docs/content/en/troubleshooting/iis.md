# IIS

Though IIS is not an [officially recommended](https://wordpress.org/about/requirements/) web server, WordPress core has some built-in support for it and VersionPress does too – for example, we generate `web.config` files to protect certain locations from direct access by default.

In theory, IIS should work just fine but in practice, there are often setup issues related to users and permissions. This is what we know so far; if you're an IIS guru and can help us improve this page, please [file an issue on GitHub](https://github.com/versionpress/docs/issues).

## The problem

The core issue on IIS seems to be that the [Symfony\Process](http://symfony.com/doc/current/components/process.html) cannot read the process output. We use the Process component to interact with Git, and even when Git is installed and Symfony can successfully call it, it eventually fails because it cannot read what Git returned.

This seems to be happening for a cascade of reasons:

1. Symfony internally uses [`proc_open()`](http://php.net/manual/en/function.proc-open.php) and for some reason, IIS spawns the processes created by `proc_open()` [under a different user](http://stackoverflow.com/q/33481246/21728) than PHP standard user.
2. Because of a [PHP bug on Windows](https://github.com/symfony/process/blob/319794f611bd8bdefbac72beb3f05e847f8ebc92/Pipes/WindowsPipes.php#L90), Symfony\Process cannot read the process output directly and stores it to some temporary file first. It chooses to store it into the [`sys_get_temp_dir()`](http://php.net/manual/en/function.sys-get-temp-dir.php) directory.
3. If this directory doesn't have write permission for the user used for `proc_open()`, VersionPress cannot read the Git output. It will look this strange on the [system info page](./system-info-page.md):

    ```php
    array (
        'git-binary-as-configured' => 'C:/Program Files (x86)/Git/bin/git.exe',
        'git-available' => true,
        'git-version' => NULL,
        'git-binary-as-called-by-vp' =>'C:/Program Files (x86)/Git/bin/git.exe',
        'git-full-path' => 'C:/Program Files (x86)/Git/bin/git.exe',
        'versionpress-min-required-version' => '1.9',
        'matches-min-required-version' => false
    )
    ```

## Solution

1. Use the [system info page](./system-info-page.md) to determine which user is used for `proc_open()` calls.
2. Give write access to [`sys_get_temp_dir()`](http://php.net/manual/en/function.sys-get-temp-dir.php) to this user.
