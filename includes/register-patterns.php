<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly
class MyReads_Register_Patterns {
    public function __construct() {
        add_action( 'init', [ $this, 'myreads_register_patterns' ] );
    }

    /**
     * Register block patterns and category.
     *
     * @return void
     */
    public function myreads_register_patterns() {
        register_block_pattern(
            'my-reads/my-reads-default',
            [
                'title' => __( 'My Reads Default', 'my-reads' ),
                'description' => _x( 'A default for My Reads.', 'A default for My Reads...', 'my-reads' ),
                'content' => file_get_contents( MYREADS_PATH . '/patterns/my-reads-default.php' ),
                'categories' => [ 'my-reads' ],
                'keywords' => [ 'reads', 'book', 'audiobook', 'article' ],
                'postTypes' => [ 'myreads' ],
                'source' => 'plugin'
            ]
        );

        register_block_pattern_category(
            'my-reads',
            [ 'label' => __( 'My Reads', 'my-reads' ) ]
        );
    }
}

new MyReads_Register_Patterns();
