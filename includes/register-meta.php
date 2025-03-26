<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly
class MyReads_Register_Meta {
    public function __construct() {
        add_action( 'init', [ $this, 'register_myreads_post_meta'] );
    }

    /**
     * Add some post meta
     *
     * @return void
     */
    public function register_myreads_post_meta() {
        $post_meta = [
            '_myreads_author' => [ 'type' => 'string', 'default' => '' ],
            '_myreads_format' => [ 'type' => 'string', 'default' => 'book' ],
            '_myreads_rating' => [ 'type' => 'number', 'default' => 3.5 ],
            '_myreads_ratingStyle' => [ 'type' => 'string', 'default' => 'star' ],
            '_myreads_isFavorite' => [ 'type' => 'boolean', 'default' => false ],
            '_myreads_currentlyReading' => [ 'type' => 'boolean', 'default' => false ],
            '_myreads_amazonLink' => [ 'type' => 'string', 'default' => '' ],
        ];

        foreach ( $post_meta as $meta_key => $args ) {
            register_post_meta(
                'myreads',
                $meta_key,
                [
                    'show_in_rest'  => true,
                    'single'        => true,
                    'type'          => $args['type'],
                    'default'       => $args['default'],
                    'auth_callback' => function () {
                        return current_user_can( 'edit_posts' );
                    }
                ]
            );
        }
        // Add dynamic image sizes metadata.
        register_post_meta( 'myreads', '_myreads_image_sizes', [
            'type' => 'object',
            'description' => 'Dynamic image sizes metadata',
            'single' => true,
            'show_in_rest' => [
                'schema' => [
                    'type' => 'object',
                    'additionalProperties' => [
                        'type' => 'object',
                        'properties' => [
                            'file' => ['type' => 'string'],
                            'width' => ['type' => 'integer'],
                            'height' => ['type' => 'integer'],
                            'filesize' => ['type' => 'integer'],
                            'mime_type' => ['type' => 'string'],
                            'source_url' => ['type' => 'string'],
                        ],
                        'required' => ['file', 'width', 'height', 'mime_type', 'source_url'],
                    ],
                ],
            ],
            'auth_callback' => function () {
                return current_user_can( 'edit_posts' );
            }
        ] );
    }
}

new MyReads_Register_Meta();
