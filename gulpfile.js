/**
 * VersionPress build script. See the end of the file for main callable tasks.
 */

var gulp = require('gulp-help')(require('gulp'));
var del = require('del');
var shell = require('gulp-shell');
var shelljs = require('shelljs/global');
var rename = require('gulp-rename');
var zip = require('gulp-zip');
var replace = require('gulp-replace');
var fs = require('fs');
var path = require('path');
var chalk = require('chalk');
var removeLines = require('gulp-remove-lines');
var neon = require('neon-js');
var composer = require('gulp-composer');
var git = require('gulp-git');
var merge = require('merge-stream');
var runSequence = require('run-sequence');


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
 * like `composer install` etc. are run on them.
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
 * Frontend source directory
 *
 * @type {string}
 */
var frontendDir = './frontend';

/**
 * Frontend destination directory
 *
 * @type {string}
 */
var adminGuiDir = vpDir + '/admin/public/gui';

/**
 * Array of globs that will be used in gulp.src() to copy files from source to destination.
 * Prepared in `prepare-src-definition`.
 *
 * @type {Array}
 */
var srcDef = [];


gulp.task('set-nightly-build', false, function () {
    buildType = 'nightly';
});

/**
 * Sets `buildDir` and `buildType` so that the copy methods copies to the WP test site.
 */
gulp.task('prepare-test-deploy', false, function () {
    var testConfigStr = fs.readFileSync(vpDir + '/tests/test-config.neon', 'utf-8');
    var testConfig = neon.decode(testConfigStr).toObject(true);
    var sitePath = process.env.VP_DEPLOY_TARGET || testConfig["sites"][testConfig["test-site"]]["wp-site"]["path"];
    if (isRelative(sitePath)) {
        sitePath = vpDir + '/tests/' + sitePath;
    }
    buildDir = sitePath + "/wp-content/plugins/versionpress";

    buildType = 'test-deploy'; // later influences the `prepare-src-definition` task
});

/**
 * Prepares `srcDef` that is later used in the `copy` task. The src definition
 * is slightly different for various buildTypes.
 */
gulp.task('prepare-src-definition', false, function () {

    srcDef = [];

    srcDef.push(vpDir + '/**'); // add all, except:

    srcDef.push('!' + vpDir + '/.idea{,/**}');
    srcDef.push('!' + vpDir + '/.gitignore'); // this .gitignore is valid for a project, not for deployed VP
    srcDef.push('!' + vpDir + '/.editorconfig');
    srcDef.push('!' + vpDir + '/.gitattributes');
    srcDef.push('!' + vpDir + '/tests{,/**}'); // tests might be useful for `test-deploy` but we don't currently need them
    srcDef.push('!' + vpDir + '/log/**/!(.gitignore)'); // keep just the .gitignore inside `log` folder
    srcDef.push('!' + vpDir + '/**/*.md'); // all Markdown files are considered documentation

    // and now some build-specific patterns:
    if (buildType == 'test-deploy') {
        srcDef.push('!' + vpDir + '/composer.json');
        srcDef.push('!' + vpDir + '/composer.lock');
        srcDef.push('!' + vpDir + '/vendor/**/.git{,/**}'); // keep vendor but ignore all nested Git repositories in it
    } else {
        srcDef.push('!' + vpDir + '/vendor{,/**}'); // `vendor` is fresh-generated later in the `composer-install` task
        // keep composer.json|lock
    }

});

/**
 * Cleans buildDir and distDir
 */
gulp.task('clean', false, function (cb) {
    return del([buildDir, distDir], {force: true});
});


/**
 * Copies files defined by `srcDef` to `buildDir`
 */
gulp.task('copy', false, ['clean', 'prepare-src-definition', 'frontend-deploy'], function (cb) {
    var srcOptions = {dot: true, base: vpDir};
    return gulp.src(srcDef, srcOptions).pipe(gulp.dest(buildDir));
});

/**
 * Builds the frontend.
 */
gulp.task('frontend-build', false, ['init-frontend'], shell.task([
    'npm run build'
], {cwd: frontendDir}));

/**
 * Deploys the frontend.
 */
gulp.task('frontend-deploy', false, function (cb) {
    var src = frontendDir + '/build/**';
    var srcOptions = {dot: true};
    return gulp.src(src, srcOptions).pipe(gulp.dest(adminGuiDir));
});

/**
 * Builds and deploys the frontend.
 */
gulp.task('frontend-build-and-deploy', false, function (cb) {
    runSequence('frontend-build', 'frontend-deploy', cb);
});

/**
 * Installs Composer packages, ignores dev packages prefers dist ones
 */
gulp.task('composer-install', false, ['copy'], shell.task(['composer install -d ' + buildDir + ' --no-dev --prefer-dist --ignore-platform-reqs --optimize-autoloader']));

