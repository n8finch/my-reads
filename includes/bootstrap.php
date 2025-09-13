<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

class MyReads_Bootstrap {
    public function __construct() {
        register_activation_hook( __FILE__, [ $this, 'myreads_activate' ] );
        register_deactivation_hook( __FILE__, [ $this, 'myreads_deactivate' ] );
        add_action( 'wp_after_insert_post', [ $this, 'myreads_generate_json_on_save' ], 10, 3 );
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


    /**
     * myreads_generate_json_on_save
     * Trigger JSON regeneration when a 'myreads' post is saved
     * 
     * @param int     $post_id The post ID.
     * @param WP_Post $post The post object.
     * @param bool    $update Whether this is an existing post being updated or not.
     * @return void
     */
    public function myreads_generate_json_on_save( $post_id, $post, $update ) {
        // Only proceed if this is a 'myreads' post type
        if ( $post->post_type !== 'myreads' ) {
            return;
        }

        // Avoid infinite loop by checking if this is an autosave or revision
        if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
            return;
        }

        // Regenerate the JSON file
        if ( class_exists( 'MyReads_All_Reads_Endpoint' ) && get_option( 'myreads_auto_regenerate_json', '0' ) === '1' ) {
            $json_generator = new MyReads_All_Reads_Endpoint();
            $json_generator->create_all_reads_file();
        }
    }
}

new MyReads_Bootstrap();
