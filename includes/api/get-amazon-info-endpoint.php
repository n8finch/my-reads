<?php

class Get_Amazon_Info_Endpoint {
    // Define the path to the JSON file.
    public $all_reads_file = '';

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_my_reads_fetch_amazon_data_route' ] );
    }

    public function register_my_reads_fetch_amazon_data_route() {
        register_rest_route( 'my-reads/v1', '/fetch-amazon-data', [
            'methods' => 'POST',
            'callback' => [ $this, 'my_reads_fetch_amazon_data' ],
            'permission_callback' => function() {
              return current_user_can( 'edit_posts' );
            }
        ] );
    }

    public function my_reads_fetch_amazon_data( $request ) {
        if ( empty( $request['url'] ) ) {
            wp_send_json_error( ['error' => 'Amazon URL is required.'] );
            return;
        }

        $amazon_url = esc_url_raw( $request['url'] );

        // Fetch the Amazon page HTML
        $response = wp_remote_get( $amazon_url );
        if ( is_wp_error( $response ) ) {
            wp_send_json_error( ['error' => 'Failed to fetch Amazon page.'] );
            return;
        }

        $html = wp_remote_retrieve_body( $response );

        // Extract the Book Title
        preg_match( '/<span id="productTitle"[^>]*>(.*?)<\/span>/is', $html, $title_match );
        $book_title = trim( $title_match[1] ?? '' );

        // Extract the Main Image URL
        preg_match( '/data-a-dynamic-image="{&quot;(https:\/\/m\.media-amazon\.com[^&]+)&quot;/', $html, $image_match );
        $image_url = html_entity_decode( $image_match[1] ?? '' );

        if ( !$book_title || !$image_url ) {
            wp_send_json_error( ['error' => 'Could not extract book details.'] );
            return;
        }

        // Upload image to WordPress media library
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );
        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        // Create a slug-friendly title for the image, replace any special characters in the title with a dash.
        $book_title_slug = preg_replace( '/[^a-zA-Z0-9\-]/', '-', sanitize_title( $book_title ) );

        $attachment_id = media_sideload_image( $image_url, 0, $book_title_slug, 'id' );

        if ( is_wp_error( $attachment_id ) ) {
            wp_send_json_error( ['error' => 'Failed to upload image.'] );
            return;
        }

        wp_send_json_success( [
            'title' => $book_title,
            'imageUrl' => $image_url,
            'attachmentId' => $attachment_id,
        ] );
    }
}

new Get_Amazon_Info_Endpoint();
