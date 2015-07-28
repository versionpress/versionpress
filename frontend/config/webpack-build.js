'use strict';

var webpack = require('webpack');
var webpackConfig = require('./webpack.config.js')(false);
var gutil = require('gulp-util');

process.env.NODE_ENV = 'production';

webpack(webpackConfig, function (fatalError, stats) {
  var jsonStats = stats.toJson();
  var buildError = fatalError || jsonStats.errors[0] || jsonStats.warnings[0];

  if (buildError) {
    throw new gutil.PluginError('webpack', buildError);
  }

  gutil.log('[webpack]', stats.toString({
    colors: true,
    version: false,
    hash: false,
    timings: false,
    chunks: false,
    chunkModules: false
  }));
});
