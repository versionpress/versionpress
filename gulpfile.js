var gulp = require('gulp');
var del = require('del');
var shell = require('gulp-shell');
var rename = require('gulp-rename');
var zip = require('gulp-zip');
var replace = require('gulp-replace');
var fs = require('fs');
var path = require('path');

var packageVersion = "";

var buildDir = './build';
var distDir = './dist';
var vpDir = './plugins/versionpress';

gulp.task('clean', function (cb) {
    del([buildDir, distDir], cb);
});

gulp.task('copy', ['clean'], function (cb) {
    return gulp.src([
        vpDir + '/**',
        '!' + vpDir + '/{node_modules,node_modules/**}',
        '!' + vpDir + '/{vendor,vendor/**}',
        '!' + vpDir + '/{spec,spec/**}',
        '!' + vpDir + '/{tests,tests/**}',
        '!' + vpDir + '/versionpress.iml',
        '!' + vpDir + '/gulpfile.js',
        '!' + vpDir + '/package.json'
    ], {dot: true, base: vpDir}).pipe(gulp.dest(buildDir));
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

        fs.readFile(buildDir + '/versionpress.php', fileOptions, function (err, content) {
            var definePosition = content.indexOf("define");
            var newContent = originalHead + content.substr(definePosition);
            fs.writeFile(buildDir + '/versionpress.php', newContent, fileOptions, function (err) {
                cb();
            });
        });
    });
});

gulp.task('set-production-mode', ['rename-back'], function (cb) {
    return gulp.src(buildDir + '/_db.php').pipe(replace(
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

gulp.task('default', ['clean-build']);