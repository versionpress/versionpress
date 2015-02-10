var gulp = require('gulp');
var del = require('del');
var fs = require('fs');
var browserSync = require('browser-sync');
var gutil = require('gulp-util');
var watch = require('gulp-watch');
var runSequence = require('run-sequence');

var source = "./content/**";
var destination = "../VersionPress-docssite/VersionPress.DocsSite/App_Data/content";

/**
 * Default task reports usage only
 */
gulp.task('default', function (cb) {
    gutil.log('');
    gutil.log('Usage:');
    gutil.log(' ' + gutil.colors.green('gulp copy-docs') + ' Copies docs to docssite');
    gutil.log(' ' + gutil.colors.green('gulp watch') + '     Copies docs on every change, plus BrowserSync');
    gutil.log('');
});


gulp.task('clean', function(cb) {
  del(destination, {force: true}, cb);
});

gulp.task('copy-docs', ['clean'], function() {
  return gulp.src(source, { dot: true }).pipe(gulp.dest(destination));
});

gulp.task('watch', ['browser-sync'], function() {
  watch(source, function() {
      runSequence('copy-docs', browserSync.reload);
  });
});

gulp.task('browser-sync', function() {
  browserSync({
      proxy: 'http://localhost:1515',
      port: 1516,
      notify: false
  });
});
