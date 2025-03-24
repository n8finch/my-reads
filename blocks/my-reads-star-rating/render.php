<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

global $post;


$ratingEmojis = '';
// Get the rating and rating style from the post meta
$rating = floatval( get_post_meta( get_the_ID(), '_my_reads_rating', true ) );
$ratingStyle = get_post_meta( get_the_ID(), '_my_reads_ratingStyle', true );
// If the rating style is not set, default to star
if ( ! $ratingStyle ) {
    $ratingStyle = 'star';
}
// Generate the rating emojis.
for ( $i = 0.5; $i < $rating; $i += 1.0 ) {
    $ratingEmojis .= $ratingStyle === 'star' ? '⭐️' : '❤️';
}

// Append ½ if rating is half.
if ( fmod( $rating, 1.0 ) !== 0.0 ) {
    $ratingEmojis .= '½';
}


?>
<p <?php echo esc_attr( get_block_wrapper_attributes() ) ?>>
		<?php echo wp_kses_post( 'Rating: <span class="rating-' . $ratingStyle . '">' . $ratingEmojis . '</span>', 'my-reads' ); ?>
</p>
