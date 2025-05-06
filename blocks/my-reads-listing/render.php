<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

$wrapper_attributes = get_block_wrapper_attributes();
$attributes['myReadsUploadsDir'] = trailingslashit( wp_upload_dir()['baseurl'] . '/my-reads' );
$attributes_json = wp_json_encode( $attributes );

echo sprintf(
    '<div id="my-reads-filter" %s data-attributes="%s">%s</div>',
    wp_kses_post( $wrapper_attributes ),
    esc_attr( $attributes_json ),
    'Loading...'
);
