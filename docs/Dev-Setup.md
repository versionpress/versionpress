# Dev setup

🚧 This is being updated for Dockerized dev setup 🚧

## Getting started

Install:

- Git 2.10+
- Node.js 6+ 
- Docker 17+

Then run:

1. `git clone <repo>` && `cd <repo>`
2. `npm install` – this downloads all dependencies and builds the project
3. `docker-compose up`
4. Finish WordPress installation at `http://localhost:8088`

This leaves you with a working development environment:

- VersionPress is mapped to container's `wp-content/plugins` so you can start hacking. (The `./frontend` project requires a rebuild, see below.)
- Database can be inspected at `http://localhost:8088`, server name: `db`, login info: see `docker-compose.yml`.
- WordPress site root from the container is mapped to `./dev-env/wp` where you can inspect the files and Git history using your favorite tool.
- To invoke certain binaries, e.g., WP-CLI, in the context of a test WordPress site, you have these options:
    - SSH into container the container: `docker-compose exec wordpress /bin/bash`
    - Use `docker-compose exec wordpress <command>`, for example:
        - `docker-compose exec wordpress wp option update blogname "Hello"`
        - `docker-compose exec wordpress git log`
- Stop all Docker services: `Ctrl+C` in the console.
    - `docker-compose down -v` to clear up everything if you want to start fresh.
