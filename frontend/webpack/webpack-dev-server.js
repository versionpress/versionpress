'use strict';

const WebpackDevServer = require('webpack-dev-server');
const webpack = require('webpack');
const path = require('path');
const argv = require('minimist')(process.argv.slice(2));

process.env.NODE_ENV = 'development';

let env = argv.env;
env.port = env.port || 8888;
const config = require(path.join(process.cwd(), argv.config))(env);

new WebpackDevServer(webpack(config), {
    hot: true,
    historyApiFallback: true,
    publicPath: config.output.publicPath,
    quiet: false,
    noInfo: false,
    stats: {
        assets: true,
        chunkModules: false,
        chunks: true,
        colors: true,
        hash: true,
        timings: true,
        version: true,
    },
}).listen(env.port, 'localhost', function (err) {
    if (err) {
        return console.error(err);
    }
    console.log(`Listening at http://localhost:${env.port}/`);
});
