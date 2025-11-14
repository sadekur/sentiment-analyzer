const path = require('path');

module.exports = (env, argv) => ({
    mode: argv.mode || "development",

    entry: {
        admin: path.resolve(__dirname, "spa/admin/settings/src/App.jsx"),
        public: path.resolve(__dirname, "spa/public/shortcodes/src/App.jsx"),
        tailwind: path.resolve(__dirname, "assets/common/css/tailwind.css"),
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
                    loader: 'postcss-loader',
                    options: {
                        postcssOptions: {
                            ident: "postcss",
                            plugins: [
                                require("tailwindcss"),
                                require("autoprefixer"),
                            ],
                        },
                    },
                },
            },
        ],
    },
    resolve: {
        extensions: ['.js', '.jsx'],
    },
});
