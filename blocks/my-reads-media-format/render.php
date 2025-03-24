<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

global $post;

$format_sentence = '';
$format = get_post_meta( $post->ID, '_my_reads_format', true );
$format_options = [
    'book' => [
        'icon' => '📖',
        'label' => __( 'Book', 'my-reads' ),
    ],
    'audiobook' => [
        'icon' => '🎧',
        'label' => __( 'Audiobook', 'my-reads' ),
    ],
    'comicbook' => [
        'icon' => '🗯️',
        'label' => __( 'Comicbook', 'my-reads' ),
    ],
    'article' => [
        'icon' => '📰',
        'label' => __( 'Article', 'my-reads' ),
    ],
];
if ( array_key_exists( $format, $format_options ) ) {
    $format_sentence = $format_options[$format]['icon'] . ' ' . $format_options[$format]['label'];
}

?>
<p <?php echo esc_attr( get_block_wrapper_attributes() ) ?>>
  <?php echo wp_kses_post( __( 'Format: ', 'my-reads' ) ) . esc_html( $format_sentence ) ?>
</p>
