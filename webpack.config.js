const path = require("path");
const defaultConfig = require("@wordpress/scripts/config/webpack.config");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

const plugins = [];
const disablePlugins = ["cleanwebpackplugin"];

function resolve(...paths) {
  return path.resolve(__dirname, ...paths);
}

defaultConfig.plugins.forEach((item) => {
  const pluginName = item.constructor.name.toLowerCase();

  if (pluginName === "minicssextractplugin") {
    item.options.filename = "../css/[name].css";
    item.options.chunkFilename = "../css/[name].css";
    item.options.esModule = true;
  }

  if (disablePlugins.includes(pluginName)) {
    return;
  }

  plugins.push(item);
});

module.exports = {
  ...defaultConfig,

  plugins,

  module: {
    rules: [
      ...defaultConfig.module.rules,
      {
        test: /\.(bmp|png|jpe?g|gif)$/i,
        type: "asset/resource",
        generator: {
          filename: "../images/[name].[hash:8][ext]",
        },
      },
    ],
  },

  output: {
    filename: "[name].js",
    path: resolve("assets", "js"),
  },
};
