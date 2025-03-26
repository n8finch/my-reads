<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $post;
$author = get_post_meta( $post->ID, '_myreads_author', true );

?>
<div <?php echo esc_attr( get_block_wrapper_attributes() ) ?>>
    <?php echo wp_kses_post( __( 'Author: ', 'my-reads' ) ) . esc_html( $author ) ?>
</div>