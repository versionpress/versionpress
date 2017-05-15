const CheckerPlugin = require('awesome-typescript-loader').CheckerPlugin;
const CopyWebpackPlugin = require('copy-webpack-plugin');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
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
        new ExtractTextPlugin({
            filename: '[name].css',
            // ExtractTextPlugin doesn't support hot reloading and is disabled in dev mode;
            // see the 'fallback' attribute below
            disable: isDevelopment,
        }),
        new webpack.NamedModulesPlugin(),
    ].concat(isDevelopment
        ?  [
            new webpack.HotModuleReplacementPlugin(),
            new webpack.NoEmitOnErrorsPlugin(),
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
                    fallback: 'style-loader?sourceMap',
                    use: [
                        'css-loader?sourceMap',
                        {
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
                      },
                      'less-loader?sourceMap',
                    ]
                }),
            },
        ].concat(options.module && options.module.rules || []),
    },
    devServer: options.devServer,
});
