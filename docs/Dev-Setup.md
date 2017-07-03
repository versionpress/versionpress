# Dev setup

This will set you up for VersionPress development.

## Introduction

While VersionPress ships as a WordPress plugin, it is a relatively large piece of software where good development tools and workflows are necessary. We try to provide as much as possible out of the box, relying on this general approach:

- You should use **local tools** and their power to **write** code. Specifically, we recommend PhpStorm.
- All **runtime concerns** are handled by **Docker**. For example, you do not need a local WordPress site, it run as a Docker container. Same for testing, etc.

> **Note**: If you still need to use the legacy approach where the entire setup was local, refer to the the `4.0-alpha1` tag of the documents [Dev-Setup.md](https://github.com/versionpress/versionpress/blob/4.0-alpha1/docs/Dev-Setup.md) and [Testing.md](https://github.com/versionpress/versionpress/blob/4.0-alpha1/docs/Testing.md).

## Getting started

Install:

- PHP 5.6+ and Composer
- Git 2.10+
- Node.js 8+
- Docker 17+

Then run:

1. `git clone <repo>` && `cd <repo>`
2. `npm install`
3. `npm start`

You can now log in to a WordPress site at `http://localhost:8088`, username `admin`, password `adminpwd` (all this can be changed via `docker-compose.yml`).

![image](https://cloud.githubusercontent.com/assets/101152/26283542/17fccd8a-3e2b-11e7-9881-a26fbb49d144.png)

This leaves you with this working environment:

- VersionPress source files are mapped to container's `wp-content/plugins/versionpress` so any changes you make locally are immediately reflected in the test site.
    - The React `./frontend` app requires a build, see below.
- Database can be inspected at `http://localhost:8099`, server name: `db`, username: `root`, password: `r00tpwd`.
    - You can also use tools like MySQL Workbench or `mysql` command-line client on port 3399, e.g., `mysql --port=3399 -u root -p`.
- WordPress site is mapped to `./dev-env/wp` where you can inspect the files and Git history using your favorite tools.
- To invoke things like WP-CLI in the context of a test WordPress site, you have these options:
    - SSH into container the container: `docker-compose exec wordpress /bin/bash`
    - Use `docker-compose exec wordpress <command>`, for example:
        - `docker-compose exec wordpress wp option update blogname "Hello"`
        - `docker-compose exec wordpress git log`
- Stop all Docker services: `Ctrl+C` in the console. `npm run cleanup-docker-stack` clears up everything if you want to start fresh. Otherwise, both files and database data are persisted.

Next steps:

- [PhpStorm setup](#phpstorm)
- [Writing code](#writing-code)
- [Debugging](#debugging)
- [Testing](#testing)
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

#### Command line

If you don't need to run or debug tests from PhpStorm, running tests is as simple as:

1. `cd ./plugins/versionpress/tests`
2. `npm run tests`

This runs all the tests in the project (see below for tips how to run a subset of them). The firsts run downloads and builds a lot of stuff, subsequent runs are almost instant.

You can now inspect the results in the console and also, the whole Docker stack is still running so you can e.g. inspect the test site or its database. Run `npm run stop-tests` to shut down the Docker stack or `npm run cleanup-tests` to also remove all the volumes (next start will be completely fresh).

The only local requirement is a free port 80 (end2end tests make the WordPress site available for local inspection).

#### Running tests from PhpStorm

It is often useful to run or debug tests from PhpStorm. There is a one-time setup:

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

The final step is to set up a test framework in _PHP_ > _Test Frameworks_. Don't forget to set the _Default bootstrap file_ to `/opt/project/tests/phpunit-bootstrap.php`.

![image](https://user-images.githubusercontent.com/101152/27797069-900fafce-600c-11e7-9ff9-db2d4507aa89.png)

Now you're ready to run the tests. For example, to run all unit tests, right-click the `Unit` folder and select _Run_:

![image](https://user-images.githubusercontent.com/101152/27797266-48a041fc-600d-11e7-88f0-aa557eb02325.png)

Debugging also works, just select _Debug_ instead of _Run_:

![image](https://user-images.githubusercontent.com/101152/27797346-93e132ca-600d-11e7-8052-9b4790739747.png)

This works equally well other types of tests as well, for example, Selenium tests:

![image](https://user-images.githubusercontent.com/101152/27797533-57f12904-600e-11e7-971b-08fd943aaf7b.png)

#### Tips for running tests

- You can start the `tests` container in an interactive session via `docker-compose run tests /bin/bash`. You can then invoke `phpunit` manually, e.g., `/opt/project/vendor/phpunit/phpunit/phpunit --bootstrap /opt/project/tests/phpunit-bootstrap.php --no-configuration /opt/project/tests/Unit`.
- If you frequently run only a subset of tests from command line, say, `PublicWebTest`, you can create `docker-compose.override.yml` in the `tests` folder with a command inspired by what PhpStorm executes. For example:
    ```yaml
    version: '2'
    services:
      tests:
        command: /opt/project/vendor/phpunit/phpunit/phpunit --bootstrap /opt/project/tests/phpunit-bootstrap.php --no-configuration VersionPress\Tests\Selenium\PublicWebTest /opt/project/tests/Selenium/PublicWebTest.php
    ```

### Unit tests

Unit tests are best suited for small pieces of algorithmic functionality. For example, `IniSerializer` is covered with unit tests extensively.

You can either run unit tests in a dockerized environment as described above or set up a local CLI interpret; it makes the execution a bit faster.

### End2end tests

End2end tests exercise a WordPress site and check that VersionPress creates the right Git commits and that the database is in correct state. These tests are quite heavy and slow to run but if they pass, there's a good chance that VersionPress works correctly. (Before the project had these, long and painful manual testing period was necessary before each release.)

End2end tests use the concept of **workers**: each test itself is implemented once but e.g. how a post is created or a user deleted is up to a specific worker. There are currently two types of workers:

1. **Selenium workers** – simulate real user by clicking in a browser.
2. **WP-CLI workers** – run WP-CLI commands against the test site.

In the future, we might add REST API workers; the idea is to cover all possible interactions with the site as different workers can (and in practice do) produce slightly different results.

Currently, the default worker is WP-CLI (is used when you `npm run tests`) and the only way to switch workers is to update `tests/test-config.yml`, the `end2end-test-type` key, but this will be changing soon as this file is not intended for local changes. In the future, there will be another method to parametrize this, e.g., a command line switch or two sets of test classes.

After you run the tests using one of the methods described above, the Docker Compose stack is left up and running so that you can inspect it:

- You can access the site in your local browser by aliasing a `wordpress` host in your `hosts` file (add a line with `127.0.0.1 wordpress`) and then visiting `http://wordpress/vp01`.
- `docker exec -ti tests_wordpress_1 /bin/bash` to start an interactive session inside the WordPress site container. You can then e.g. run `git log` against the site.
- `docker-compose ps` lists all the running services.
- `docker-compose down` shuts the whole stack down.


<div id="other-tests"></div>

### Other tests

There are also other types of integration tests, e.g., `GitRepositoryTests` or `StorageTests`. These are lighter than End2End tests but still depend on some external subsystem like Git or file system.

You can run these tests individually as per instructions above. 

## Production build

Run `npm run build`, it will produce a file like `dist/versionpress-3.0.2.zip`.

The version number is based on the nearest Git tag and can also be something like `3.0.2-27-g0e1ce7f` meaning that the closest tag is `3.0.2`, there have been 27 commits since then and the package was built from `0e1ce7f`. See [`git describe --tags`](https://git-scm.com/docs/git-describe#_examples) for more examples.

## Docker tips

Here are some tips for working with Docker / Docker Compose:

- Aliasing `docker-compose` to `dc` will save you some typing. (All examples here still use the full variant.)
- You can start the whole stack in the background via `docker-compose up -d`. Then, you would use:
    - `docker-compose logs --tail=10` to display last 10 log messages from each container. Logs can also be followed (similar to `docker-compose up`) by `docker-compose logs -f`.
    - `docker-compose ps` to list the containers.
    - `docker-compose stop` to shut down the stack.
    - `npm run cleanup-docker-stack` to clean up everything.
- Most values in `docker-compose.yml` like environment variables can be changed in `docker-compose.override.yml`.
- Any container from the stack can be started by `docker-compose run <service>`, e.g., `docker-compose run unit-tests`.
    - The session can be made interactive by putting
        ```
        stdin_open: true
        tty: true
        ```
        next to the service. Then, it's possible to do e.g. `docker-compose run unit-tests /bin/bash`. (This unfortunately needs to be done in Compose file, not yet possible on the command line. Tracking issue: https://github.com/docker/compose/issues/363.)
