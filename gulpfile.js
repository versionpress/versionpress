/**
 * VersionPress build script. See the end of the file for main callable tasks.
 */

var gulp = require('gulp');
var del = require('del');
var shell = require('gulp-shell');
var shelljs = require('shelljs/global');
var rename = require('gulp-rename');
var zip = require('gulp-zip');
var replace = require('gulp-replace');
var fs = require('fs');
var path = require('path');
var iniParser = require('ini');

/**
 * Version to be displayed both in WordPress administration and used as a suffix of the generated ZIP file
 * @type {string}
 */
var packageVersion = "";

/**
 * Type of build. Possible values:
 *
 *  - '' (default - means production)
 *  - 'nightly' - set by the `nightly` task
 *  - 'test-deploy' - set by the `test-deploy` task
 *
 * @type {string}
 */
var buildType = ''; // empty (default, means production),

/**
 * Directory where the VP files are copied to and some actions
 * like `composer install`, comment stripping etc. are run on them.
 *
 * One exception is the `test-deploy` task that basically just runs the `copy`
 * and sets buildDir directly to some WP test site, see `prepare-test-deploy`.
 *
 * @type {string}
 */
var buildDir = './build';

/**
 * Where to put the final ZIP file
 *
 * @type {string}
 */

var distDir = './dist';

/**
 * VersionPress source directory
 *
 * @type {string}
 */
var vpDir = './plugins/versionpress';

/**
 * Array of globs that will be used in gulp.src() to copy files from source to destination.
 * Prepared in `prepare-src-definition`.
 *
 * @type {Array}
 */
var srcDef = [];


gulp.task('set-nightly-build', function() {
    buildType = 'nightly';
});

/**
 * Sets `buildDir` and `buildType` so that the copy methods copies to the WP test site.
 */
gulp.task('prepare-test-deploy', function() {
    var testConfigStr = fs.readFileSync(vpDir + '/tests/test-config.ini', 'utf-8');
    var testConfig = iniParser.parse(testConfigStr);
    buildDir = testConfig["Site-Settings"]["site-path"] + "/wp-content/plugins/versionpress";

    buildType = 'test-deploy'; // later influences the `prepare-src-definition` task
});

/**
 * Prepares `srcDef` that is later used in the `copy` task. The src definition
 * is slightly different for various buildTypes.
 */
gulp.task('prepare-src-definition', function () {

    srcDef = [];

    srcDef.push(vpDir + '/**'); // add all, except:

    srcDef.push('!' + vpDir + '/versionpress.iml');
    srcDef.push('!' + vpDir + '/.gitignore'); // this .gitignore is valid for a project, not for deployed VP
    srcDef.push('!' + vpDir + '/.editorconfig');
    srcDef.push('!' + vpDir + '/.gitattributes');
    srcDef.push('!' + vpDir + '/tests{,/**}'); // tests might be useful for `test-deploy` but we don't currently need them
    srcDef.push('!' + vpDir + '/log/**/!(.gitignore)'); // keep just the .gitignore inside `log` folder
    srcDef.push('!' + vpDir + '/**.md'); // all Markdown files are considered documentation

    // and now some build-specific patterns:
    if (buildType == 'test-deploy') {
        srcDef.push('!' + vpDir + '/composer.json');
        srcDef.push('!' + vpDir + '/composer.lock');
        // keep `vendor`
    } else {
        srcDef.push('!' + vpDir + '/vendor{,/**}'); // `vendor` is fresh-generated later in the `composer-install` task
        // keep composer.json|lock
    }

});

/**
 * Cleans buildDir and distDir
 */
gulp.task('clean', function (cb) {
    del([buildDir, distDir], {force: true} , cb);
});


/**
 * Copies files defined by `srcDef` to `buildDir`
 */
gulp.task('copy', ['clean', 'prepare-src-definition'], function (cb) {
    var srcOptions = {dot: true, base: vpDir};
    return gulp.src(srcDef, srcOptions).pipe(gulp.dest(buildDir));
});

/**
 * Removes all comments from the source code and stores the new files to .php-strip files.
 * Next tasks are `rename-phpstrip-back` and `remove-phpstrip-files` and also `persist-plugin-comment`
 * needs to be run in order to restore plugin metadata which are also technically comments but need
 * to be there.
 */
