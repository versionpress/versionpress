# VersionPress #

## Prerequisites ##

* Apache webserver
* Git
* PHP version 5.3 or greater
* MySQL version 5.0 or greater

## Installation ##

1. Copy `plugins/versionpress` into `wp-content/plugins` directory.
2. Configure Apache (if you want synchronize between two environments)
3. Activate plugin in WordPress administration
4. Go to VersionPress section in administration and click on "Initialize"
5. Enjoy!


## Apache Configuration Example ##

Git push needs to be enabled on the server. This is an example config taken from [here](http://stackoverflow.com/questions/3817478/setting-up-git-server-on-windows-with-git-http-backend-exe#3982493):

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