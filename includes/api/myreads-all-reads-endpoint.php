<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

class MyReads_All_Reads_Endpoint {
    // Define the path to the JSON file.
    public $all_reads_file = '';

    public function __construct() {
        $upload_dir = wp_upload_dir();
        $this->all_reads_file = trailingslashit( $upload_dir['basedir'] ) . 'all-the-reads.json';
        // Create a WP REST API endpoint for all the posts.
        add_action( 'rest_api_init', [ $this, 'create_all_reads_endpoint' ] );
    }

    // Create a WP REST API endpoint for all the posts.
    public function create_all_reads_endpoint() {
        register_rest_route(
            'my-reads/v1',
            '/all-the-reads',
            [
                'methods' => 'GET',
                'callback' => [ $this, 'myreads_get_all_the_reads' ],
                'permission_callback' => function () {
                    return current_user_can( 'edit_posts' );
                },
            ]
        );
    }

    /**
     * myreads_get_all_the_reads
     *
     * @return object
     */
    public function myreads_get_all_the_reads( WP_REST_Request $request ) {
        // Get the 'refresh' parameter from the request
        $refresh = $request->get_param( 'refresh' );
        // If 'refresh' is set to true, create a new file.
        if ( $refresh ) {
            return $this->create_all_reads_file();
        }

        // If the file exists and is less than 10 minutes old, return the contents of the file.
        // Check if the file exists.
        if ( file_exists( $this->all_reads_file ) ) {
            // Check the file timestamp.
            $file_timestamp = filemtime( $this->all_reads_file );
            // Check the current timestamp.
            $current_timestamp = time();
            // Check if the file is older than 10 minutes.
            if ( $current_timestamp - $file_timestamp < 10 * MINUTE_IN_SECONDS ) {
                // If the file is less than 10 minutes old, return the contents of the file.
                return json_decode( file_get_contents( $this->all_reads_file ) );
            }
        }

        // If the file doesn't exist or is older than 10 minutes, create the file.
        return $this->create_all_reads_file();
    }

    /**
     * create_all_reads_file
     *
     * @return object
     */
    public function create_all_reads_file() {
        $args = [
            'post_type'      => 'my_reads',
            'posts_per_page' => -1, // Get all posts
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];

        $query = new WP_Query( $args );
        $posts_by_year = [];

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $post_id = get_the_ID();

                // Get the 'years' taxonomy term
                $years_terms = get_the_terms( $post_id, 'my_reads_year' );
                $year = $years_terms && ! is_wp_error( $years_terms ) ? $years_terms[0]->name : 'Unknown';

                // Get the featured image in custom size 'my_reads_image'
                $featured_image = get_the_post_thumbnail_url( $post_id, 'my_reads_image' );

                // Prepare post data
                $post_data = [
                    'id'              => $post_id,
                    'title'           => get_the_title(),
                    '_my_reads_rating' => floatval( get_post_meta( $post_id, '_my_reads_rating', true ) ),
                    '_my_reads_ratingStyle' => get_post_meta( $post_id, '_my_reads_ratingStyle', true ) ?? 'star',
                    '_my_reads_isFavorite' => get_post_meta( $post_id, '_my_reads_isFavorite', true ),
                    '_my_reads_format' => get_post_meta( $post_id, '_my_reads_format', true ) ?? 'book',
                    '_my_reads_amazonLink' => get_post_meta( $post_id, '_my_reads_amazonLink', true ) ?? '',
                    'excerpt'         => get_the_excerpt(),
                    'featured_image'  => $featured_image ? str_replace( site_url(), '', $featured_image ) : '',
                    'year'             => $year,
                    'permalink'       => str_replace( site_url(), '', get_permalink( $post_id ) ),
                    'genres'          => wp_get_post_terms( $post_id, 'my_reads_genre', ["fields" => "names"] ),
                ];

                // Group posts by year
                if ( ! isset( $posts_by_year[ $year ] ) ) {
                    $posts_by_year[ $year ] = [];
                }
                $posts_by_year[ $year ][] = $post_data;
            }

            // Sort the posts by year
            krsort( $posts_by_year );

            wp_reset_postdata();
        }

        file_put_contents( $this->all_reads_file, wp_json_encode( $posts_by_year ) );

        return rest_ensure_response( $posts_by_year );
    }
}

new MyReads_All_Reads_Endpoint();
