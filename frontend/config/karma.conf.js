'use strict';

var webpackConfig = require('./webpack.config.js')(true);

module.exports = function(config) {
  config.set({

		browsers: ['Chrome'],

		frameworks: ['mocha', 'chai-sinon'],

		singleRun: false,

		files: [
			'./webpack-test.js'
		],

		preprocessors: {
			'./webpack-test.js': ['webpack', 'sourcemap']
		},

		reporters: ['dots'],

		webpack: {
			devtool: 'inline-source-map',
			module: webpackConfig.module,
			resolve: webpackConfig.resolve
		},

		webpackServer: {
			noInfo: true
		},

		plugins: [
			require('karma-webpack'),
			'karma-mocha',
			'karma-chai-sinon',
			'karma-chrome-launcher',
			'karma-sourcemap-loader'
		]
  });
};
