This page is about getting you ready for VersionPress _development_. For instructions on using VersionPress, see [these docs](https://docs.versionpress.net/en).

Table of contents:

- [Prerequisites](#prerequisites)
- [Checkout & build](#checkout--build)
- [IDE / editor setup](#ide--editor-setup)
- [Running & debugging](#running--debugging)
- [Testing](#testing)
- [Windows tips](#windows-tips)

## Prerequisites

Make sure these tools are installed on your computer:

- PHP 5.6 or 7.x and a webserver like [MAMP](https://www.mamp.info/en/) on Mac or [WampServer](http://www.wampserver.com/en/) on Windows
- [Git](http://git-scm.com/)
- [Composer](https://getcomposer.org/)
- [WP-CLI](http://wp-cli.org/)
    - DB commands expect `mysql` in PATH
- [Node.js](http://nodejs.org/)
    - [Python 2.x](https://www.python.org/downloads/) is required to build some NPM modules
- [Gulp](http://gulpjs.com/) (globally)


## Checkout & build

1. **Clone the repo** to a project folder like `/Users/you/Projects/versionpress`. Note that this is _not_ under `wp-content` of a WordPress site; you'll set up a test site later.
2. **Run `npm install`** in the project root. This downloads dev dependencies and builds the project.
3. If impatient, you can now **copy the `plugins/versionpress` folder to some WordPress site** for testing. There are better ways though, see below.

The sources contain two main parts:

- **`plugins/versionpress`**: the actual plugin, i.e., PHP code.
- **`frontend`**: the frontend written in React and TypeScript.

The front-end requires building (transpiling the code to JavaScript, bundling it and copying to the WordPress plugin). `npm install` made it automatically, you can also invoke `gulp frontend-build-and-deploy` manually but there are easier workflows, see below.

### ZIP builds

To build a ZIP file to distribute VersionPress (supports both stable releases, alphas, betas etc.):

1. Run **`gulp build`**.
2. Watch the magic happen.

A file like `dist/versionpress-3.0.2.zip` is produced. The file name is based on the nearest Git tag, for example:

```
versionpress-3.0.2.zip
# built from commit tagged 3.0.2

versionpress-3.0.2-27-g0e1ce7f.zip
# built from 0e1ce7f which is based on 3.0.2 with 27 new commits
```

See [`git describe --tags`](https://git-scm.com/docs/git-describe#_examples) for more examples.


## IDE / editor setup

[PhpStorm](https://www.jetbrains.com/phpstorm/) is the recommended IDE: it understands both PHP and JavaScript / TypeScript code pretty well. If you don't want to use PhpStorm, please proceed to [Developing without PhpStorm](#developing-without-phpstorm).

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

1. Run `gulp idea` if you haven't done that already. This will create PhpStorm / WebStorm project files.
2. Open the `frontend` project in PhpStorm.
3. Answer "No" to *Compile TypeScript to JavaScript?* prompt.

Linting task is set up for the frontend project. Run `npm run lint` in the `frontend` directory.

### Developing without PhpStorm

In the editor of your choice, please install [EditorConfig](http://editorconfig.org/) support.

For the 'VersionPress' project:

1. Open `plugins/versionpress` in your IDE / editor.
2. Add `ext-libs` in include path if your IDE / editor supports it.
3. Set the code style to PSR-2 if your IDE / editor supports it.

For the 'frontend' project just open `frontend` and start hacking – it's a TypeScript + React app. See [Run frontend separately](#run-frontend-separately).


## Running & debugging

After the initial setup and build (see above), you can copy `PROJECT_DIR/plugins/versionpress` to `YOUR_WP_SITE/wp-content/plugins`. However, for the full workflow where you'll be editing files and redeploying the project, we recommend setting up `WpAutomation`.

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

1. Copy `frontend/src/config/config.local.sample.ts` to `frontend/src/config/config.local.ts` and enter your local values.
2. Find the `plugins/versionpress/bootstrap.php` file inside the live WordPress / VersionPress installation and redefine the `VERSIONPRESS_REQUIRE_API_AUTH` constant to `false`.
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
