'use strict';

var gulp = require('gulp');
var tslint = require('gulp-tslint');

gulp.task('tslint', function() {
  return gulp.src('src/**/*.ts{,x}')
    .pipe(tslint({
      formatter: "verbose"
    }))
    .pipe(tslint.report());
});
