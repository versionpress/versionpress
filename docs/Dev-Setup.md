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

- **PHP source files** at `./plugins/versionpress` are mapped into the container, any changes are immediately reflected.
    - Frontend (the UI) is a React single page app that requires a rebuild. See below for instructions. 
- **DB** can be inspected at `http://localhost:8088`, server name `db`, login info form `docker-compose.yml`.
- **WP-CLI**: invoke `docker-compose exec wordpress wp <command>` in a new console, e.g., `docker-compose exec wordpress wp option update blogname "Hello"`.
    - Create an alias if you use this often.
- **Git**: `docker-compose exec wordpress git log`.
- Stop all Docker services: `Ctrl+C` in the console.
    - `docker-compose down -v` to clear up everything if you want to start fresh.
