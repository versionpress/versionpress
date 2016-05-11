This page is about getting your local environment ready for VersionPress **development**. The setup is more like a standard software project because there are external dependencies, we require a build step etc.

**Just cloning a repo into the `wp-content/plugins` folder will not work.** 


## System-wide tools / prerequisites

1. [Git](http://git-scm.com/), obviously :)
2. Until we have a Vagrant / Docker based workflow, install PHP & Apache locally, e.g., using [MAMP](https://www.mamp.info/en/) on Mac or [WampServer 32bit](http://www.wampserver.com/en/) on Windows.
    - PHP **5.6+** is required for development (5.3+ is enough in runtime; the difference is some development-time tools and libraries).
3. Install [Composer](https://getcomposer.org/).
4. Install [WP-CLI](http://wp-cli.org/)
    - Can be [installed using Composer](https://github.com/wp-cli/wp-cli/wiki/Alternative-Install-Methods).
    - DB commands expect `mysql` in PATH.
5. Install [Node.js](http://nodejs.org/).
    - [Python 2.x](https://www.python.org/downloads/) is required to build some NPM modules.
    - Install [Gulp](http://gulpjs.com/) globally.


## Project checkout

1. Clone [the repo](https://github.com/versionpress/versionpress) into some **project folder**, e.g., `/Users/you/Projects/versionpress`.
    - This is NOT under `wp-content` of some WordPress installation. Test site lives separately and you'll set it up later.
3. Run `npm install` in the project root.

This will download all sorts of dev dependencies and build the project. It contains two main parts:

- The **VersionPress core** in **`plugins/versionpress`**. This is mostly the PHP code implementing the core versioning functionality.
- Its **frontend** in the **`frontend`** folder. The GUI is built as a JavaScript SPA and can run either inside the WordPress administration (which we currently do when we distribute VersionPress) or separately (e.g., for testing or inside some control panel in the future).


## IDE / editor setup

PhpStorm is the recommended IDE. If you don't use PhpStorm, you can proceed to [Developing without PhpStorm](#developing-without-phpstorm).

### PhpStorm setup

Required extensions:

- [EditorConfig](https://plugins.jetbrains.com/plugin/7294?pr=phpStorm)


Recommended extensions:

- [.ignore](https://plugins.jetbrains.com/search/index?pr=phpStorm&search=gitignore)
- [NodeJS](https://plugins.jetbrains.com/plugin/6098?pr=phpStorm)


### PhpStorm + the 'VersionPress' project

To generate project files, run `gulp idea`. It creates two PhpStorm projects, one for the plugin itself (in `plugins/versionpress`) and one for the frontend (in `frontend`). This section focuses on the former.

Open the project at `plugins/versionpress` (still in a *project folder*) and then:

1. Enable WordPress supports (PhpStorm should prompt you automatically)
2. Do **not** enter *WordPress installation path* in the popup dialog – just leave it empty and press OK
3. Do **not** set WordPress code style which PhpStorm will prompt you to (just dismiss the notification). We use [PSR-2](http://www.php-fig.org/psr/psr-2/).
4. Run *Composer* > *Init Composer*. This should automatically add Composer dependencies as PHP libraries in PhpStorm.
5. External dependencies (`PROJECT_ROOT/ext-libs`) need to be added manually. Go to *Settings > Languages & Frameworks > PHP* and add:
    - `ext-libs/wordpress`
    - `ext-libs/vendor/wp-cli`
6. Specify a path to Code Sniffer in *Settings* > *Languages & Frameworks* > *PHP* > *Code Sniffer*, click the three dots, _PHP Code Sniffer (phpcs) path_. The path should point to `vendor/bin/phpcs`. After this is done, PhpStorm will start checking the code style locally which is useful because the same checks run on Travis CI once the code is pushed to GitHub. 


### PhpStorm + the 'frontend' project

You'll only need to open this project if you do a frontend (GUI) development.

1. Run `gulp idea` if you haven't done that already. This will create PhpStorm project files.
2. Open the `frontend` project in PhpStorm.
3. Answer "No" to *Compile TypeScript to JavaScript?* prompt.

Linting task is set up for the frontend project. Run `npm run lint` in the `frontend` directory.

### Developing without PhpStorm

In the editor of your choice, please install [EditorConfig](http://editorconfig.org/) support.

For the 'VersionPress' project:

1. Open `plugins/versionpress` in your IDE / editor.
2. Add `ext-libs` in include path (if your IDE / editor supports it).
3. Set the code style to PSR-2 (if your IDE / editor supports it).

For the 'frontend' project just open `frontend` and start hacking – it's a TypeScript + React app. See [Run frontend separately](#run-frontend-separately).


## Running & debugging

To simply run VersionPress in your WP site, you can copy `PROJECT_DIR/plugins/versionpress` to `YOUR_WP_SITE/wp-content/plugins`. However, for the full workflow where you'll be editing files and redeploying the project, we recommend setting up `WpAutomation`.

### WpAutomation setup

We use the [`WpAutomation`](https://github.com/versionpress/versionpress/blob/master/plugins/versionpress/tests/Automation/WpAutomation.php) class and its methods to script certain tasks like setting up a WordPress site, copying VersionPress there, etc. It could all be done manually but WpAutomation saves a lot of time so let's set it up.

1. Make sure you have WP-CLI installed globally (see [environment setup](#system-wide-tools--prerequisites) above).
3. In the `tests` directory (`PROJECT_DIR/plugins/versionpress/tests`), copy `test-config.sample.yml` into `test-config.yml` and update the values to match your local environment.
    - WpAutomation doesn't create a database, it should already exist. For example, the sample file uses dbname, user and password `vp01` so you should create this beforehand (or configure any values that fit your local environment).
    - The web and DB server must both be up and running.
    - Windows users, here's a [sample configuration](./Testing.md#windows-users) for you.
3. Use `WpAutomation` methods to do stuff you need. For example, this will set up a WordPress site and initialize VersionPress in it:

```
$testConfig = TestConfig::createDefaultConfig();
$wpAutomation = new WpAutomation($testConfig->testSite, $testConfig->wpCliVersion);

$wpAutomation->setUpSite();
$wpAutomation->copyVersionPressFiles();
$wpAutomation->initializeVersionPress();
```

**Tip**: There's a `WpAutomationRunnerSample` class which you can copy to `WpAutomationRunner.local.php`, rename the class and place the actual scripting code into the `runAutomation()` method. This can then be conveniently run as a PHPUnit test from within PhpStorm (right-click method name and select *Run*).


### Run VersionPress

With WpAutomation in place, just run the web server and invoke the automation methods as above to set up the site and activate / initialize VersionPress there. This also takes care of building the frontend and deploying it to the VersionPress admin screens.

Subsequent edits can be deployed to WordPress either using the `gulp test-deploy` task, copying `plugins/versionpress` in `wp-content/plugins` of your site or by setting up PhpStorm's deployment.


#### PhpStorm's Deployment

Create new deployment and set it up like this:

- *Connection* tab:
    - *Type* = Local or mounted folder
    - *Folder* = c:\wamp\www\vp01\wp-content\plugins\versionpress
    - *Web server root URL* = http://localhost/vp01/wp-content/plugins/versionpress (they named it wrong, this "root" URL should really be this nested one)
- *Mappings* tab:
    - Keep only single mapping - remove the rest. Then:
    - *Local path* = `PROJ_DIR/plugins/versionpress/`
    - *Deployment path on server* = `.`
    - *Web path on server* = `/`
- *Excluded Paths* tab:
    - `tests` (except cases when scripts from tests/automation are wanted)
    - `versionpress.iml`
    - `.gitignore`
    - `composer.json`
    - `composer.lock`
    - and possibly others, see the build script



### Run frontend separately

For pure frontend development, it's more convenient to run it outside of the WordPress administration. To do that:

1. Edit `frontend/src/config.local.ts` and enter your local values
2. Find the `plugins/versionpress/vpconfig.yml` file inside the live WordPress / VersionPress installation and add this to it:

    ```
    requireApiAuth: false
    ```

3. Run `npm run dev` in the `frontend` directory. This launches [webpack dev server](http://webpack.github.io/docs/webpack-dev-server.html) on the default URL http://localhost:8888. Changed files are automatically reflected in the browser.

To deploy the JS app into the WordPress backend, you can use `gulp test-deploy` task in the project root.


### Debugging PHP code

PhpStorm's [Zero-Configuration Debugging](https://www.jetbrains.com/phpstorm/help/zero-configuration-debugging.html) with Xdebug works well with this project. We need just some slight configuration because the project doesn't map directly to the site strucutre.

1. Make sure you have Xdebug installed and enabled on your webserver
2. Create a "server" in PHP > Servers, check "Use path mappings" and create these:
    1. Under Project files, map `PROJ_DIR/plugins/versionpress` to `SITE_ROOT/wp-content/plugins/versionpress`
    2. Under Include path, map `PROJ_DIR/ext-libs/wordpress` to `SITE_ROOT` (e.g., `/var/www/wordpress`)

That's it. Enable Xdebug in the browser, click "Start listening" in PhpStorm and rock on.

### Debugging PHP CLI code

Some pieces of code run from the conosle (e.g., WP-CLI commands). Here is how to debug them:

1. Turn on Xdebug in `php.ini` (careful is you use WampServer: it uses different `php.ini` for the web server and for the command line; you need to update `C:\wamp\bin\php\php-x.y.z\php.ini`):

    ```
    [xdebug]
    zend_extension = ...
    xdebug.remote_enable = on
    ```
 
2. Run `export XDEBUG_CONFIG="idekey=session_name"` on Linux & Mac / `SET XDEBUG_CONFIG=idekey=xdebug` on Windows
3. Start zero config debug in PhpStorm
4. Run the CLI command

If PhpStorm doesn't understand that deployed WP-CLI script should map to a source WP-CLI script in your project (breakpoints are missed), just open the deployed `vp.php` or `vp-internal.php` files in PhpStorm and set breakpoints there. (Update this section is there is a better way.)


### Debugging frontend project

GUI debugging is recommended in Chrome Dev Tools (or other F12 tools) as described [here](https://developer.chrome.com/devtools/docs/javascript-debugging). Source files can be found in the `Sources` folder under the `webpack://` line in the `.` folder.

It is also recommended to install [React Developer Tools](https://chrome.google.com/webstore/detail/react-developer-tools/fmkadmapgofadopljbjfkapdkoienihi).


## Testing

Testing is covered by separate page, see [Testing](./Testing.md).


## Windows tips

While most VersionPress developers (and WP developers in general) are on Macs, we try to provide similar dev experience on all platforms. Here's a couple of tips for Windows users.


### WampServer

There are many WAMP stacks available for Windows but we recommend [**WampServer**](http://www.wampserver.com/en/). The current 3.0 version contains both PHP 5.6 and 7.0 which is good for development.

Most tips below assume WampServer / are tailored to it.


### Xdebug

Beware that WampServer comes with **two `php.ini` files**, one for the web server and one command-line PHP.

- For the web server, you can just click the tray icon > *PHP* > *PHP Settings* > *(Xdebug): Remote Debug*.
- For command-line debugging, e.g., unit tests in PhpStorm, you need to edit `php.ini` file in C:\wamp\bin\php\phpx.y.z. Copy the values from `phpForApache.ini`, e.g.:

```
[xdebug]
zend_extension ="C:/wamp/bin/php/php5.6.16/zend_ext/php_xdebug-2.4.0rc2-5.6-vc11-x86_64.dll"
xdebug.remote_enable = On
```
