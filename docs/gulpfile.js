var gulp = require('gulp');
var del = require('del');
var fs = require('fs');

var source = "./content/**";
var destination = "../VersionPress-docssite/VersionPress.DocsSite/App_Data/content";
//var destination = "./testcopy";


gulp.task('clean', function(cb) {
  del(destination, {force: true}, cb);
});

gulp.task('copy-contents', ['clean'], function() {
  return gulp.src(source, { dot: true }).pipe(gulp.dest(destination));
});

/*
// .changed file created this way was a problem for `gulp watch` - there
// were many cases where the clean task reported unlink errors just on this file
// So right now we copy the .changed file straight from the sources where it has
// been added.
gulp.task('create-changed-file', ['copy-contents'], function(cb) {
  fs.openSync(destination + "/.changed", 'w');
  cb();
});
*/

gulp.task('copy-docs', ['copy-contents'], function() {});

gulp.task('watch', function() {
  gulp.watch(source, ['copy-docs']);
});

gulp.task('default', ['copy-docs'], function() {});