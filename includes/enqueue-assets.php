<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

class MyReads_Add_Assets {
    public function __construct() {
        add_action( 'after_setup_theme', [$this, 'add_square_image_size' ] );
        add_action( 'admin_enqueue_scripts', [$this, 'enqueue_admin_settings_scripts'] );
        add_action( 'admin_enqueue_scripts', [$this, 'enqueue_cpt_scripts'] );
        add_filter( 'post_thumbnail_html', [ $this, 'myreads_default_featured_image' ], 10, 5 );
    }

    /**
    * Register book image size (same size Amazon uses).
    */
    public function add_square_image_size() {
        add_image_size( 'myreads_image', 344, 522, [ 'center', 'center' ] );
    }

    /**
     * Enqueue admin scripts and styles for settings page.
     */
    public function enqueue_admin_settings_scripts() {
        // Enqueue script for the settings page.
        if ( isset( $_GET['page'] ) && sanitize_text_field( wp_unslash( $_GET['page'] ) ) === 'my-reads-cpt-settings' ) {
            wp_enqueue_script( 'my-reads-settings', MYREADS_URL . '/includes/js/admin-my-reads-settings-page.js', [], MYREADS_PLUGIN_VERSION, true );

            // Add in extra data for the settings page.
            wp_localize_script( 'my-reads-settings', 'MYREADS_SETTINGS', [
                'allTheReads' => esc_url( site_url( '/wp-json/my-reads/v1/all-the-reads/?refresh=true' ) ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
            ] );
        }
    }

    /**
     * Enqueue admin scripts and styles.
     */
    public function enqueue_cpt_scripts() {
        global $post;
        // Load only on ?page=my-first-gutenberg-app.
        if ( 'myreads' !== get_post_type( $post ) ) {
            return;
        }

        // Automatically load imported dependencies and assets version.
        $asset_file = include MYREADS_PATH . '/build/slotfill-my-reads/index.asset.php';

        // Enqueue CSS dependencies.
        foreach ( $asset_file['dependencies'] as $style ) {
            wp_enqueue_style( $style );
        }

        // Load our app.js.
        wp_register_script(
            'slotfill-my-reads',
            MYREADS_URL . '/build/slotfill-my-reads/index.js',
            $asset_file['dependencies'],
            $asset_file['version'],
            true
        );
        wp_enqueue_script( 'slotfill-my-reads' );

        // Localize script for passing data to JS.
        wp_localize_script( 'slotfill-my-reads', 'MYREADS_CPT', [
            'nonce' => wp_create_nonce( 'wp_rest' ),
        ] );
    }

    public function myreads_default_featured_image( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
        // Only apply to "myreads" post type
        if ( get_post_type( $post_id ) !== 'myreads' ) {
            return $html;
        }

        // If a featured image is set, return the existing HTML
        if ( $post_thumbnail_id ) {
            return $html;
        }

        // Get the _myreads_format meta field
        $format = get_post_meta( $post_id, '_myreads_format', true ) ?? 'book';

        // Define default images based on format
        $default_images = [
            'article' => MYREADS_URL . '/includes/images/article.webp',
            'audiobook' => MYREADS_URL . '/includes/images/headphones.webp',
            'book'       => MYREADS_URL . '/includes/images/book.webp',
            'comicbook'  => MYREADS_URL . '/includes/images/article.webp',
        ];

        // Check if the format exists in our defaults, otherwise return the original HTML
        if ( isset( $default_images[$format] ) ) {
            return '<img src="' . esc_url( $default_images[$format] ) . '" class="wp-post-image" />';
        }

        return $html;
    }
}

new MyReads_Add_Assets();
