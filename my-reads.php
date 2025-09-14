<?php

/**
 * Plugin Name:       My Reads
 * Plugin URI: 		  https://github.com/n8finch/my-reads
 * Description:       A plugin to allow users to track their reading list.
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Version:           0.2.6
 * Author:            Nate Finch
 * Author URI:        https://n8finch.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       my-reads
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Set up constants
 */
if ( ! defined( 'MYREADS_PLUGIN_VERSION' ) ) {
    define( 'MYREADS_PLUGIN_VERSION', '0.2.3' );
}

if ( ! defined( 'MYREADS_PLUGIN_FILE' ) ) {
    define( 'MYREADS_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'MYREADS_PATH' ) ) {
    define( 'MYREADS_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
}

if ( ! defined( 'MYREADS_URL' ) ) {
    define( 'MYREADS_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
}

// Bootstrap the plugin.
require_once MYREADS_PATH . '/includes/bootstrap.php';

// Register the custom post type, taxonomies, and settings.
require_once MYREADS_PATH . '/includes/custom-post-types-taxonomies.php';
require_once MYREADS_PATH . '/includes/my-reads-settings-page.php';

// Register custom blocks and meta.
require_once MYREADS_PATH . '/includes/register-blocks.php';
require_once MYREADS_PATH . '/includes/register-meta.php';

// Register patterns.
require_once MYREADS_PATH . '/includes/register-patterns.php';

/// Register the single template.
require_once MYREADS_PATH . '/includes/register-templates.php';

// Enqueue assets.
require_once MYREADS_PATH . '/includes/enqueue-assets.php';

// Add API Endpoints
require_once MYREADS_PATH . '/includes/api/myreads-all-reads-endpoint.php';
require_once MYREADS_PATH . '/includes/api/myreads-amazon-info-endpoint.php';
