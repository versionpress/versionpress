'use strict';

const ExtractTextPlugin = require('extract-text-webpack-plugin');
const path = require('path');
const webpack = require('webpack');
const autoprefixer = require('autoprefixer');

module.exports = function (isDevelopment) {

  return {
    entry: {
      app: isDevelopment ? [
        'webpack-dev-server/client?http://localhost:8888',
        'webpack/hot/only-dev-server',
        './src/main.tsx'
      ] : [
        './src/main.tsx'
      ]
    },
    output: {
      path: path.join(__dirname, '..', 'build'),
      filename: '[name].js',
      publicPath: isDevelopment ? 'http://localhost:8888/' : ''
    },

    cache: isDevelopment,
    devtool: isDevelopment ? 'source-map' : '',

    plugins: [
      new webpack.NamedModulesPlugin(),
      new webpack.DefinePlugin({
        'process.env': {
          NODE_ENV: JSON.stringify(isDevelopment ? 'development' : 'production'),
          IS_BROWSER: true
        }
      }),
      new ExtractTextPlugin({filename: 'app.css', allChunks: true, disable: isDevelopment}),
      // new webpack.IgnorePlugin(/^\.\/locale$/, [/moment$/]), // http://stackoverflow.com/a/25426019/1243495
      // new webpack.LoaderOptionsPlugin({
      //   debug: isDevelopment
      // }),
    ].concat(isDevelopment ? [
      new webpack.HotModuleReplacementPlugin(),
      new webpack.NoEmitOnErrorsPlugin()
    ] : [
      new webpack.optimize.DedupePlugin(),
      new webpack.optimize.OccurrenceOrderPlugin(),
      new webpack.optimize.UglifyJsPlugin({
        compress: {
          warnings: false
        }
      })
    ]),

    module: {
      rules: [
        // {
        //   test: /\.tsx?$/,
        //   enforce: 'pre',
        //   exclude: /node_modules/,
        //   use: 'tslint-loader',
        // },
        {
          test: /\.tsx?$/,
          exclude: /node_modules/,
          use: isDevelopment
            ? ['react-hot-loader', 'awesome-typescript-loader']
            : ['awesome-typescript-loader'],
        },
        {
          test: /\.woff2?$|\.ttf$|\.eot$/,
          use: [{
            loader: 'file-loader',
            options: {
              name: 'fonts/[name].[ext]',
            },
          }],
        },
        {
          test: /\.(gif|jpg|png|svg)$/,
          use: [{
            loader: 'file-loader',
            options: {
              name: 'images/[name].[ext]',
            },
          }],
        },
        {
          test: /\.css$/,
          use: ['style-loader', 'css-loader'],
        },
        {
          test: /\.less$/,
          use: ExtractTextPlugin.extract({
            fallback: "style-loader",
            use: ["css-loader", "less-loader"]
          })
        }

      ]
    },

    resolve: {
      extensions: ['.ts', '.tsx', '.js']
    }
  }

};
