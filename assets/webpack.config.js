const path                 = require( 'path' );
const defaultConfig        = require( '@wordpress/scripts/config/webpack.config' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const plugins              = [];

function resolve( ...paths ) {
	return path.resolve( __dirname, ...paths );
}

defaultConfig.plugins.forEach( ( item ) => {
	if ( item instanceof MiniCssExtractPlugin ) {
		item.options.filename      = '../css/[name].css';
		item.options.chunkFilename = '../css/[name].css';
		item.options.esModule      = true;
	}

	plugins.push( item );
} );

module.exports = {
	...defaultConfig,

	plugins,

	output: {
		filename: '[name].js',
		path: resolve( 'assets', 'js' ),
	},
};