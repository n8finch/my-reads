<?php

$wrapper_attributes = get_block_wrapper_attributes();
$attributes_json = wp_json_encode( $attributes );

echo sprintf(
    '<div id="my-reads-filter" %s data-attributes="%s">%s</div>',
    esc_attr( $wrapper_attributes ),
    esc_attr( $attributes_json ),
    'Loading...'
);
