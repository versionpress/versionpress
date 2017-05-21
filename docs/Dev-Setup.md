# Dev setup

🚧 This is being updated for Dockerized dev setup 🚧

## Getting started

Install:

- Git 2.10+
- Node.js 6+ 
- Docker 17+

Then run:

1. `git clone <repo>` && `cd <repo>`
2. `npm install`
3. `docker-compose up`
4. Finish WordPress installation at `http://localhost:8088`

![image](https://cloud.githubusercontent.com/assets/101152/26283542/17fccd8a-3e2b-11e7-9881-a26fbb49d144.png)

This leaves you with a working development environment:

- VersionPress is mapped to container's `wp-content/plugins` so you can start hacking. (The `./frontend` project requires a rebuild, see below.)
- Database can be inspected at `http://localhost:8088`, server name: `db`, login info: see `docker-compose.yml`.
    - You can also use local tools like MySQL Workbench or `mysql` command-line client, the server is exposed on port `3366`. For example: `mysql --port=3366 -u root -p`.
- WordPress site root from the container is mapped to `./dev-env/wp` where you can inspect the files and Git history using your favorite tool.
- To invoke certain binaries, e.g., WP-CLI, in the context of a test WordPress site, you have these options:
    - SSH into container the container: `docker-compose exec wordpress /bin/bash`
    - Use `docker-compose exec wordpress <command>`, for example:
        - `docker-compose exec wordpress wp option update blogname "Hello"`
        - `docker-compose exec wordpress git log`
- Stop all Docker services: `Ctrl+C` in the console.
    - `docker-compose down -v` to clear up everything if you want to start fresh.

Next steps:

- [Debugging](#debugging)

## Debugging

> **Note**: VersionPress is PHP core plus React app for the frontend. This section concerns PHP debugging only.

The development container is preconfigured with [Xdebug](https://xdebug.org/) and most of the VersionPress developers prefer [PhpStorm](https://www.jetbrains.com/phpstorm/) but any IDE / editor should work similarly. Here's an example setup:

1. Create a `docker-compose.override.yml` file next to `docker-compose.yml`, you can copy it from `docker-compose.override.example.yml`.
2. ❗ Put your local IPv4 address there. This is specific to your computer and one of the few places where things can go wrong. You'll probably use a tool like `ipconfig` and it will be something like `192.168.123.123`.
3. In PhpStorm, go to Settings > PHP > Servers and create a server with two file mappings:<br><br>![image](https://cloud.githubusercontent.com/assets/101152/26285020/999202ea-3e47-11e7-8859-c792ca0d7d36.png)<br><br>
    - `<project root>/plugins/versionpress` -> `/var/www/html/wp-content/plugins/versionpress`
    - `<project root>/ext-libs/wordpress` -> `/var/www/html`
4. The default zero configuration settings should be fine: <br><br>![image](https://cloud.githubusercontent.com/assets/101152/26285067/34fefbd4-3e48-11e7-8f11-544507a1c5f7.png)<br><br>
5. Place a breakpoint somewhere and start listening for debug connections ![image](https://cloud.githubusercontent.com/assets/101152/26285076/5b9b2ca4-3e48-11e7-8ea3-280f9027831a.png)

Debugging should now work:

![image](https://cloud.githubusercontent.com/assets/101152/26285090/bb8aa432-3e48-11e7-973a-944abfe0039e.png)
    

