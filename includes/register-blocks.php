<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

class My_Reads_Register_Blocks {
    public const UNREGISTERED_BLOCKS = [
        'my-reads/my-reads-details',
        'my-reads/my-reads-star-rating',
        'my-reads/my-reads-media-format',
    ];

    public function __construct() {
        add_action( 'init', [ $this, 'my_reads_block_init' ] );
        add_action( 'allowed_block_types_all', [ $this, 'unregister_my_reads_blocks_on_other_post_types' ], 10, 2 );
        add_action( 'wp_print_scripts', [ $this, 'unregister_my_reads_blocks_on_other_post_types_js' ], 999 );
        add_filter( 'block_categories_all', [ $this, 'add_blocks_to_category' ], 99 );
    }

    /**
    * Register blocks.
    *
    * @return void
    */
    public function my_reads_block_init() {
        register_block_type( MYREADS_PATH . '/build/my-reads-listing' );
        register_block_type( MYREADS_PATH . '/build/my-reads-star-rating' );
        register_block_type( MYREADS_PATH . '/build/my-reads-media-format' );
        // register_block_type( MYREADS_PATH . '/build/my-reads-details' );
    }

    /**
    * Add Blocks to My Reads category.
    */
    public function add_blocks_to_category( $existingCategories ) {
        $customCategories = [
            [
                'slug' => 'my-reads',
                'title' => __( 'My Reads', 'my-reads' ),
            ]
        ];
        return array_merge( $customCategories, $existingCategories );
    }

    /**
    * Enqueue block editor only JavaScript and CSS.
    */
    public function unregister_my_reads_blocks_on_other_post_types( $allowed_block_types, $block_editor_context ) {
        if ( get_post_type() !== 'my_reads' ) {
            // Get all registered blocks if $allowed_block_types is not already set.
            if ( ! is_array( $allowed_block_types ) || empty( $allowed_block_types ) ) {
                $registered_blocks   = WP_Block_Type_Registry::get_instance()->get_all_registered();
                $allowed_block_types = array_keys( $registered_blocks );
            }

            // Create a new array for the allowed blocks.
            $filtered_blocks = [];

            // Loop through each block in the allowed blocks list.
            foreach ( $allowed_block_types as $block ) {

                // Check if the block is not in the disallowed blocks list.
                if ( ! in_array( $block, self::UNREGISTERED_BLOCKS, true ) ) {

                    // If it's not disallowed, add it to the filtered list.
                    $filtered_blocks[] = $block;
                }
            }

            // Return the filtered list of allowed blocks
            return $filtered_blocks;
        }

        return $allowed_block_types;
    }

    /**
    * Enqueue block editor only JavaScript and CSS.
    * @todo doesn't seem to actually work to dequeue the styles and scripts
    */
    public function unregister_my_reads_blocks_on_other_post_types_js() {
        if ( get_post_type() !== 'my_reads' ) {
            // Dequeue all blocks in UNREGISTERED_BLOCKS
            foreach ( self::UNREGISTERED_BLOCKS as $block ) {
                wp_dequeue_style( str_replace( '/', '-', $block ) . '-style-css' );
                wp_dequeue_script( str_replace( '/', '-', $block ) . '-editor-script-js' );
            }
        }
    }
}

new My_Reads_Register_Blocks();
