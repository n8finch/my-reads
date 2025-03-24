<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class My_Reads_Register_Meta {
    public function __construct() {
        add_action( 'init', [ $this, 'register_my_reads_post_meta'] );
    }

    /**
     * Add some post meta
     *
     * @return void
     */
    public function register_my_reads_post_meta() {
        $post_meta = [
            '_my_reads_author' => [ 'type' => 'string', 'default' => '' ],
            '_my_reads_format' => [ 'type' => 'string', 'default' => 'book' ],
            '_my_reads_rating' => [ 'type' => 'number', 'default' => 3.5 ],
            '_my_reads_ratingStyle' => [ 'type' => 'string', 'default' => 'star' ],
            '_my_reads_isFavorite' => [ 'type' => 'boolean', 'default' => false ],
            '_my_reads_amazonLink' => [ 'type' => 'string', 'default' => '' ],
        ];

        foreach ( $post_meta as $meta_key => $args ) {
            register_post_meta(
                'my_reads',
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
        register_post_meta( 'my_reads', '_my_reads_image_sizes', [
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

new My_Reads_Register_Meta();
