const defaults = require("@wordpress/scripts/config/webpack.config");
const webpack = require("webpack");

/**
 * WP-Scripts Webpack config.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-scripts/#provide-your-own-webpack-config
 */
module.exports = {
    ...defaults,
    entry: {
        analytics: "./src/analytics/index.js",
        settings: "./src/settings/index.js",
        siteimprove: "./src/siteimprove/index.js",
    },
    plugins: [
        ...defaults.plugins,
        new webpack.ProvidePlugin({
            $: "jquery",
            jQuery: "jquery",
        }),
    ],
};
