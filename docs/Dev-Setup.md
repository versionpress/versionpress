# Dev setup

This will set you up for VersionPress development. 👩‍💻 👨‍💻

## Getting started

Since VersionPress 4.0, our approach is:

- You develop in **local tools** you're comfortable with – PhpStorm, vim, VSCode, etc.
- Runtime is handled by **Docker**. You don't need to have MAMP/XAMPP, set up WordPress, Java, Selenium etc.
- Common tasks are easy to do via **npm scripts**. For example, to run tests you'll just run `npm run tests`.

You can familiarize yourself with Docker in [this great quick start](https://docs.docker.com/get-started/) and we also have some [tips](#docker-tips) for you.

This software is expected on your machine:

- Git 2.10+
- Node.js 8+, npm 6+
- Docker 18+
- PHP 7+ and Composer 1.4+

### Windows users: use Git Bash

The dev setup is actively tested on Windows but you have to use Git Bash (or WSL) for all commands to work. We currently don't have enough capacity to port the dev setup to `cmd.exe`, sorry.

Please see [Windows tips](#windows-tips) below.

## Project checkout

Then clone a repo and install the dev dependencies:

1. `git clone https://github.com/versionpress/versionpress`
2. `cd versionpress`
3. `npm install`

Have a ☕ as this will take a while, initially.

## Exploring dockerized environment

For regular development, you'll want to have a test WordPress site which is provided for you – you don't need to set up MAMP or XAMPP or anything like that.

After you have started Docker on your machine, run this:

1. `git clone <repo>` && `cd <repo>`
2. `npm install`
3. `npm start`

This starts a set of Docker containers in the background, you can view the progress by running `docker-compose logs -f`. When everything boots up, log into the test site at `http://localhost:8088`, install WordPress and activate VersionPress on the _Plugins_ page. You're now all set up! 🎉

![image](https://cloud.githubusercontent.com/assets/101152/26283542/17fccd8a-3e2b-11e7-9881-a26fbb49d144.png)

Explore your development environment:

- VersionPress source files are directly mapped to the site's `wp-content/plugins/versionpress` so any changes you make locally are immediately live.
- Database can be inspected using [Adminer](https://www.adminer.org/) at `http://localhost:8099`, server name: `db`, username: `root`, password: `r00tpwd`.
    - You can also use tools like MySQL Workbench or `mysql` command-line client on port 3399, e.g., `mysql --port=3399 -u root -p`.
- WordPress site's web root is mapped to `./dev-env/wp` so you can e.g. use your local Git client to inspect the history.
- To invoke things like WP-CLI in the context of a test WordPress site, you have these options:
    - SSH into container the container: `docker-compose exec wordpress /bin/bash`
    - Use `docker-compose exec wordpress <command>`, for example:
        - `docker-compose exec wordpress wp option update blogname "Hello"`
        - `docker-compose exec wordpress git log`

Some useful tips for managing your Dockerized environment:

- `docker-compose ps` lists running containers.
- `docker-compose logs -f` displays live logs.

To stop your environment, run `npm run stop`. To also delete WordPress data so that next start is fresh, run `npm run stop-and-cleanup`.

See also [Docker tips](#docker-tips) section below.

Next steps:

- [PhpStorm setup](#phpstorm)
- [Writing code](#writing-code)
- [Debugging](#debugging)
- [Testing](#testing)
- [Frontend development](#frontend-development)
- [Production build](#production-build)
- [Docker tips](#docker-tips)

<div id="phpstorm"></div>

## PhpStorm setup

We recommend [PhpStorm](https://www.jetbrains.com/phpstorm/) for VersionPress development. Version **2017.2** is necessary for Docker Compose workflows below.

First, **run `npm run init-phpstorm`**. This copies `.idea` to `./plugins/versionpress` where most things are already preconfigured for you.

Then, open the `./plugins/versionpress` project in PhpStorm. On the first start, you'll see two prompts:

![image](https://cloud.githubusercontent.com/assets/101152/26286846/c369a5b0-3e6e-11e7-8781-c1a3c8446aa6.png)

**Enable WordPress support** but leave the installation path empty (ignore the warning):

![image](https://cloud.githubusercontent.com/assets/101152/26286883/6d11d22c-3e6f-11e7-94eb-a4c0287fb181.png)

Also initialize the **Composer** support:

![image](https://cloud.githubusercontent.com/assets/101152/26286903/c2d1befc-3e6f-11e7-9296-062fbed20983.png)

For **Code Sniffer** inspections to work, there's a one-time configuration: Go to *Settings* > *Languages & Frameworks* > *PHP* > *Code Sniffer*, select *Local*, click the three dots next to it and provide your full system path to `./vendor/bin/phpcs`. After this is done, PhpStorm will start checking the code style.

> **Note**: Most VersionPress code uses the [PSR-2](http://www.php-fig.org/psr/psr-2/) coding standard with only the parts directly interacting with WordPress might use WordPress-like conventions, e.g., global functions are defined as `vp_register_hooks()`, not `registerHooks()`.

It is also useful to **install the [EditorConfig](https://plugins.jetbrains.com/plugin/7294?pr=phpStorm) extension**, VersionPress ships with some basic formatting rules in `.editorconfig`.

## Writing code

Please refer to the [Contributing code](https://github.com/versionpress/versionpress/blob/master/CONTRIBUTING.md#contributing-code) section in `CONTRIBUTING.md`.

## Debugging

> **Note**: VersionPress consists of core PHP code plus React app for the frontend. This section is about PHP debugging only.

The development environment is preconfigured with [Xdebug](https://xdebug.org/). Here's an example setup:

1. Create a `docker-compose.override.yml` file next to `docker-compose.yml` (it's in the repo root, not in `./plugins/versionpress`). You can copy it from `docker-compose.override.example.yml`.
2. Put your computer IP address there as seen on the local network, e.g., `192.168.1.2`. Tools like `ipconfig` or `ifconfig` will show that.
3. Start the Docker stack: `docker-compose up`.
4. In PhpStorm, go to Settings > Languages & Frameworks > PHP > Servers and create a server with two file mappings:<br><br>![image](https://cloud.githubusercontent.com/assets/101152/26285020/999202ea-3e47-11e7-8859-c792ca0d7d36.png)<br><br>
    - `<project root>/plugins/versionpress` -> `/var/www/html/wp-content/plugins/versionpress`
    - `<project root>/ext-libs/wordpress` -> `/var/www/html`
5. The default zero configuration settings in Settings > Languages & Frameworks > PHP > Debug should be fine: <br><br>![image](https://cloud.githubusercontent.com/assets/101152/26285067/34fefbd4-3e48-11e7-8f11-544507a1c5f7.png)<br><br>
6. Enable debugging in the browser, most commonly using a [browser extension or a bookmarklet](https://confluence.jetbrains.com/display/PhpStorm/Browser+Debugging+Extensions): <br><br> ![image](https://cloud.githubusercontent.com/assets/101152/26764669/7f3e4dc0-496b-11e7-9dc2-10351d6378bc.png)
7. Place a breakpoint somewhere and start listening for debug connections ![image](https://cloud.githubusercontent.com/assets/101152/26285076/5b9b2ca4-3e48-11e7-8ea3-280f9027831a.png)

Debugging should now work:

![image](https://cloud.githubusercontent.com/assets/101152/26285090/bb8aa432-3e48-11e7-973a-944abfe0039e.png)

## Testing

Tests are a significant part of VersionPress core, currently about 60% of the codebase. They live in `./plugins/versionpress/tests` and there are several types of them, from unit tests to full end2end tests. They all run in a dockerized test environment.

> **Note**: the `./frontend` app has its own tests, this section is about core VersionPress tests (PHP code) only.

In this section:

- [Dockerized testing environment](#dockerized-tests)
- [Unit tests](#unit-tests)
- [End2end tests](#end2end-tests)
- [Other tests](#other-tests)

<div id="dockerized-tests"></div>

### Dockerized testing environment

Docker greatly helps with running tests: it requires almost no local setup and produces consistent results across platforms.

#### Running tests from command line

If you don't need to run or debug tests from PhpStorm, running tests is as simple as:

1. Make sure you have Docker up and running.
2. `npm run tests:unit` or `npm run tests:full`.
3. If you've run full tests, stop the Docker stack with `npm run stop` or `npm run stop-and-cleanup` after you've explored the test WordPress site and no longer need it. 

> **Note**: Full tests are _slow_ to start (can take up to a couple of minutes to even start producing output) and _slow_ to run (can take over 30 minutes to complete) as they explore every corner of WordPress. See [end2end tests](#end2end-tests) for more. 🐌

If you want to further customize which tests run, use standard PHPUnit approaches like providing your own `phpunit.xml` or customizing via command-line parameters. Some examples:

```sh
# Pick a test suite from the default phpunit.xml:
docker-compose run --rm tests ../vendor/bin/phpunit -c phpunit.xml --testsuite Unit

# Create your own phpunit.override.xml (gitignored), customize and then:
docker-compose run --rm tests ../vendor/bin/phpunit -c phpunit.override.xml --color

# PhpStorm-like invocation (copy/pasted from its console):
docker-compose run --rm tests ../vendor/bin/phpunit --bootstrap /opt/project/tests/phpunit-bootstrap.php --no-configuration /opt/project/tests/Unit
```

One thing to understand here is that there are two Docker Compose services:

- Use `docker-compose run --rm tests` to run tests that don't need to boot up a working WordPress site, like unit tests.
- Use `docker-compose run --rm tests-with-wordpress` for full integration tests.

**Output of tests** is written in the testdox format to container's `/opt/logs` which is made available to you in your local folder `./dev-env/test-logs`. If you want to logs in [another format supported by PHPUnit](http://phpunit.readthedocs.io/en/7.1/textui.html#command-line-options), run tests manually like this:

```
docker-compose run --rm tests ../vendor/bin/phpunit -c phpunit.xml --log-junit /opt/logs/vp-tests.log
```

After the tests are run, the whole Docker stack is kept up and running so that you can **inspect the test WordPress site**, its database, etc. The [end2end tests](#end2end-tests) section provides more info on this.

Run `npm run stop` to **shut down the Docker stack** or `npm run stop-and-cleanup` to also remove the volumes and start fresh the next time.

<div id="running-tests-from-phpstorm"></div>

#### Running tests from PhpStorm

It is often useful to run or debug tests from PhpStorm. Again, version **2017.2** or newer is required as the earlier versions didn't support Docker Compose. There is a one-time setup to go through:

First, if you're using _Docker for Mac_ or _Docker for Windows_, expose a daemon in Docker settings:

![image](https://user-images.githubusercontent.com/101152/27441580-43a964c8-576e-11e7-9912-1be811f73c4b.png)

In PhpStorm, create a new Docker environment in _Build, Execution, Deployment_ > _Docker_:

![image](https://user-images.githubusercontent.com/101152/27441828-ec760098-576e-11e7-9251-670204bf2643.png)

In the Docker panel, you should now be able to connect:

![image](https://user-images.githubusercontent.com/101152/27441986-508eb2e6-576f-11e7-83b6-20e9b6944619.png)

Next, define a remote interpreter. Make sure you have the **PHP Docker** plugin enabled and go to *Settings* > *Languages & Frameworks* > *PHP*. Add a new interpreter there:

![image](https://user-images.githubusercontent.com/101152/27442419-6e177932-5770-11e7-9d28-dfc219a41fcd.png)

![image](https://user-images.githubusercontent.com/101152/27796438-3fec98e2-600a-11e7-9e9b-f6276ddb0a63.png)

If this doesn't go smoothly, try unchecking the _Include parent environment variables_ checkbox in the _Environment variables_ field:

![image](https://user-images.githubusercontent.com/101152/27796503-81cff2f4-600a-11e7-8cfb-96661f0281a9.png)

Select this CLI interpreter as the main one for the project and define two path mappings:

![image](https://user-images.githubusercontent.com/101152/27796964-2834b8c2-600c-11e7-8a0a-8d7ad43e1471.png)

The final step is to set up a test framework in _PHP_ > _Test Frameworks_. Add a new _PHPUnit by Remote Interpreter_:

![image](https://user-images.githubusercontent.com/101152/27797069-900fafce-600c-11e7-9ff9-db2d4507aa89.png)

Don't forget to set the _Default bootstrap file_ to `/opt/project/tests/phpunit-bootstrap.php`.

Now you're ready to run the tests. For example, to run all unit tests, right-click the `Unit` folder and select _Run_:

![image](https://user-images.githubusercontent.com/101152/27797266-48a041fc-600d-11e7-88f0-aa557eb02325.png)

Debugging also works, just select _Debug_ instead of _Run_:

![image](https://user-images.githubusercontent.com/101152/27797346-93e132ca-600d-11e7-8052-9b4790739747.png)

This works equally well other types of tests as well, for example, Selenium tests:

![image](https://user-images.githubusercontent.com/101152/27797533-57f12904-600e-11e7-971b-08fd943aaf7b.png)

### Unit tests

Unit tests are best suited for small pieces of algorithmic functionality. For example, `IniSerializer` is covered with unit tests extensively.

You can either run unit tests in a dockerized environment as described above or set up a local CLI interpret which makes the execution a bit faster (all unit tests run in-memory).

### End2end tests

End2end tests exercise a WordPress site and check that VersionPress creates the right Git commits, that the database is in correct state, etc. These tests are quite heavy and slow to run but if they pass, there's a good chance that VersionPress works correctly. (Before the project had these, long and painful manual testing period was necessary before each release.)

End2end tests use the concept of **workers**: each test itself is implemented once but e.g. how a post is created or a user deleted is up to a specific worker. There are currently two types of workers:

1. **Selenium workers** – simulate real user by clicking in a browser.
2. **WP-CLI workers** – run WP-CLI commands against the test site.

In the future, we might add REST API workers; the idea is to cover all possible interactions with the site as different workers can (and in practice do) produce slightly different results.

Currently, the default worker is WP-CLI (is used when you `npm run tests`) and the only way to switch workers is to update `tests/test-config.yml`, the `end2end-test-type` key, but this will be changing soon as this file is not intended for local changes. In the future, there will be another method to parametrize this, e.g., a command line switch or two sets of test classes.

After you run the tests using one of the methods described above, the Docker Compose stack is left up and running so that you can inspect it:

- You can access the **test WordPress site** on **port 80** in your local browser by aliasing a `wordpress` host in your `hosts` file (add a line with `127.0.0.1 wordpress`) and then visiting `http://wordpress/vp01`.
- You can start a **Bash session** in the WordPress container by running `docker exec -ti tests_wordpress_1 /bin/bash`. You can then e.g. inspect Git history via `git log`, etc.
- The **database** is available on the standard **port 3306**, you can connect to it e.g. by `mysql -u root -p`.
- **Adminer** is available on **port 8099** after you run `docker-compose run -d --service-ports adminer`.
- `docker-compose ps` lists all the running services.
- `docker-compose down [-v]` shuts down the whole stack (there are npm scripts for that too, see above).

<div id="other-tests"></div>

### Other tests

The project has these other types of tests (folders in the `./plugins/versionpress/tests` folder and also test suite names in `phpunit.xml` so that you can run them using `--testsuite <SuiteName>`):

- `GitRepositoryTests` – test Git repository manipulation in `GitRepository`.
- `SynchronizerTests` – these are quite slow and test that given some INI files on disk, the database is in a correct state after synchronization runs.
- `StorageTests` – test that entities are stored correctly as INI files.
- `LoadTests` – they are run together with other tests but with very few iterations; manually update their source files and execute them separately to properly exercise them.
- `Selenium` – a bit like end2end tests but for rarer cases, like VersionPress not being activated yet.
- `Workflow` – exercise cloning and merging between environments.

## Frontend development

VersionPress uses a JavaScript frontend implemented as a React app in the `./frontend` folder.

### PhpStorm / WebStorm setup

1. Run `npm run init-phpstorm` if you haven't done that already.
2. Open the `frontend` project in PhpStorm.
3. Answer "No" to *Compile TypeScript to JavaScript?* prompt.

Linting task is set up for the frontend project. Run `npm run lint` in the `frontend` directory.

### Running frontend separately

For pure frontend development, it's more convenient to run it outside of the WordPress administration. Let's assume you run the frontend against the default Docker site.

1. Make sure that the site is running and that VersionPress is activated in it. You should be able to visit `http://localhost:8088` in the browser and the `frontend/src/config/config.local.ts` should contain this URL as API root.
2. In your test WordPress site, put this to `wp-config.php` (the file should be editable at `./dev-env/wp/wp-config.php`):
    ```
    define('VERSIONPRESS_REQUIRE_API_AUTH', false);
    ```
3. Run `npm start` in the `frontend` directory.

This launches [webpack dev server](https://webpack.js.org/configuration/dev-server/) at <http://localhost:8888>:

![image](https://cloud.githubusercontent.com/assets/101152/26268495/c4738ff0-3cef-11e7-90ce-b807cc085865.png)

Source code edits will be automatically reflected in the browser.

## Production build

Run `npm run build`, it will produce a file like `dist/versionpress-3.0.2.zip`.

The version number is based on the nearest Git tag and can also be something like `3.0.2-27-g0e1ce7f` meaning that the closest tag is `3.0.2`, there have been 27 commits since then and the package was built from `0e1ce7f`. See [`git describe --tags`](https://git-scm.com/docs/git-describe#_examples) for more examples.

## Developing the dev setup

Meta! If you're working on updating the dev setup (this document, Docker images, etc.), here are some tips for you.

### Building and pushing images

1. `npm run build-images`
2. `docker login`
3. `docker push versionpress/wordpress` / `versionpress/wordpress:cli`

## Docker tips

Here are some tips for working with Docker / Docker Compose:

- Aliasing `docker-compose` to `dc` will save you some typing.
- Inspect `tests/package.json` to see which Docker Compose commands run in the background.
- You can rebuild all images with `npm run rebuild-images`, e.g., to get a newer WordPress or WP-CLI release.
- Most values in `docker-compose.yml` can be customized via `docker-compose.override.yml`.
- Any container from the stack can be started in an interactive session by adding this to `docker-compose.yml`:
    ```
    stdin_open: true
    tty: true
    ```
    Then, it's possible to do e.g. `docker-compose run --rm tests sh`.

## Windows tips

### Git Bash

As noted in the _Getting started_ section, we currently only support Git Bash (or WSL) on Windows, not `cmd.exe`. This simplifies many scripts and instructions as macOS, Linux and Windows can be treated basically the same.

Git Bash comes with [Git for Windows](https://gitforwindows.org/) and after you get used to paths like `/c/Users/You/versionpress` instead of `C:\Users\You\versionpress`, you'll love it, we guarantee :)

The only problematic issue is that Docker messes with paths and for example, trying to run `docker run --rm -it ubuntu /bin/bash`, you'll see an error like `C:/Program Files/Git/usr/bin/bash.exe: no such file or directory` – Docker will try to prepend `C:/Program Files/Git` for some reason. [Use this workaround](https://gist.github.com/borekb/cb1536a3685ca6fc0ad9a028e6a959e3) and you'll be fine.

### Docker for Windows

If you can, use [Docker for Windows](https://www.docker.com/docker-windows), not [Docker Toolbox](https://docs.docker.com/toolbox/toolbox_install_windows/). The experience will be generally smoother.

### Disable antivirus software

You might want to disable your antivirus software when working with Docker. Recommendations differ between version, please look it up.
