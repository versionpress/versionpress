/**
 * VersionPress build script. Run `gulp` or `gulp help` to see the main tasks.
 */

var gulp = require('gulp-help')(require('gulp'));
var del = require('del');
var shell = require('gulp-shell');
var shelljs = require('shelljs/global');
var rename = require('gulp-rename');
var replace = require('gulp-replace');
var fs = require('fs');
var path = require('path');
var chalk = require('chalk');
var removeLines = require('gulp-remove-lines');
var yaml = require('yamljs');
var composer = require('gulp-composer');
var git = require('gulp-git');
var merge = require('merge-stream');
var runSequence = require('run-sequence');
var execSync = require('child_process').execSync;
var gutil = require('gulp-util');
var through = require('through2');
var archiver = require('archiver');

/**
 * Version to be displayed both in WordPress administration and used as a suffix of the generated ZIP file
 * @type {string}
 */
var packageVersion = "";

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

/**
 * Set to true for test-deploy build 
 *
 * @type {boolean}
 */
var isTestDeployBuild = false;

/**
 * Sets `buildDir` and `isTestDeployBuild` so that the copy methods copies to the WP test site.
 */
gulp.task('prepare-test-deploy', false, function () {
    var testConfigStr = fs.readFileSync(vpDir + '/tests/test-config.yml', 'utf-8');
    var testConfig = yaml.parse(testConfigStr);
    var sitePath = process.env.VP_DEPLOY_TARGET || testConfig["sites"][testConfig["test-site"]]["wp-site"]["path"];
    if (isRelative(sitePath)) {
        sitePath = vpDir + '/tests/' + sitePath;
    }
    buildDir = execSync('wp eval "echo WP_PLUGIN_DIR;"', {cwd: sitePath, encoding: 'utf8'}) + '/versionpress';

    isTestDeployBuild = true;
});

/**
 * Prepares `srcDef` that is later used in the `copy` task. The src definition
 * is slightly different for production build and test-deploy build.
 */
gulp.task('prepare-src-definition', false, function () {

    srcDef = [];

    srcDef.push(vpDir + '/**'); // add all, except:

    srcDef.push('!' + vpDir + '/.idea{,/**}');
    srcDef.push('!' + vpDir + '/.gitignore'); // this .gitignore is valid for a project, not for deployed VP
    srcDef.push('!' + vpDir + '/.editorconfig');
    srcDef.push('!' + vpDir + '/.gitattributes');
    srcDef.push('!' + vpDir + '/ruleset.xml');
    srcDef.push('!' + vpDir + '/tests{,/**}'); // tests might be useful for `test-deploy` but we don't currently need them
    srcDef.push('!' + vpDir + '/log/**/!(.gitignore)'); // keep just the .gitignore inside `log` folder
    srcDef.push('!' + vpDir + '/**/*.md'); // all Markdown files are considered documentation

    // and now some build-specific patterns:
    if (isTestDeployBuild) {
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
 * Builds frontend and copies files defined by `srcDef` to `buildDir`
 */
gulp.task('copy', false, ['clean', 'prepare-src-definition', 'frontend-build-and-deploy'], function (cb) {
    var srcOptions = {dot: true, base: vpDir};
    return gulp.src(srcDef, srcOptions).pipe(gulp.dest(buildDir));
});

/**
 * Copies files defined by `srcDef` to `buildDir` without building frontend
 */
gulp.task('copy-without-build', false, ['clean', 'prepare-src-definition', 'frontend-deploy'], function (cb) {
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
 * Fills the packageVersion variable
 */
gulp.task('fill-vp-version', false, function(cb) {

    // E.g., 2.1.1-50-g5cab646. See https://git-scm.com/docs/git-describe#_examples
    packageVersion = exec('git describe --tags', {silent: true}).output.trim();
    cb();

});


/**
 * Updated versionpress.php to contain the current package version
 */
gulp.task('update-plugin-version', false, ['fill-vp-version', 'copy'], function(cb) {

    var pluginFile = buildDir + '/versionpress.php';
    fs.readFile(pluginFile, {encoding: 'UTF-8'}, function (err, content) {

        if (err) {
            return console.log(err);
        }

        var result = content.replace(/^Version: (.*)$/m, 'Version: ' + packageVersion);
        fs.writeFile(pluginFile, result, 'utf8', function(err) {
            if (err) {
                return console.log(err);
            }
            cb();
        });

    });

});


/**
 * Builds the final ZIP in the `distDir` folder.
 */
gulp.task('zip', false, ['copy', 'disable-debugger', 'remove-composer-files', 'fill-vp-version', 'update-plugin-version'], function (cb) {
    return gulp.src(buildDir + '/**', {dot: true}).
        pipe(rename(function (path) {
            path.dirname = 'versionpress/' + path.dirname;
        })).
        pipe(compress('versionpress-' + packageVersion + '.zip')).
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
 * Inits the tests.
 */
gulp.task('init-tests', false, shell.task([
    'npm install'
], {cwd: vpDir + '/tests'}));

/**
 * Inits the frontend project.
 */
gulp.task('init-frontend', false, function () {
    var configPath = frontendDir + '/src/config/config.local.sample.ts';
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
    git.exec({args: 'config core.ignorecase false'}, function (err, stdout) {
        if (err) {
            console.log(err);
            cb();
        }
        
        git.exec({args: 'config core.filemode false'}, function (err, stdout) {
            if (err) {
                console.log(err);
                cb();
            }
        
            cb();
        });
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
 * Task called from WpAutomation to copy the plugin files to the test directory
 * specified in `test-config.yml`. Basically does only the `copy` task.
 */
gulp.task('test-deploy', 'Task called from WpAutomation to copy the plugin files to the test directory.', ['prepare-test-deploy', 'copy-without-build']);

/**
 * Inits dev environment.
 * Install vendors, set env variables.
 */
gulp.task('init-dev', 'Inits user development environment.', ['git-config', 'composer-install-ext-libs', 'composer-install-versionpress-libs', 'frontend-build-and-deploy', 'init-tests']);


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

/**
 * Compress the build into a zip file.
 * We don't use gulp-zip because of this bug: https://github.com/sindresorhus/gulp-zip/issues/79
 * Inspiration from https://github.com/sindresorhus/gulp-tar/blob/cbe4e1df44fdc477a3a9743cfb62ccb999748c16/index.js
 */
function compress(filename) {
    if (!filename) {
        return;
    }

    var firstFile;
    var archive = archiver('zip');

    return through.obj(function (file, enc, cb) {
        if (file.relative === '') {
            cb();
            return;
        }

        if (firstFile === undefined) {
            firstFile = file;
        }

        archive.append(file.contents, {
            name: file.relative.replace(/\\/g, '/') + (file.isNull() ? '/' : ''),
            mode: file.stat && file.stat.mode,
            date: file.stat && file.stat.mtime ? file.stat.mtime : null
        });

        cb();
    }, function (cb) {
        if (firstFile === undefined) {
            cb();
            return;
        }

        archive.finalize();

        this.push(new gutil.File({
            cwd: firstFile.cwd,
            base: firstFile.base,
            path: path.join(firstFile.base, filename),
            contents: archive
        }));

        cb();
    });
}