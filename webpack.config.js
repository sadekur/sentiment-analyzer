const path = require('path');

module.exports = (env, argv) => ({
    mode: argv.mode || "development",

    entry: {
        admin: path.resolve(__dirname, "spa/admin/settings/src/App.jsx"),
        public: path.resolve(__dirname, "spa/public/shortcodes/src/App.jsx"),
    },
    output: {
        filename: "[name].bundle.js",
        path: path.resolve(__dirname, "build"),
    },
    module: {
        rules: [
            {
                test: /\.(js|jsx)$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@babel/preset-env', '@babel/preset-react'],
                    },
                },
            },
        ],
    },
    resolve: {
        extensions: ['.js', '.jsx'],
    },
});
