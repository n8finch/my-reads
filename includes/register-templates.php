<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

class MyReads_Register_Templates {
    public function __construct() {
        // add_filter( 'single_template', [ $this, 'myreads_load_single_template_php' ] );
        // add_filter( 'wp_theme_json_data', [$this, 'add_html_template_path' ], 10, 2);
        // add_filter( 'block_template_paths', [$this, 'add_html_template_path' ] );

        add_action( 'init', [ $this, 'myreads_register_block_template' ] );
    }

    public function myreads_register_block_template() {
        // Register a block template.
        register_block_template( 'my-reads//single-myreads', [
            'title'       => __( 'My Reads Template', 'my-reads' ),
            'description' => __( 'A template for My Reads.', 'my-reads' ),
            'postTypes'   => [ 'myreads' ],
            'content'     => $this->myreads_get_template( 'single-myreads.html' )
        ] );
    }

    public function myreads_get_template( $template ) {
        ob_start();
        include MYREADS_PATH . "/templates/{$template}";
        return ob_get_clean();
    }

    public function add_wp_theme_json_data( $theme_json, $context ) {
        // Only apply if in the site editor or frontend
        if ( $context !== 'theme' ) {
            return $theme_json;
        }

        $custom_templates = [
            'single-myreads' => [
                'title' => __( 'Single My Reads', 'my-reads' ),
                'postTypes' => ['myreads']
            ],
        ];

        // Add custom template data to theme JSON
        $theme_json->merge( ['customTemplates' => $custom_templates] );

        return $theme_json;
    }

    public function add_html_template_path( $paths ) {
        $plugin_templates = plugin_dir_path( __FILE__ ) . 'templates/';
        $paths[] = $plugin_templates;
        return $paths;
    }

    /**
    * Load single template for My Reads.
    */
    public function myreads_load_single_template_php( $template ) {
        global $post;
        $myreads_template = MYREADS_PATH . '/templates/single-myreads.php';

        if ( 'myreads' === $post->post_type && file_exists( $myreads_template ) ) {
            return $myreads_template;
        }

        return $template;
    }
}

new MyReads_Register_Templates();
