const path = require('path');
const webpack = require('webpack');

module.exports = () => {
    let config = {
        entry: {
            app: path.join(process.cwd(), './src/main.tsx'),
        },
        output: {
            publicPath: './',
        },
    };

    return require('./webpack.base')(false, config);
};
