<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly
class My_Reads_Register_Patterns {
    public function __construct() {
        add_action( 'init', [ $this, 'my_reads_register_patterns' ] );
    }

    /**
     * Register block patterns and category.
     *
     * @return void
     */
    public function my_reads_register_patterns() {
        register_block_pattern(
            'my-reads/my-reads-default',
            [
                'title' => __( 'My Reads Default', 'my-reads' ),
                'description' => _x( 'A default for My Reads.', 'A default for My Reads...', 'my-reads' ),
                'content' => file_get_contents( MY_READS_PATH . '/patterns/my-reads-default.php' ),
                'categories' => [ 'my-reads' ],
                'keywords' => [ 'reads', 'book', 'audiobook', 'article' ],
                'postTypes' => [ 'my_reads' ],
                'source' => 'plugin'
            ]
        );

        register_block_pattern_category(
            'my-reads',
            [ 'label' => __( 'My Reads', 'my-reads' ) ]
        );
    }
}

new My_Reads_Register_Patterns();