/**
 * Removes composer.json|lock after the `composer-install` task is done
 */
gulp.task('remove-composer-files', false, ['composer-install'], function (cb) {
    return del([buildDir + '/composer.json', buildDir + '/composer.lock']);
});

/**
 * Disables the debugger because we don't want to handle all exceptions and errors caused by all plugins. See WP-268.
 */
gulp.task('disable-debugger', false, ['copy'], function (cb) {
    return gulp.src(buildDir + '/bootstrap.php').pipe(removeLines(
        {filters: [/^Debugger::enable/]}
    )).pipe(gulp.dest(buildDir));
});

/**
 * Fills VersionPress version
 */

gulp.task('fill-vp-version', false, function(cb){
    var fileOptions = {encoding: 'UTF-8'};
    fs.readFile(vpDir + '/versionpress.php', fileOptions, function (err, content) {
        var versionMatch = content.match(/^Version: (.*)$/m);
        packageVersion = versionMatch[1];
        if (buildType == 'nightly') {
            var gitCommit = exec('git rev-parse --short HEAD', {silent: true}).output.trim(); // trims the "\n" from the end
            packageVersion += '+' + gitCommit;
        }
        cb();
    })

})

/**
 * Builds the final ZIP in the `distDir` folder.
 */
gulp.task('zip', false, ['copy', 'disable-debugger', 'remove-composer-files', 'fill-vp-version'], function (cb) {
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
gulp.task('clean-build', false, ['zip'], function (cb) {
    return del(['build']);
});

/**
 * Installs Composer external libs.
 */
gulp.task('composer-install-ext-libs', false, function () {
    return composer('update', {cwd: './ext-libs', bin: 'composer', 'ignore-platform-reqs': true});
});

/**
 * Installs Composer libs.
 */
gulp.task('composer-install-versionpress-libs', false, function () {
    return composer({cwd: './plugins/versionpress', bin: 'composer', 'ignore-platform-reqs': true});
});

/**
 * Inits the frontend project.
 */
gulp.task('init-frontend', false, function () {
    var configPath = frontendDir + '/src/config.local.sample.ts';
    var targetName = configPath.replace('.sample', '');
    if (fs.existsSync(targetName)) {
        targetName = configPath;
    }

    return gulp.src(configPath)
        .pipe(rename(targetName))
        .pipe(gulp.dest('.'))
        .pipe(shell([
            'npm install'
        ], {cwd: frontendDir}));
});

/**
 * Sets git to be case sensitive.
 */
gulp.task('git-config', false, function (cb) {
    return git.exec({args: 'config core.ignorecase false'}, function (err, stdout) {
        if (err) {
            console.log(err);
        }
        cb();
    });
});


//--------------------------------------
// Main callable tasks
//--------------------------------------

gulp.task('default', false, ['help'], function () {
});


/**
 * Task that exports production build
 */
gulp.task('build', 'Task that exports production build', ['clean-build'], function () {
    console.log(" ");
    console.log("Build ready: " + chalk.white.bold.bgGreen("versionpress-" + packageVersion + ".zip"));
    console.log(" ");
});

/**
 * Exports "nightly" build which contains the same files as production (`build`) build
 * but the version number in both plugin metadata and file name contains short Git hash,
 * e.g., "versionpress-1.0+58a96f2.zip" or "Version: 1.0+58a96f2".
 */
gulp.task('nightly-build', 'Nightly build from current Git revision. Short hash is added to ZIP name.', ['set-nightly-build', 'clean-build']);

/**
 * Task called from WpAutomation to copy the plugin files to the test directory
 * specified in `test-config.neon`. Basically does only the `copy` task.
 */
gulp.task('test-deploy', 'Task called from WpAutomation to copy the plugin files to the test directory.', ['prepare-test-deploy', 'copy']);

/**
 * Inits dev environment.
 * Install vendors, set env variables.
 */
gulp.task('init-dev', 'Inits user development environment.', ['git-config', 'composer-install-ext-libs', 'composer-install-versionpress-libs', 'frontend-build-and-deploy']);


//--------------------------------------
// IDE tasks
//--------------------------------------

/**
 * Setup project files for IDEA / PhpStorm
 */
gulp.task('idea', 'Setup project files for IDEA / PhpStorm', function () {
    var ideaProjects = [
        {src: './.ide-tpl/.idea-versionpress/**', dest: vpDir + '/.idea'},
        {src: './.ide-tpl/.idea-frontend/**', dest: frontendDir + '/.idea'}
    ];

    var streams = ideaProjects.map(function (project) {
        return gulp.src(project.src, {dot: true}).pipe(gulp.dest(project.dest));
    });

    return merge.apply(null, streams);
});

//--------------------------------------
// Helper functions
//--------------------------------------

function isRelative(sitePath) {
    return path.normalize(sitePath) != path.resolve(sitePath);
}