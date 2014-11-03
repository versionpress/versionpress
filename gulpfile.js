var gulp = require('gulp');
var del = require('del');
var shell = require('gulp-shell');
var shelljs = require('shelljs/global');
var rename = require('gulp-rename');
var zip = require('gulp-zip');
var replace = require('gulp-replace');
var fs = require('fs');
var path = require('path');

var packageVersion = "";
var buildType = '';

var buildDir = './build';
var distDir = './dist';
var vpDir = './plugins/versionpress';


gulp.task('set-nightly-build', function() {
    buildType = 'nightly';
});

gulp.task('clean', function (cb) {
    del([buildDir, distDir], cb);
});

gulp.task('copy', ['clean'], function (cb) {
    var srcOptions = {dot: true, base: vpDir};
    return gulp.src([
        vpDir + '/**',
        '!' + vpDir + '/vendor{,/**}',
        '!' + vpDir + '/tests{,/**}',
        '!' + vpDir + '/versionpress.iml',
        '!' + vpDir + '/log/**/!(.gitignore)'
    ], srcOptions).pipe(gulp.dest(buildDir));
});

gulp.task('strip-comments', ['copy'], function (cb) {
	var stripCmd = (vpDir + '/vendor/bin/strip').replace(/\//g, path.sep);

    return gulp.src(buildDir + '/**/*.php', {read: false}).
        pipe(shell([stripCmd + ' <%= file.path %> > <%= file.path %>-strip']));
});

gulp.task('rename-back', ['strip-comments'], function (cb) {
    return gulp.src(buildDir + '/**/*.php-strip').pipe(rename({extname: '.php'})).
        pipe(gulp.dest(buildDir));
});

gulp.task('remove-temp-files', ['rename-back'], function (cb) {
    del(buildDir + '/**/*.php-strip', cb);
});

gulp.task('composer-install', ['remove-temp-files'], shell.task(['composer install -d ' + buildDir + ' --no-dev --prefer-dist']));

gulp.task('remove-composer-files', ['composer-install'], function (cb) {
    del([buildDir + '/composer.json', buildDir + '/composer.lock'], cb);
});

gulp.task('persist-plugin-comment', ['rename-back'], function (cb) {
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

gulp.task('set-production-mode', ['rename-back'], function (cb) {
    return gulp.src(buildDir + '/bootstrap.php').pipe(replace(
        "NDebugger::DETECT",
        "NDebugger::PRODUCTION"
    )).pipe(gulp.dest(buildDir));
});

gulp.task('zip', ['persist-plugin-comment', 'set-production-mode', 'remove-composer-files'], function (cb) {
    return gulp.src(buildDir + '/**', {dot: true}).
        pipe(rename(function (path) {
            path.dirname = 'versionpress/' + path.dirname;
        })).
        pipe(zip('versionpress-' + packageVersion + '.zip')).
        pipe(gulp.dest(distDir));
});

gulp.task('clean-build', ['zip'], function (cb) {
	del(['build'], cb);
});

gulp.task('nightly', ['set-nightly-build', 'clean-build']);

gulp.task('default', ['clean-build']);

