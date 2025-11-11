    const path = require('path');

    module.exports = {
        mode: argv.mode || "development",
        // entry: '.spa/src/App.js',
        // output: {
        //     filename: 'bundle.js',
        //     path: path.resolve(__dirname, 'build'),
        // },
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
                // Add rules for CSS, images, etc., as needed
            ],
        },
        resolve: {
            extensions: ['.js', '.jsx'],
        },
        // Add plugins as needed (e.g., CleanWebpackPlugin, CopyWebpackPlugin)
    };