'use strict';

var ExtractTextPlugin = require('extract-text-webpack-plugin');
var path = require('path');
var webpack = require('webpack');

module.exports = function (isDevelopment) {
  var entry = {
    app: isDevelopment ? [
      'webpack-dev-server/client?http://localhost:8888',
      'webpack/hot/only-dev-server',
      './src/main.tsx'
    ] : [
      './src/main.tsx'
    ]
  };

  var loaders = [
    {
      exclude: /node_modules/,
      loaders: ['react-hot','ts-loader'],
      test: /\.tsx?$/
    },
    {
      loader: 'url-loader?limit=32768',
      test: /\.(gif|jpg|png|woff|woff2|eot|ttf|svg)(\?.*)?$/
    }
  ];

  var autoprefixerLoader = 'autoprefixer-loader?' +
    '{browsers:["Chrome >= 20", "Firefox >= 24", "Explorer >= 8", "Opera >= 12", "Safari >= 6"]}';
  var cssLoader = 'css-loader!' + autoprefixerLoader;
  var stylesheetLoaders = {
    'css': cssLoader,
    'less': cssLoader + '!less-loader'
  };

  var output = isDevelopment ? {
    path: path.join(__dirname, 'build'),
    filename: '[name].js',
    publicPath: 'http://localhost:8888/build/'
  } : {
    path: 'build/',
    filename: '[name].js'
  };

  var plugins = [
    new webpack.DefinePlugin({
      'process.env': {
        NODE_ENV: JSON.stringify(isDevelopment ? 'development' : 'production'),
        IS_BROWSER: true
      }
    }),
    new webpack.IgnorePlugin(/^\.\/locale$/, [/moment$/]) // http://stackoverflow.com/a/25426019/1243495
  ];
  if (isDevelopment) {
    plugins.push(
      new webpack.HotModuleReplacementPlugin(),
      new webpack.NoErrorsPlugin()
    );
  } else {
    plugins.push(
      new ExtractTextPlugin('app.css', {
        allChunks: true
      }),
      new webpack.optimize.DedupePlugin(),
      new webpack.optimize.OccurenceOrderPlugin(),
      new webpack.optimize.UglifyJsPlugin({
        compress: {
          warnings: false
        }
      })
    );
  }

  loaders = loaders.concat(
    Object.keys(stylesheetLoaders).map(function (ext) {
      var loader = isDevelopment
        ? 'style-loader!' + stylesheetLoaders[ext]
        : ExtractTextPlugin.extract('style-loader', stylesheetLoaders[ext]);
      return {
        loader: loader,
        test: new RegExp('\\.(' + ext + ')$')
      };
    })
  );

  return {
    cache: isDevelopment,
    debug: isDevelopment,
    devtool: isDevelopment ? 'inline-source-map' : false,
    entry: entry,
    module: {
      loaders: loaders
    },
    output: output,
    plugins: plugins,
    resolve: {
      extensions: ['', '.js', '.json', '.ts', '.tsx']
    }
  }

};
