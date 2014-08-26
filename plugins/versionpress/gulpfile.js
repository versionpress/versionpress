var gulp = require('gulp');
var del = require('del');
var shell = require('gulp-shell');
var rename = require('gulp-rename');
var zip = require('gulp-zip');
var fs = require('fs');
var replace = require('gulp-replace');

gulp.task('clean', function(cb) {
  del(['build', 'dist'], cb);
});

gulp.task('copy', ['clean'], function(cb) {
  return gulp.src([
    './**', 
    '!./{node_modules,node_modules/**}',
    '!./{vendor,vendor/**}',
    '!./{spec,spec/**}',
    '!./{tests,tests/**}',
    '!versionpress.iml',
    '!gulpfile.js',
    '!package.json',
    ], {dot: true}).pipe(gulp.dest('./build'));
});

gulp.task('strip-comments', ['copy'], function(cb) {
  return gulp.src('./build/**/*.php', {read: false}).
    pipe(shell(['vendor\\bin\\strip <%= file.path %> > <%= file.path %>-strip']));
});

gulp.task('rename-back', ['strip-comments'], function(cb) {
  return gulp.src('./build/**/*.php-strip').pipe(rename({extname: '.php'})).
  pipe(gulp.dest('./build'));
});

gulp.task('remove-temp-files', ['rename-back'], function(cb) {
  del('./build/**/*.php-strip', cb);
});

gulp.task('composer-install', ['remove-temp-files'], shell.task(['composer install -d build --no-dev --prefer-dist']));

gulp.task('persist-plugin-comment', ['rename-back'], function(cb) {
  var fileOptions = {encoding: 'UTF-8'};
  fs.readFile('./versionpress.php', fileOptions, function (err, content) {
    var definePosition = content.indexOf("define");
    var originalHead = content.substr(0, definePosition);
    
    fs.readFile('./build/versionpress.php', fileOptions, function (err, content) {
      var definePosition = content.indexOf("define");
      var newContent = originalHead + content.substr(definePosition);
      fs.writeFile('./build/versionpress.php', newContent, fileOptions, function (err) {
        cb();
      });
    });
  });
});

gulp.task('set-production-mode', ['rename-back'], function (cb) {
  return gulp.src('./build/_db.php').pipe(replace(
    "NDebugger::DETECT",
    "NDebugger::PRODUCTION"
    )).pipe(gulp.dest('./build'));
});

gulp.task('zip', ['remove-temp-files', 'persist-plugin-comment', 'set-production-mode'], function(cb) {
  return gulp.src('./build/**', {dot: true}).
    pipe(rename(function (path) {
        path.dirname = 'versionpress/' + path.dirname;
    })).
    pipe(zip('versionpress-1.0-alpha1.zip')).
    pipe(gulp.dest('./dist'));
});

gulp.task('default', ['zip']);