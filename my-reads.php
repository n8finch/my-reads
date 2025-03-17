<?php

/**
 * Plugin Name:       My Reads
 * Plugin URI: 				https://n8finch.com
 * Description:       A plugin to allow users to track their reading list.
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Version:           0.2.0
 * Author:            Nate Finch
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
if ( ! defined( 'MY_READS_PLUGIN_FILE' ) ) {
    define( 'MY_READS_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'MY_READS_PATH' ) ) {
    define( 'MY_READS_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
}

if ( ! defined( 'MY_READS_URL' ) ) {
    define( 'MY_READS_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
}

// Bootstrap the plugin.
require_once MY_READS_PATH . '/includes/bootstrap.php';

// Register the custom post type, taxonomies, and settings.
require_once MY_READS_PATH . '/includes/custom-post-types-taxonomies.php';
require_once MY_READS_PATH . '/includes/my-reads-settings-page.php';

// Register custom blocks and meta.
require_once MY_READS_PATH . '/includes/register-blocks.php';
require_once MY_READS_PATH . '/includes/register-meta.php';

// Register patterns.
require_once MY_READS_PATH . '/includes/register-patterns.php';

/// Register the single template.
require_once MY_READS_PATH . '/includes/register-templates.php';

// Enqueue assets.
require_once MY_READS_PATH . '/includes/enqueue-assets.php';

// Add API Endpoints
require_once MY_READS_PATH . '/includes/api/get-all-reads-endpoint.php';
require_once MY_READS_PATH . '/includes/api/get-amazon-info-endpoint.php';
