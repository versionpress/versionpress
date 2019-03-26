# Testing

Tests are a significant part of the VersionPress project, we care about writing and maintaining them. They live in `plugins/versionpress/tests` and there are several types of them, from unit to full end2end tests. They all run in a dockerized test environment.

> **Note**: the `./frontend` app has its own tests, this section is about core VersionPress tests (PHP code) only.

## Running tests from command line

1. Make sure you ran `npm install` as described in [Dev Setup](dev-setup.md).
2. Run `npm run tests:unit` as a sanity check – this should pass quickly.
3. Run `npm run tests` to run the full test suite.

The first run might be very slow as Docker images need to be pulled. Subsequent runs should take between 10 and 30 minutes, depending on your machine and how you run Docker runs (for example, Docker Toolbox on Windows uses slower virtualization than Docker Desktop).

### Customizing what tests run

The `tests` script accepts [PHPUnit parameters](https://phpunit.de/manual/5.7/en/textui.html), here are some examples:

❗️ Notice how parameters come after `--`; this is required by npm.

```
# Pick a test suite (see phpunit.xml):
npm run tests -- --testsuite Unit

# Filter down further:
npm run tests -- --testsuite Unit --filter CursorTest

# Stop on the first error or failure:
npm run tests -- --stop-on-failure

# Create your own phpunit.xml (phpunit.*.xml is Git-ignored)
npm run tests -- -c phpunit.custom.xml
```

## Running and debugging tests from PhpStorm

This section has not been updated for the new test runner (PR [#1401](https://github.com/versionpress/versionpress/pull/1401)) yet; you can see how it worked previously [here](https://github.com/versionpress/versionpress/blob/dbfa7f37d436d4e4035b48b25e39f4d3553ec643/docs/content/en/developer/testing.md#running-and-debugging-tests-from-phpstorm).

## Starting debugging session from command line

This method is more universal and works for PhpStorm, VSCode and other IDEs. You generally do this:

1. Set a breakpoint.
2. Start listening in your IDE.
3. Launch a debug-enabled script (TODO).

### PhpStorm example

First, make sure you have the 'VersionPress-tests' server defined in _Settings > Languages & Frameworks > PHP > Servers_. If not, run `npm run init-phpstorm`.

Then, set a breakpoint in some test and start listening for debug connections in the toolbar.

Run `TODO` in the console, skip the first break at the `wp` binary and see your breakpoint hit:

![image](https://user-images.githubusercontent.com/101152/40370369-66eda84e-5de0-11e8-88f5-9792421a92ab.png)

See [this JetBrains help page](https://confluence.jetbrains.com/display/PhpStorm/Debugging+PHP+CLI+scripts+with+PhpStorm) for more.

### VSCode example

In VSCode, install [PHP Debug extension](https://marketplace.visualstudio.com/items?itemName=felixfbecker.php-debug) and create a `launch.json` config containing this:

```json
{
  "name": "PHP: Listen for Xdebug",
  "type": "php",
  "request": "launch",
  "port": 9000,
  "pathMappings": {
    "/opt/versionpress": "${workspaceRoot}/plugins/versionpress",
  }
}
```

Then, start a debugging session in VSCode and set a breakpoint. Run the `TODO` script and see the breakpoint hit:

![image](https://user-images.githubusercontent.com/101152/40372175-7880a49a-5de4-11e8-9e87-adfb25d184ef.png)

## Unit tests

Unit tests are best suited for small pieces of algorithmic functionality. For example, `IniSerializer` is covered by unit tests extensively.

You can either run unit tests in a dockerized environment as described above or set up a local CLI interpret which makes the execution faster and more convenient.

## End2end tests

End2end tests exercise a WordPress site and check that VersionPress creates the right Git commits, that the database is in correct state, etc. These tests are quite heavy and slow to run but if they pass, there's a good chance that VersionPress works correctly. (Before the project had these, long and painful manual testing period was necessary before each release.)

End2end tests use the concept of **workers**: each test itself is implemented once but how e.g. a post is created or a user deleted is up to a specific worker. There are currently two types of workers:

1. **WP-CLI workers** – run WP-CLI commands against the test site.
2. **Selenium workers** – simulate real user by clicking in a browser.

In the future, we might add REST API workers; you get the idea.

Currently, the default worker is WP-CLI and the only way to switch workers is to update `tests/test-config.yml`, the `end2end-test-type` key. We'll make it more flexible in the future.

### Troubleshooting failed tests

The Docker containers are stopped when tests finish running but the data is kept in Docker volumes. You can start the WordPress site again via:

```
npm run tests -- --explore
```

You can now inspect it:

- The site is running at <http://wordpress-for-tests/wptest> – check `test-config.yml` for the login info. (You'll also need to update your hosts file so that `wordpress-for-tests` resolves to `127.0.0.1`.)
- Connect to the database via `mysql -u root -p` or Adminer which you can access by running `docker-compose run -d --service-ports adminer` and visiting <http://localhost:8099>. The database name is `mysql-for-wordpress`.
- To inspect the site files or the logs, you have two options:
    1. Run `docker-compose -f docker-compose-tests.yml run --rm tests sh` and use commands like `ls -ls /var/www/html/wptest` or `cd /var/www/html/wptest && git log` to explore the files. Type `exit` when finished.
    2. Run `npm run tests:copy-files-to-host` to copy files to your local filesystem. This will create two folders, `dev-env/wp-for-tests` and `dev-env/test-logs`, where you can conveniently use your local tools (editors, Git GUI clients, etc.). Note that this can be quite resource-intensive, for example, on Docker for Mac, this will overwhelm the system for several minutes.

When you're done, clean up everything by running:

```
npm run tests:cleanup
```

This will stop & remove containers, delete volumes and remove temporaray files under `dev-env`.

## Other tests

The project has these other types of tests (folders in the `./plugins/versionpress/tests` folder and also test suite names in `phpunit.xml` so that you can run them using `--testsuite <SuiteName>`):

- `GitRepositoryTests` – test Git repository manipulation in `GitRepository`.
- `SynchronizerTests` – these are quite slow and test that given some INI files on disk, the database is in a correct state after synchronization runs.
- `StorageTests` – test that entities are stored correctly as INI files.
- `LoadTests` – they are run together with other tests but with very few iterations; manually update their source files and execute them separately to properly exercise them.
- `Selenium` – a bit like end2end tests but for rarer cases, like VersionPress not being activated yet.
- `Workflow` – exercise cloning and merging between environments.
