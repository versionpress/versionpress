Tests are a large part of the project, both in terms of size (currently about 50% of the codebase) and importance. The test suite is evolving all the time but the basics are relatively stable so let's look at them.


## Overview

In the early days of VersionPress, we didn't have a good way to answer a simple question "does it work at all?". Before every release, we just tried to click as many buttons in the wp-admin as possible and manually checked that the commits look right. It just didn't scale.

Sometime around 1.0, we decided to replace ourselves with **Selenium** and created what is now known as **end2end tests** ([repo link](https://github.com/versionpress/versionpress/tree/master/plugins/versionpress/tests/End2End)). They are slow but very thorough – they execute WordPress actions and check that the Git commits are right and that the database is in sync with the repo. If these tests pass, we're pretty sure VersionPress does its work.

More recently, end2end tests were made more flexible to allow other type of **"workers"** beside Selenium, so for example now instead of clicking in the wp-admin, the tests can call **WP-CLI** commands to do the same actions. In the future, we will likely add **REST API workers** as well.

These end2end tests have the greatest value to us and are run before every release. At the same time, they have two downsides:

1. They are **slow** and can easily take 5-15 minutes, depending on hardware.
2. Some **setup is required** and for example Selenium is quite brittle when it comes to compatible versions of Firefox etc. (see below)

Which is why why have other types of tests as well:

- **Unit tests** cover small units of code. A great example are [tests for IniSerializer](https://github.com/versionpress/versionpress/blob/master/plugins/versionpress/tests/Unit/IniSerializerTest.php), basically describing our core storage format. If there's a bug report, we create a unit test for it first and fix it second.
- **Integration tests** like [StorageTests](https://github.com/versionpress/versionpress/tree/master/plugins/versionpress/tests/StorageTests), [SynchronizerTests](https://github.com/versionpress/versionpress/tree/master/plugins/versionpress/tests/SynchronizerTests) or [GitRepositoryTests](https://github.com/versionpress/versionpress/tree/master/plugins/versionpress/tests/GitRepositoryTests). They touch external systems like the filesystem or the Git repo and are not as fast as unit tests but still much faster than full end2end tests.

We're currently working on a **CI solution** with the goal to run the slow but thorough end2end tests after every push to a feature branch on GitHub. It's not ready yet though so a local setup is required to run the tests. 


## Setup

To be able to run the tests you have to create a **test config file**. You can copy a sample from `plugins/versionpress/tests/test-config.sample.yml` to `test-config.yml` in the same directory. Update these values to match your local environment:

 - `selenium` > `firefox-binary` — path to your Firefox executable (see notes on Selenium below),
 - `common-site-config`,
 - `sites` — here you can specify a list of your test sites (or just simple alter the `vp01`). 

The test config file is generally very important as our WpAutomation [depends on it](./Dev-Setup.md#wpautomation-setup) and the tests use it to couldn't automate WordPress installation to run tests in it.

> <small>More detailed setup instructions follow, if you just want to run the tests skip to [Running tests](#running-tests).</small>

### Selenium setup

Selenium Server is downloaded automatically by the test runner script (`tests/gulpfile.js`) but still requires some local env setup because:

1. It requires **Java**. Make sure you have it installed and in your PATH.
2. Compatibility with Firefox is unfortunately quite tricky. Currently, with Selenium Server 2.47.1 ([see](https://github.com/versionpress/versionpress/blob/ddde14c44752ba678d6db7dde575bd92c1305dda/plugins/versionpress/tests/gulpfile.js#L46)), Firefox 34 works. If we ever upgrade, you'll need to find out which version works :)

To set up a matching version of Firefox:

- On a **Mac**, download Firefox in a matching version and put it somewhere. The path to this Firefox can then be put into `test-config.yml` like this:

        firefox-binary: /Users/johndoe/Path/To/Firefox.app/Contents/MacOS/firefox
        
- On **Windows**, it's best to use [Firefox Portable](http://portableapps.com/apps/internet/firefox_portable). To run it side-by-side with system Firefox, copy `FirefoxPortable.ini` from `Other/Sources` into the root and change `AllowMultipleInstances` to true.

On both systems, Selenium will always create a new Firefox profile so you don't need to worry that it will mess up with your default profile.


### Windows users

The test config sample is "Mac-first" so here's an example of what parts of the config might look like on Windows. Note the forward slashes.


```
selenium:
    firefox-binary: C:/Path/To/FirefoxPortable/App/Firefox/firefox.exe

...

sites:

    vp01:
        host: localhost
        db:
            host: localhost
            dbname: vp01
            user: vp01
            password: vp01
        wp-site:
            path: C:/wamp/www/vp01
            url: http://localhost/vp01
            title: "VP Test @ WampServer"
```

Other than different paths, all should be pretty much the same as on Mac.


### Vagrant

The test config sample contains a couple of [Vagrant](https://www.vagrantup.com/) configs that allow us to execute the whole test suite against various versions of PHP, Git etc. We do not use it much so feel free to skip this section, however, technically, here's the description of how to get it work. 

> **Why we don't run Vagrant tests often:** because they are *very slow*. They run the already slow end2end tests on virtual machines and interact with them via WP-CLI commands tunneled through SSH which, on Windows, is another emulated layer; you get the idea. We plan to replace this with cloud-hosted Docker machines with preset PHP / Git configurations which should be *much* faster.

Here's how to set up and run Vagrant tests:

1.  Install [VirtualBox](https://www.virtualbox.org/ "https://www.virtualbox.org/")
2.  Install [Vagrant](https://www.vagrantup.com/ "https://www.vagrantup.com/")
3.  Install [vagrant-hostupdater](https://github.com/cogitatio/vagrant-hostsupdater "https://github.com/cogitatio/vagrant-hostsupdater") - automatically updates the `hosts` file so that domains like `vagrant-php53.local` work
4.  Make sure `ssh` is in PATH (on Windows, add Git's `bin` directory to PATH)
5.  Run the console as an admin
6.  Go to `versionpress/tests/vagrant`
7.  Run `vagrant up`
    *   This by default runs the `wordpress-php53` config. If you want another one run e.g. `vagrant up wordpress-php55`
8.  Create file `tests/wp-cli.local.yml` - you can copy it from `wp-cli.local.sample.yml` or [this one](https://github.com/xwp/wp-cli-ssh/blob/master/wp-cli.sample.yml)
9.  In the `test-config.yml` file, use one of the Vagrant sites as the `test-site`.
10.  In the `tests` folder, run `gulp run-tests`.
11.  After you're done, run `vagrant halt`.

**Vagrant troubleshooting:**

Sometimes, the web server cannot see the WordPress installation - it just shows the default Apache page "It works". In such case, try to run `vagrant provision` or `vagrant halt` followed by `vagrant up`.



## Running tests

Tests are run by PHPUnit which is automatically downloaded via Composer if you follow the [Dev-Setup steps](./Dev-Setup.md) (there's no need to have PHPUnit installed globally).

To run **all tests**:

1. Go to the `PROJ_DIR/plugins/versionpress/tests` directory.
2. Make sure the `test-config.yml` file is ready
3. Run `gulp run-tests [--force-setup]`.

The gulp task downloads Selenium Server, starts it and runs all the tests.

> :toilet: If you can't get Selenium working, change the `end2end-test-type` in test-config from `selenium` to `wp-cli`. WP-CLI tests are less thorough and don't cover as much as full-fledged Selenium tests but are faster to run and generally much less troublesome to set up.

To **customize** what tests run, you can either edit the `phpunit.xml` file in the `tests` folder or use other method to run the tests, e.g., PhpStorm.


### Run tests from PhpStorm

This is especially useful:

1. For **unit tests**
2. If you want to **debug** some test
3. If you want to run a **specific end2end test**, e.g., `PostsTest`.

Just right-click on desired directory / class (`tests` for all, `tests/Unit` for unit tests etc.) and choose *Run*. (For End2End tests with Selenium worker, you need to start the Selenium server manually first.)


### Logging

The gulp script is set-up to log the results into these locations:

- The console
- phpunit-log.*format*.txt, for example, `phpunit-log.tap.txt`, `phpunit-log.testdox.txt` and possibly others. They are in the `tests` folder. 


## Writing tests

Just a couple of hints / conventions:

- **Tests should be part of a feature**. Algorithmic code like parsing something MUST have unit tests. For larger features we might write end2end tests afterwards, after the feature stabilizes a bit. In such case, there's a new issue for it.
- Test classes end with `Test`, not `Tests`. This is so that PHPUnit can automatically find tests classes.
- For **test method names**, we use convention no. 4 from [this article](https://dzone.com/articles/7-popular-unit-test-naming). See also [generating agile documentation](https://phpunit.de/manual/current/en/other-uses-for-tests.html#other-uses-for-tests.agile-documentation).
    - We prefer marking tests with the `@test` annotation, not via the `testXyz` prefix.


## Resources

Random collection of possibly useful resources:


- [Automated Testing in WordPress presentation](http://www.slideshare.net/ptahdunbar/automated-testing-in-wordpress-really), interesting from slide 80 above
- http://wptest.io/
