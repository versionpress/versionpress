# VersionPress #

Version control plugin for WordPress

## Development environment ##

Setting up development environment is a bit tricky because VersionPress itself creates and manages a Git repository, so for example testing a revert can also roll back the code changes that are currently being tested, etc.

The setup below tries to make things as easy as possible:

1. **Install PhpStorm** / IDEA and enable *PHP* and *WordPress support* plugins (those are not enabled by default in IDEA)
2. **Clone the project** from BitBucket to some folder, e.g. `C:\Projects\VersionPress`
3. **Install [WAMP Server](http://www.wampserver.com/en/)** 64-bit version
4. **[Install WordPress](http://codex.wordpress.org/Installing_WordPress)** under WAMP. Quick summary (assuming the WordPress instance will be named **vp01**):
	a. Extract files to e.g. `c:\wamp\www\vp01`
    b. Go to phpMyAdmin (http://localhost/phpmyadmin) > Users > Add user > give him a name like `vp01`, for host `localhost`, check *Create database with same name and grant all privileges*. You should see a new database `vp01` created.
    c. Download WordPress, extract the files to `www\vp01`
    d. Go to http://localhost/vp01/ and follow the WP installation wizard
    e. Finish the wizard, log in to the WP site
    f. Create a copy of the WP site to e.g. `vp01-cleaninstall`, dump the MySQL database to a file like `vp01-cleaninstall\vp01.sql` so that you can quickly return the site to the default state if needed
5. **Open VersionPress project in PhpStorm** / IDEA - note that you are opening the project *outside* of c:\wamp\www. The project was checked out to c:\Projects\VersionPress in this example.
6. If not done automatically, configure project to support WordPress and **add vp01 (WordPress installation dir) into PHP include path**
7. Update **Deployment settings** under project settings, see e.g. [here](http://www.trotch.com/blog/wordpress-and-phpstorm-howto/). In short:
	* Create a mapping between project folder, webserver folder and localhost URL
	* In the Options sub-page, check *Upload changed files automatically...* and check *Upload external changes*
8. **Deploy the code** - right-click project, select Upload...
9. Activate VersionPress plugin via WP administration
10. Debug (TODO - document this)



## Remote site setup (push / pull) ##

*Note: this is not fully worked out yet, staging feature will be implemented later.*

Git push needs to be enabled on the server. This is an example Apache config taken from [here](http://stackoverflow.com/questions/3817478/setting-up-git-server-on-windows-with-git-http-backend-exe#3982493):

httpd.conf
```
# ============================================================================
### Git Configuration
# ============================================================================

<Directory "${path}/www"> # ${path} is special EasyPHP variable – it should be replaced with real path
Options +ExecCGI
Require all granted
</Directory>

SetEnv GIT_PROJECT_ROOT ${path}/www
SetEnv GIT_HTTP_EXPORT_ALL
SetEnv REMOTE_USER=$REDIRECT_REMOTE_USER

ScriptAliasMatch "(?x)^/(.*/(HEAD|info/refs|objects/(info/[^/]+|[0-9a-f]{2}/[0-9a-f]{38}|pack/pack-[0-9a-f]{40}\.(pack|idx))|git-(upload|receive)-pack))$" "c:/Program Files (x86)/Git/libexec/git-core/git-http-backend.exe/$1" # Set path to git-http-backend

<LocationMatch ".*\.git.*">
Options +ExecCGI
AuthType Basic
AuthUserFile "${path}/www/git/test.git/.htpasswd" # Set path to htpasswd file
AuthName intranet
Require valid-user
</LocationMatch>

# ============================================================================
### Git Configuration End
# ============================================================================
```