gulp.task('strip-comments', ['copy'], function (cb) {
	var stripCmd = (vpDir + '/vendor/bin/strip').replace(/\//g, path.sep);

    return gulp.src(buildDir + '/**/*.php', {read: false}).
        pipe(shell([stripCmd + ' <%= file.path %> > <%= file.path %>-strip']));
});

/**
 * Copies content of *.php-strip files back to the original files
 */
gulp.task('rename-phpstrip-back', ['strip-comments'], function (cb) {
    return gulp.src(buildDir + '/**/*.php-strip').pipe(rename({extname: '.php'})).
        pipe(gulp.dest(buildDir));
});

/**
 * Removes *.php-strip files
 */
gulp.task('remove-phpstrip-files', ['rename-phpstrip-back'], function (cb) {
    del(buildDir + '/**/*.php-strip', cb);
});

/**
 * Installs Composer packages, ignores dev packages prefers dist ones
 */
gulp.task('composer-install', ['remove-phpstrip-files'], shell.task(['composer install -d ' + buildDir + ' --no-dev --prefer-dist']));

/**
 * Removes composer.json|lock after the `composer-install` task is done
 */
gulp.task('remove-composer-files', ['composer-install'], function (cb) {
    del([buildDir + '/composer.json', buildDir + '/composer.lock'], cb);
});

/**
 * Copies plugin metadata from source versionpress.php to the `buildDir` version.
 */
gulp.task('persist-plugin-comment', ['rename-phpstrip-back'], function (cb) {
    var fileOptions = {encoding: 'UTF-8'};
    fs.readFile(vpDir + '/versionpress.php', fileOptions, function (err, content) {
        var definePosition = content.indexOf("define");
        var originalHead = content.substr(0, definePosition);
        var versionMatch = content.match(/^Version: (.*)$/m);
        packageVersion = versionMatch[1];
        if (buildType == 'nightly') {
            var gitCommit = exec('git rev-parse --short HEAD', {silent: true}).output.trim(); // trims the "\n" from the end
            packageVersion += '+' + gitCommit;
        }

        fs.readFile(buildDir + '/versionpress.php', fileOptions, function (err, content) {
            var definePosition = content.indexOf("define");
            var newContent = originalHead + content.substr(definePosition);
            newContent = newContent.replace(/^Version: .*$/m, 'Version: ' + packageVersion);
            fs.writeFile(buildDir + '/versionpress.php', newContent, fileOptions, function (err) {
                cb();
            });
        });
    });
});

/**
 * Does some code changes to build a production version. Currently sets NDebugger::PRODUCTION mode.
 */
gulp.task('set-production-mode', ['rename-phpstrip-back'], function (cb) {
    return gulp.src(buildDir + '/bootstrap.php').pipe(replace(
        "NDebugger::DETECT",
        "NDebugger::PRODUCTION"
    )).pipe(gulp.dest(buildDir));
});

/**
 * Builds the final ZIP in the `distDir` folder.
 */
gulp.task('zip', ['persist-plugin-comment', 'set-production-mode', 'remove-composer-files'], function (cb) {
    return gulp.src(buildDir + '/**', {dot: true}).
        pipe(rename(function (path) {
            path.dirname = 'versionpress/' + path.dirname;
        })).
        pipe(zip('versionpress-' + packageVersion + '.zip')).
        pipe(gulp.dest(distDir));
});

/**
 * After the ZIP has been built, cleans the build directory
 */
gulp.task('clean-build', ['zip'], function (cb) {
	del(['build'], cb);
});



//--------------------------------------
// Main callable tasks
//--------------------------------------

/**
 * Default task that exports production build
 */
gulp.task('default', ['clean-build']);

/**
 * Exports "nightly" build which contains the same files as production (`default`) build
 * but the version number in both plugin metadata and file name contains short Git hash,
 * e.g., "versionpress-1.0+58a96f2.zip" or "Version: 1.0+58a96f2".
 */
gulp.task('nightly', ['set-nightly-build', 'clean-build']);

/**
 * Task called from WpAutomation to copy the plugin files to the test directory
 * specified in `test-config.ini`. Basically does only the `copy` task.
 */
gulp.task('test-deploy', ['prepare-test-deploy', 'copy']);


