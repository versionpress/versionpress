# Testing

Tests are a significant part of the VersionPress project, we care about writing and maintaining them. They live in `plugins/versionpress/tests` and there are several types of them, from unit to full end2end tests. They all run in a dockerized test environment.

> **Note**: the `./frontend` app has its own tests, this section is about core VersionPress tests (PHP code) only.

## Dockerized testing environment

Similarly to the [development environment](#exploring-dockerized-environment), tests utilize Docker Compose as well. The main benefit is that you don't need to set up things like Selenium or Java locally.

Most tasks are scripted, for example, you just run `npm run tests:unit` but you can also drop to the raw Docker Compose mode and do things like `docker-compose run --rm tests ...`. In that case, one thing to understand is that there are two services in `docker-compose.yml` to choose from:

- `tests` – just a test runner.
- `tests-with-wordpress` – starts a WordPress stack.

All scripts also come with a `...:debug` version, for example, `tests:unit:debug`. See [Starting a debugging session from command line](#starting-debugging-session-from-command-line).

## Running tests from command line

1. Make sure you have run `npm install` as described above and have Docker running.
2. Run `npm run tests:unit` or `npm run tests:full`.

Unit tests use a simpler `tests` service and are fast to execute.

The full tests include [end2end tests](#end2end-tests) and are relatively slow to run, however, if they pass, there's a good chance that VersionPress works correctly.

### Customizing what tests run

`tests:custom` and `tests:custom-with-wordpress` scripts allow you to run custom tests easily. Here are some examples:

> ❕ Notice how PHPUnit arguments come after `--`.

```sh
# Pick a test suite from the default phpunit.xml
npm run tests:custom -- -c phpunit.xml --testsuite Unit

# Create your own phpunit.*.xml (gitignored)
npm run tests:custom -- -c phpunit.custom.xml

# Run specific test class
npm run tests:custom-with-wordpress -- -c phpunit.xml --filter RevertTest
```

If you want to go entirely custom, use raw `docker-compose`:

```sh
# PhpStorm-like invocation (copy/pasted from its console):
docker-compose run --rm tests ../vendor/bin/phpunit --bootstrap /opt/versionpress/tests/phpunit-bootstrap.php --no-configuration /opt/versionpress/tests/Unit
```

### Test output

Npm scripts are configured to log in a TestDox format to container's `/var/opt/versionpress/logs` which is mapped to your local folder `./dev-env/test-logs`.

To log in [another supported format](http://phpunit.readthedocs.io/en/7.1/textui.html#command-line-options), run tests manually like this:

```
docker-compose run --rm tests ../vendor/bin/phpunit -c phpunit.xml --log-junit /var/opt/versionpress/logs/vp-tests.log
```

### Clean up tests

If you've run tests that use the `tests-with-wordpress` service, the whole Docker stack is kept running so that you can inspect it. For example, you can use your local Git client to explore the site's history in `dev-env/wp-for-tests/wptest`. The [end2end tests](#end2end-tests) section provides more info on this.

When you're done with tests, run `npm stop` to shut down the Docker stack or `npm run stop-and-cleanup` to also remove the volumes so that the next start is entirely fresh.

### Tips for tests

- If you're trying to narrow down a bug, it's useful to run a smaller test suite via one of the options above and add `stopOnFailure="true"` to the XML file or `--stop-on-failure` on the command line.
- Unit tests can also easily be run using a local `php` interpreter, basically just run them in PhpStorm.

## Running and debugging tests from PhpStorm

PhpStorm makes it easy to select specific tests and to debug them. Also, if you stop debugging, you will see messages gathered so far. There is a one-time setup to go through.

> 💡 If this doesn't work for you, e.g., due to some Docker Compose bug in PhpStorm, you can [start debugging from command line](#starting-debugging-session-from-command-line).

First, if you're using _Docker for Mac_ or _Docker for Windows_, expose a daemon in Docker settings:

![image](https://user-images.githubusercontent.com/101152/27441580-43a964c8-576e-11e7-9912-1be811f73c4b.png)

In PhpStorm, create a new Docker environment in _Settings_ > _Build, Execution, Deployment_ > _Docker_:

![image](https://user-images.githubusercontent.com/101152/27441828-ec760098-576e-11e7-9251-670204bf2643.png)

In the Docker panel, you should now be able to connect:

![image](https://user-images.githubusercontent.com/101152/27441986-508eb2e6-576f-11e7-83b6-20e9b6944619.png)

Next, define a remote interpreter. Make sure you have the **PHP Docker** plugin enabled and go to *Settings* > *Languages & Frameworks* > *PHP*. Add a new interpreter there:

![image](https://user-images.githubusercontent.com/101152/40119446-04674760-591d-11e8-9a53-43f61eb7de5c.png)

Note that the `docker-compose.yml` is at the repo root, not inside `./plugins/versionpress`:

![image](https://user-images.githubusercontent.com/101152/40119401-de795a66-591c-11e8-97cd-8c14e7a1976c.png)

If this doesn't go smoothly, try unchecking the _Include parent environment variables_ checkbox in the _Environment variables_ field:

![image](https://user-images.githubusercontent.com/101152/27796503-81cff2f4-600a-11e7-8cfb-96661f0281a9.png)

Select this CLI interpreter as the main one for the project and make sure the path mappings are correct:

![image](https://user-images.githubusercontent.com/101152/40119974-51c4b528-591e-11e8-8d55-56fac37ffa18.png)

The final step is to set up a test framework in _PHP_ > _Test Frameworks_. Add a new _PHPUnit by Remote Interpreter_:

![image](https://user-images.githubusercontent.com/101152/27797069-900fafce-600c-11e7-9ff9-db2d4507aa89.png)

Don't forget to set the _Default bootstrap file_ to `/opt/versionpress/tests/phpunit-bootstrap.php`.

Now you're ready to run the tests. For example, to run all unit tests, right-click the `Unit` folder and select _Run_:

![image](https://user-images.githubusercontent.com/101152/27797266-48a041fc-600d-11e7-88f0-aa557eb02325.png)

Debugging also works, just select _Debug_ instead of _Run_:

![image](https://user-images.githubusercontent.com/101152/27797346-93e132ca-600d-11e7-8052-9b4790739747.png)

This works equally well other types of tests as well, for example, Selenium tests:

![image](https://user-images.githubusercontent.com/101152/27797533-57f12904-600e-11e7-971b-08fd943aaf7b.png)

## Starting debugging session from command line

This method is more universal and works for PhpStorm, VSCode and other IDEs. You generally do this:

1. Set a breakpoint.
2. Start listening in your IDE.
3. Launch a debug-enabled script like `npm run tests:unit:debug` (see [package.json](https://github.com/versionpress/versionpress/blob/master/package.json)).

### PhpStorm example

First, make sure you have the 'VersionPress-tests' server defined in _Settings > Languages & Frameworks > PHP > Servers_. If not, run `npm run init-phpstorm`.

Then, set a breakpoint in some test and start listening for debug connections in the toolbar.

Run `npm run tests:unit:debug` in the console, skip the first break at the `wp` binary and see your breakpoint hit:

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

Then, start a debugging session in VSCode and set a breakpoint. Run the `tests:unit:debug` script and see the breakpoint hit:

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

After you've run the tests, the Docker stack is left up and running so that you can inspect it:

- The site is running at <http://wordpress-for-tests/> – [update your hosts file](https://www.howtogeek.com/howto/27350/beginner-geek-how-to-edit-your-hosts-file/) accordingly and log in using the info in `test-config.yml`.
- The files are mapped to `./dev-env/wp-for-tests`, you can use your local Git client to inspect it.
- Connect to the database via `mysql -u root -p` or Adminer which you can access by running `docker-compose run -d --service-ports adminer` and visiting <http://localhost:8099>. The database name is `mysql-for-wordpress`.

Stop the Docker stack with `npm run stop-and-cleanup` (stop-and-cleanup is strongly recommended here; end2end tests are not perfectly isolated yet).

## Other tests

The project has these other types of tests (folders in the `./plugins/versionpress/tests` folder and also test suite names in `phpunit.xml` so that you can run them using `--testsuite <SuiteName>`):

- `GitRepositoryTests` – test Git repository manipulation in `GitRepository`.
- `SynchronizerTests` – these are quite slow and test that given some INI files on disk, the database is in a correct state after synchronization runs.
- `StorageTests` – test that entities are stored correctly as INI files.
- `LoadTests` – they are run together with other tests but with very few iterations; manually update their source files and execute them separately to properly exercise them.
- `Selenium` – a bit like end2end tests but for rarer cases, like VersionPress not being activated yet.
- `Workflow` – exercise cloning and merging between environments.
