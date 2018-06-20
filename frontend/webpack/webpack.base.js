const CheckerPlugin = require('awesome-typescript-loader').CheckerPlugin;
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const path = require('path');
const webpack = require('webpack');
const autoprefixer = require('autoprefixer');

const baseDir = process.cwd();

const postCssLoader = {
    loader: 'postcss-loader',
    options: {
        plugins: function () {
            return [autoprefixer({
                browsers: [
                    '>1%',
                    'last 4 versions',
                    'Firefox ESR',
                    'not ie < 9', // React doesn't support IE8 anyway
                ],
            })];
        },
    },
};

module.exports = (isDevelopment, options) => ({
    mode: options.mode,
    optimization: options.optimization,
    entry: options.entry,
    output: {
        filename: '[name].js',
        path: path.join(baseDir, 'build'),
        publicPath: options.output && options.output.publicPath || '/static/',
    },
    cache: isDevelopment,
    devtool: isDevelopment ? 'source-map' : '',
    plugins: [
        new CheckerPlugin(),
        new MiniCssExtractPlugin({
            filename: '[name].css'
        }),
        new webpack.IgnorePlugin(/^\.\/locale$/, /moment$/), // http://stackoverflow.com/a/25426019/1243495
        new webpack.NamedModulesPlugin(),
    ].concat(isDevelopment
        ?  [
            new webpack.HotModuleReplacementPlugin(),
        ] : []
    ).concat(options.plugins || []),
    resolve: {
        extensions: ['.ts', '.tsx', '.js'],
    },
    module: {
        rules: [
            {
                test: /\.tsx?$/,
                exclude: /node_modules/,
                use: isDevelopment
                    ? [
                          {
                              loader: 'babel-loader',
                              options: {
                                  babelrc: true,
                                  plugins: ['react-hot-loader/babel'],
                              },
                          },
                          'awesome-typescript-loader',
                    ]
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
                use: [
                    isDevelopment ? 'style-loader?sourceMap' : MiniCssExtractPlugin.loader,
                    'css-loader?sourceMap',
                    postCssLoader,
                    'less-loader?sourceMap',
                ],
            },
        ].concat(options.module && options.module.rules || []),
    },
    devServer: options.devServer,
});
