const path = require('path');

module.exports = (env) => {
    const config = {
        entry: {
            app: [
                path.join(process.cwd(), './src/main.tsx'),
                `webpack-dev-server/client?http://localhost:${env.port}`,
                'webpack/hot/only-dev-server',
            ],
        },
        output: {
            publicPath: `http://localhost:${env.port}/static/`,
        },
    }

    return require('./webpack.base')(true, config);
};
