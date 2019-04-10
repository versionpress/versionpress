# Dev setup

This will set you up for VersionPress development. 👩‍💻 👨‍💻

## Getting started

Our approach is:

- You develop in **local tools** you're comfortable with – PhpStorm, vim, VSCode, ...
- Runtime is handled by **Docker**. You don't need MAMP / XAMPP, local installation of Selenium, etc.
- Common tasks are automated via **npm scripts**, for example, `npm run build`.

If you're not familiar with Docker, this is [great quick start](https://docs.docker.com/get-started/). We also have some [tips for you](#docker-tips).

This software is expected on your machine:

- Git 2.10+
- Node.js 8+, npm 6+
- Docker 18.03+
- PHP 7+ and Composer 1.4+

### Windows users: use Git Bash

Git Bash (part of [Git for Windows](https://gitforwindows.org/)) is required on Windows, please use it instead of `cmd.exe` / PowerShell for both interactive sessions and as your npm script shell:

```
npm config set script-shell "c:\Program Files\git\bin\bash.exe"
```

See also [Windows tips](#windows-tips) below.

## Project initialization

1. `git clone https://github.com/versionpress/versionpress`
2. `cd versionpress`
3. `npm install`

Have a ☕, this will take a while.

> **Tip**: From time to time, it's useful to clean up everything and pull latest Docker images. Run `npm run refresh-dev`.

## Dockerized development environment

To start a development site:

1. Make sure Docker is running and ports 80 and 3306 are free (no local MAMP / XAMPP / MySQL running).
2. Run `npm start`.

This will start a set of Docker containers in the background. When everything boots up, log into the test site at <http://localhost>, install WordPress and activate VersionPress on the _Plugins_ page. You're now all set up! 🎉

![image](https://user-images.githubusercontent.com/101152/40431982-e2884a72-5ea8-11e8-9a07-e9857b5f1b67.png)

Let's explore your development environment:

- VersionPress source files are directly mapped to the site's `wp-content/plugins/versionpress`. Any changes you make locally are immediately live.
- Database can be inspected using [Adminer](https://www.adminer.org/) at <http://localhost:8099>, server name `mysql`, login `root` / `r00tpwd`. You can also use tools like MySQL Workbench or `mysql` command-line client, e.g., `mysql -u root -p`.
- WordPress root is mapped to `./dev-env/wp`. You can use your local Git client to inspect the site's history there.
- To invoke WP-CLI or Git commands in the site, create a terminal session via `docker-compose exec wordpress /bin/bash` or invoke the command directly, e.g., `docker-compose exec wordpress git log` or `docker-compose exec wordpress wp option update blogname "Hello"`.

Some useful tips for managing your Docker environment:

- `docker-compose ps` lists running containers
- `docker-compose logs -f` displays live logs
- `docker-compose logs wordpress` displays logs of a single service
- `docker stats` show live CPU / memory usage
- Aliasing `docker-compose` to `dc` will save you some typing.
- Values in `docker-compose.yml` can be customized via `docker-compose.override.yml`.

Run `npm stop` to stop the development environment. Run `npm run stop-and-cleanup` to also clean up WordPress files and MySQL database for a fresh start next time.

## Plugin development

VersionPress consists of PHP code implementing the core versioning logic and a React frontend. This section is about the former, the latter is described in [Frontend development](#frontend-development) below.

### PhpStorm setup

For PHP development, we recommend [PhpStorm](https://www.jetbrains.com/phpstorm/) and ship project files for it. The steps here have been tested in PhpStorm **2018.1**.

Run `npm run init-phpstorm`. This copies `.idea` to `plugins/versionpress`.

Open the `plugins/versionpress` project in PhpStorm. On the first start, you'll see two prompts:

![image](https://user-images.githubusercontent.com/101152/40103254-1e1bb46e-58ed-11e8-8fd3-42c47a504fa7.png)

**Enable WordPress support** but leave the installation path empty (ignore the warning):

![image](https://user-images.githubusercontent.com/101152/40103297-4420c96a-58ed-11e8-9bd7-8e36c8851a45.png)

Enable **Composer sync**.

For **Code Sniffer inspections** to work, there's a one-time configuration: Go to *Settings* > *Languages & Frameworks* > *PHP* > *Code Sniffer*, select *Local*, click the three dots next to it and provide your full system path to `./vendor/bin/phpcs`.

> **Note**: Most VersionPress code uses the [PSR-2](http://www.php-fig.org/psr/psr-2/) coding standard with only the parts directly interacting with WordPress using WordPress conventions. For example, global functions are defined as `vp_register_hooks()`, not `registerHooks()`.

It is also useful to **install the [EditorConfig](https://plugins.jetbrains.com/plugin/7294?pr=phpStorm) extension**, VersionPress ships with some basic formatting rules.

### Writing code

Please refer to the [Contributing code](https://github.com/versionpress/versionpress/blob/master/CONTRIBUTING.md#contributing-code) section in `CONTRIBUTING.md`.

### Debugging

The development containers have [Xdebug](https://xdebug.org/) installed and configured. Here is how to make debugging work in PhpStorm; the [Debugging tests](testing.md#starting-debugging-session-from-command-line) section gives an example of how to make debugging work in VSCode.

Start the Docker stack with `npm start`.

In PhpStorm, go to _Settings_ > _Languages & Frameworks_ > _PHP_ > _Servers_ and check the path mappings of the pre-configured _VersionPress-dev_ server. Specifically, update the WordPress mapping which PhpStorm does not persist automatically:

![image](https://user-images.githubusercontent.com/101152/40432492-1f2f7530-5eaa-11e8-91f2-8b3794f97b74.png)

The two mappings should be:

- `<your local path>/plugins/versionpress` -> `/var/www/html/wp-content/plugins/versionpress`
- `<your local path>/ext-libs/wordpress` -> `/var/www/html`

The default zero configuration settings in _Settings_ > _Languages & Frameworks_ > _PHP_ > _Debug_ should be fine:

![image](https://cloud.githubusercontent.com/assets/101152/26285067/34fefbd4-3e48-11e7-8f11-544507a1c5f7.png)

Enable debugging in the browser, most commonly using a [browser extension or a bookmarklet](https://confluence.jetbrains.com/display/PhpStorm/Browser+Debugging+Extensions):

![image](https://cloud.githubusercontent.com/assets/101152/26764669/7f3e4dc0-496b-11e7-9dc2-10351d6378bc.png)

Place a breakpoint somewhere, e.g., in the main `versionpress.php` file, and start listening for debug connections in PhpStorm.

Reload a page in your browser. Debugging should now work:

![image](https://user-images.githubusercontent.com/101152/40105051-2cdd1524-58f2-11e8-8880-d50d70d4195f.png)

After you're done with debugging, run `npm stop` or `npm run stop-and-cleanup`.

## Frontend development

VersionPress uses a JavaScript frontend implemented as a React app in the `./frontend` folder.

### PhpStorm / WebStorm setup

1. Run `npm run init-phpstorm` if you haven't done that already.
2. Open the `frontend` project in PhpStorm.
3. Answer "No" to *Compile TypeScript to JavaScript?* prompt.

Linting task is set up for the frontend project. Run `npm run lint` in the `frontend` directory.

### Running frontend separately

For pure frontend development, it's more convenient to run it outside of the WordPress administration. Let's assume you run the frontend against the default Docker site.

1. Make sure that the site is running and that VersionPress is activated in it. You should be able to visit `http://localhost` in the browser and the `frontend/src/config/config.local.ts` should contain this URL as API root.
2. In your test WordPress site, put this to `wp-config.php` (the file should be editable at `./dev-env/wp/wp-config.php`):

    ```
    define('VERSIONPRESS_REQUIRE_API_AUTH', false);
    ```

3. Run `npm start` in the `frontend` directory.

This launches [webpack dev server](https://webpack.js.org/configuration/dev-server/) at <http://localhost:8888>:

![image](https://cloud.githubusercontent.com/assets/101152/26268495/c4738ff0-3cef-11e7-90ce-b807cc085865.png)

Source code edits will be automatically reflected in the browser.

## Testing

See [Testing](testing.md).

## Production build

Run `npm run build`, it will produce a file like `dist/versionpress-3.0.2.zip`.

The version number is based on the nearest Git tag and can also be something like `3.0.2-27-g0e1ce7f` meaning that the closest tag is `3.0.2`, there have been 27 commits since then and the package was built from `0e1ce7f`. See [`git describe --tags`](https://git-scm.com/docs/git-describe#_examples) for more examples.

## Windows tips

### Git Bash

As noted in [Getting started](#getting-started), we only support Git Bash on Windows, a shell that comes with [Git for Windows](https://gitforwindows.org/). `cmd.exe` or PowerShell will not work as we use Linux-style syntax (single quotes, setting environment variables, etc.) and tools like `curl` or `rm -rf` in scripts.

Git Bash is generally an awesome shell, the only problems you might encounter are related to paths. For example, Docker messes with them and when you try to run `docker run --rm -it ubuntu /bin/bash`, you'll see an error like `C:/Program Files/Git/usr/bin/bash.exe: no such file or directory`. Docker prepends `C:/Program Files/Git` for some reason but you can [use this workaround](https://gist.github.com/borekb/cb1536a3685ca6fc0ad9a028e6a959e3) or use double slash like `//bin/bash`.

### Docker Desktop vs. Docker Toolbox

If you can, use [Docker Desktop](https://www.docker.com/products/docker-desktop), not [Docker Toolbox](https://docs.docker.com/toolbox/toolbox_install_windows/). The experience will be smoother (for example, ports are forwarded by default) and also the performance is much better due to a more modern virtualization technology.

!!! important "Performance is important"
    You'll notice a big difference between Docker Desktop and Docker Toolbox, for example, [tests](testing.md) run 3-4× slower in Toolbox (10 vs. 40 minutes). You might also run into various timeouts, for example, `wp_remote_get()` has a default timeout of 5 seconds which might not be enough.

    Note that it's generally hard to improve the performance of Toolbox, even if you give the virtual machine more CPUs and RAM, see [these results for tests](https://github.com/versionpress/versionpress/pull/1401#issuecomment-476004709).

If you need to use Docker Toolbox because you have an older machine or OS, brace yourself and follow these steps:

- [Enable port forwarding in VirtualBox](https://stackoverflow.com/questions/42866013/docker-toolbox-localhost-not-working/45822356#45822356) for ports 80, 3306 and 8099.
- If you have the repo checked out in a folder _not_ under `C:\Users\youruser`, add it as a shared folder in VirtualBox settings. For example, add a share where _Folder Path_ is `C:\Projects`, _Folder Name_ is `c/Projects`, check both "Auto-mount" and "Make Permanent" and restart the VM. [Details](https://stackoverflow.com/a/32030385).
- Feel free to use the default Docker Quickstart Terminal: it uses Git Bash which is good.

### Disable antivirus software

You might want to disable your antivirus software when working with Docker. Recommendations differ between version, please look it up.

## Developing the dev setup

Meta! If you're working on updating the dev setup (this document, Docker images, etc.), here are some tips for you.

### npm scripts

Simpler tasks are scripted directly in `package.json`, more complex ones in the `./scripts` folder. See its README for more info.

### Building and pushing images

We're keeping our images close to two [official ones](https://hub.docker.com/_/wordpress/), [`wordpress:php7.2-apache`](https://github.com/docker-library/wordpress/blob/master/php7.2/apache/Dockerfile) and [`wordpress:cli`](https://github.com/docker-library/wordpress/blob/master/php7.2/cli/Dockerfile).

The only goal of our images is to be close to the official project and have the right environment in it, e.g., the PHP version. We don't care that much about specific WordPress versions (WordPress is often installed dynamically anyway, based on `test-config.yml`) so we only use "vague" tags like `php7.2-apache` or `cli`.

To build and push tags to Docker Hub:

1. `npm run build-images`
2. `docker login`
3. `npm run push-images`

You can get Docker Hub digests by running:

```
$ npm run get-image-digests

cli sha256:11c49ba4d7198c17660f30e8db4d00ca356b1c4414f338076bf99ab4dd295184
php7.2-apache sha256:39ed34f84a5ccf8ab47eb1db4041c226ffe6f874127ead4c26f0b607457b7377
```

### Links to older documents

Legacy approach is documented at the `4.0-alpha1` tag:

- [Dev-Setup.md](https://github.com/versionpress/versionpress/blob/4.0-alpha1/docs/Dev-Setup.md)
- [Testing.md](https://github.com/versionpress/versionpress/blob/4.0-alpha1/docs/Testing.md)
