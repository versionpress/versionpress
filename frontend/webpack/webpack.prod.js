const path = require('path');
const webpack = require('webpack');
const UglifyJsPlugin = require("uglifyjs-webpack-plugin");
const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");

module.exports = () => {
    let config = {
        mode: 'production',
        entry: {
            app: path.join(process.cwd(), './src/main.tsx'),
        },
        output: {
            publicPath: './',
        },
        optimization: {
            minimizer: [
                new UglifyJsPlugin({
                    cache: true,
                    parallel: true,
                    sourceMap: true,
                }),
                new OptimizeCSSAssetsPlugin({}),
            ],
        },
    };

    return require('./webpack.base')(false, config);
};
