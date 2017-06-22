# Dev setup

🚧 This is being updated for Dockerized dev setup 🚧

## Getting started

Install:

- Git 2.10+
- Node.js 8+
- Docker 17+

Then run:

1. `git clone <repo>` && `cd <repo>`
2. `npm install`
3. `docker-compose up`

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
- Stop all Docker services: `Ctrl+C` in the console.
    - `docker-compose down -v && rm -rf ./dev-env/wp` clears up everything if you want to start fresh. Otherwise, both files and database data are persisted.

Next steps:

- [PhpStorm setup](#phpstorm)
- [Debugging](#debugging)
- [Production build](#production-build)

<div id="phpstorm"></div>

## PhpStorm setup

We recommend [PhpStorm](https://www.jetbrains.com/phpstorm/) for VersionPress development.

The initial `npm install` copies `.idea` to `./plugins/versionpress` where most things are already configured, however, some manual steps are still needed.

On the first PhpStorm start, you'll see two prompts:

![image](https://cloud.githubusercontent.com/assets/101152/26286846/c369a5b0-3e6e-11e7-8781-c1a3c8446aa6.png)

**Enable WordPress support** but leave the installation path empty (ignore the warning):

![image](https://cloud.githubusercontent.com/assets/101152/26286883/6d11d22c-3e6f-11e7-94eb-a4c0287fb181.png)

Also initialize the **Composer** support:

![image](https://cloud.githubusercontent.com/assets/101152/26286903/c2d1befc-3e6f-11e7-9296-062fbed20983.png)

For **Code Sniffer** inspections to work, there's a one-time configuration: Go to *Settings* > *Languages & Frameworks* > *PHP* > *Code Sniffer*, select *Local*, click the three dots next to it and provide your full system path to `./vendor/bin/phpcs`. After this is done, PhpStorm will start checking the code style.

> Note: the same checks run on Travis CI once the code is pushed to GitHub so it's useful to have that configured in PhpStorm.


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

## Production build

Run `npm run build`,  it will produce a file like `dist/versionpress-3.0.2.zip`.

The version number is based on the nearest Git tag and can also be something like `3.0.2-27-g0e1ce7f` meaning that the closest tag is `3.0.2`, there have been 27 commits since then and the package was built from `0e1ce7f`. See [`git describe --tags`](https://git-scm.com/docs/git-describe#_examples) for more examples.
