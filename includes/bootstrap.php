<?php

class My_Reads_Bootstrap {
    public function __construct() {
        register_activation_hook( __FILE__, [ $this, 'my_reads_activate' ] );
        register_deactivation_hook( __FILE__, [ $this, 'my_reads_deactivate' ] );
        // add_action( 'admin_enqueue_scripts', [ $this, 'my_reads_block_setup' ] );
    }

    /**
     * Activate the plugin.
     */
    public function my_reads_activate() {
        // Get the plugin data.
        if ( ! function_exists( 'get_plugin_data' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        $data = get_plugin_data( __FILE__ );

        // Set a transient to check when our post type is registered.
        set_transient( 'my_reads_flush_rewrites', true, MINUTE_IN_SECONDS );
        add_option( 'my_reads_plugin_version', $data['Version'], '', true );
    }


    /**
    * Deactivation hook.
    */
    public function my_reads_deactivate() {
        // Unregister the post type, so the rules are no longer in memory.
        unregister_post_type( 'my_reads' );
        // Clear the permalinks to remove our post type's rules from the database.
        flush_rewrite_rules();
    }

}

new My_Reads_Bootstrap();
