'use strict';

module.exports = function(config) {
  config.set({

		//browsers: ['PhantomJS', 'Firefox', 'Chrome'],
		browsers: ['PhantomJS'],

		frameworks: ['mocha', 'chai'],

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
			module: {
				loaders: [
					{ test: /\.ts$/, loader: 'ts-loader' }
				]
			},
      resolve: {
        extensions: ['', '.js', '.json', '.ts']
      }
		},

		webpackServer: {
			noInfo: true
		},

		plugins: [
      require('karma-webpack'),
			'karma-mocha',
			'karma-chai',
			'karma-phantomjs-launcher',
			'karma-chrome-launcher',
			'karma-firefox-launcher',
			'karma-sourcemap-loader'
		]
  });
};
