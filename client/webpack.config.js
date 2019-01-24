const path = require('path');
const webpack = require('webpack');

module.exports = {
    entry: './src/main.js',
    mode: 'development',
    output: {
        filename: 'bundle.js',
        path: path.resolve(__dirname, 'webroot')
    },
    module: {
        rules: [
            {
                test: /\.css$/,
                use: [
                    { loader: 'style-loader' },
                    { loader: 'css-loader' }
                ]
            },
            {
                test: /\.(png|jpg)$/,
                use: [
                    { loader: 'url-loader', options: { limit: 10000 } }
                ]
            }
        ]
    },
    resolve: {
        modules: [path.resolve(__dirname, '/client'), 'node_modules'],
        descriptionFiles: ['package.json']
    }
};
