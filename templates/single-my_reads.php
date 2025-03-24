<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Get all the post meta. This is a bit verbose, but it's a good way to ensure that we have all the data we need in variables and it doesn't add to the query.
$the_title = get_the_title();
?>

<?php get_header(); ?> 

<?php the_content(); ?> 

<?php get_footer(); ?>
