/**
 * External dependencies
 */
const RemoveEmptyScriptsPlugin = require( 'webpack-remove-empty-scripts' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

/**
 * Add files are exported to in the /build folder
 */
module.exports = {
	...defaultConfig,
	entry: {
		...defaultConfig.entry(),
		'slotfill-my-reads/index': path.resolve(
			process.cwd(),
			'/includes/js/my-reads-cpt-slotfill-settings.js'
		),
	},
	plugins: [ ...defaultConfig.plugins, new RemoveEmptyScriptsPlugin() ],
};
