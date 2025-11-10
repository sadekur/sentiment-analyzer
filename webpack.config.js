    const path = require('path');

    module.exports = {
        entry: '.spa/src/App.js', // Your main React entry file
        output: {
            filename: 'bundle.js',
            path: path.resolve(__dirname, 'build'), // Output directory for compiled assets
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