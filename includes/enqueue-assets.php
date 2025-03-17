<?php

class My_Add_Assets {
    public function __construct() {
        add_action( 'after_setup_theme', [$this, 'add_square_image_size' ] );
        add_action( 'admin_enqueue_scripts', [$this, 'enqueue_scripts'] );
        add_filter( 'post_thumbnail_html', [ $this, 'my_reads_default_featured_image' ], 10, 5 );
    }

    /**
    * Register book image size (same size Amazon uses).
    */
    public function add_square_image_size() {
        add_image_size( 'my_reads_image', 344, 522, [ 'center', 'center' ] );
    }

    /**
     * Enqueue admin scripts and styles.
     */
    public function enqueue_scripts() {
        global $post;
        // Load only on ?page=my-first-gutenberg-app.
        if ( 'my_reads' !== get_post_type( $post ) ) {
            return;
        }

        // Automatically load imported dependencies and assets version.
        $asset_file = include MY_READS_PATH . '/build/slotfill-my-reads/index.asset.php';

        // Enqueue CSS dependencies.
        foreach ( $asset_file['dependencies'] as $style ) {
            wp_enqueue_style( $style );
        }

        // Load our app.js.
        wp_register_script(
            'slotfill-my-reads',
            MY_READS_URL . '/build/slotfill-my-reads/index.js',
            $asset_file['dependencies'],
            $asset_file['version']
        );
        wp_enqueue_script( 'slotfill-my-reads' );
    }

    public function my_reads_default_featured_image( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
        // Only apply to "my_reads" post type
        if ( get_post_type( $post_id ) !== 'my_reads' ) {
            return $html;
        }

        // If a featured image is set, return the existing HTML
        if ( $post_thumbnail_id ) {
            return $html;
        }

        // Get the _my_reads_format meta field
        $format = get_post_meta( $post_id, '_my_reads_format', true ) ?? 'book';

        // Define default images based on format
        $default_images = [
            'article' => MY_READS_URL . '/includes/images/article.webp',
            'audiobook' => MY_READS_URL . '/includes/images/headphones.webp',
            'book'       => MY_READS_URL . '/includes/images/book.webp',
            'comicbook'  => MY_READS_URL . '/includes/images/article.webp',
        ];

        // Check if the format exists in our defaults, otherwise return the original HTML
        if ( isset( $default_images[$format] ) ) {
            return '<img src="' . esc_url( $default_images[$format] ) . '" class="wp-post-image" />';
        }

        return $html;
    }
}

new My_Add_Assets();
