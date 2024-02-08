const defaultConfig = require("@wordpress/scripts/config/webpack.config");
const webpack = require("webpack");
const { basename, dirname, resolve } = require("path");
const srcDir = "src";
const settings = resolve(process.cwd(), "src", "settings");
const siteimprove = resolve(process.cwd(), "src", "siteimprove");
const analytics = resolve(process.cwd(), "src", "analytics");

module.exports = {
    ...defaultConfig,
    entry: {
        settings,
        siteimprove,
        analytics,
    },
    output: {
        path: resolve(process.cwd(), "build"),
        filename: "[name].js",
        clean: true,
    },
    optimization: {
        ...defaultConfig.optimization,
        splitChunks: {
            cacheGroups: {
                style: {
                    type: "css/mini-extract",
                    test: /[\\/]style(\.module)?\.(pc|sc|sa|c)ss$/,
                    chunks: "all",
                    enforce: true,
                    name(_, chunks, cacheGroupKey) {
                        const chunkName = chunks[0].name;
                        return `${dirname(chunkName)}/${basename(
                            chunkName
                        )}.${cacheGroupKey}`;
                    },
                },
                default: false,
            },
        },
    },
    plugins: [
        ...defaultConfig.plugins,
        new webpack.ProvidePlugin({
            $: "jquery",
            jQuery: "jquery",
        }),
    ],
};