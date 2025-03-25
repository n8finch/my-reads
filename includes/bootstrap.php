<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

class MyReads_Bootstrap {
    public function __construct() {
        register_activation_hook( __FILE__, [ $this, 'myreads_activate' ] );
        register_deactivation_hook( __FILE__, [ $this, 'myreads_deactivate' ] );
        // add_action( 'admin_enqueue_scripts', [ $this, 'myreads_block_setup' ] );
    }

    /**
     * Activate the plugin.
     */
    public function myreads_activate() {
        // Get the plugin data.
        if ( ! function_exists( 'get_plugin_data' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        $data = get_plugin_data( __FILE__ );

        // Set a transient to check when our post type is registered.
        set_transient( 'myreads_flush_rewrites', true, MINUTE_IN_SECONDS );
        add_option( 'myreads_plugin_version', $data['Version'], '', true );
    }


    /**
    * Deactivation hook.
    */
    public function myreads_deactivate() {
        // Unregister the post type, so the rules are no longer in memory.
        unregister_post_type( 'myreads' );
        // Clear the permalinks to remove our post type's rules from the database.
        flush_rewrite_rules();
    }

}

new MyReads_Bootstrap();
