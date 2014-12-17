# External libraries

Here are source files of external libraries that are useful to have available in PhpStorm but are not really classical `vendor` dependencies or it is not practical to have them there. For example, WordPress core itself could probably live as a dev-dependency in `plugins/versionpress/composer.json` but it's too many files (annoying for auto-upload), is not a classical library etc. so we rather keep these things separately here.

## How to initialize

Just run `composer install`. PhpStorm / IDEA project files already have the correct pointers to this directory set up.