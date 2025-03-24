<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

class My_Reads_Register_Templates {
    public function __construct() {
        // add_filter( 'single_template', [ $this, 'my_reads_load_single_template_php' ] );
        // add_filter( 'wp_theme_json_data', [$this, 'add_html_template_path' ], 10, 2);
        // add_filter( 'block_template_paths', [$this, 'add_html_template_path' ] );

        add_action( 'init', [ $this, 'my_reads_register_block_template' ] );
    }

    public function my_reads_register_block_template() {
        // Register a block template.
        register_block_template( 'my-reads//single-my_reads', [
            'title'       => __( 'My Reads Template', 'my-reads' ),
            'description' => __( 'A template for My Reads.', 'my-reads' ),
            'postTypes'   => [ 'my_reads' ],
            'content'     => $this->my_reads_get_template( 'single-my_reads.html' )
        ] );
    }

    public function my_reads_get_template( $template ) {
        ob_start();
        include MY_READS_PATH . "/templates/{$template}";
        return ob_get_clean();
    }

    public function add_wp_theme_json_data( $theme_json, $context ) {
        // Only apply if in the site editor or frontend
        if ( $context !== 'theme' ) {
            return $theme_json;
        }

        $custom_templates = [
            'single-my_reads' => [
                'title' => __( 'Single My Reads', 'my-reads' ),
                'postTypes' => ['my_reads']
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
    public function my_reads_load_single_template_php( $template ) {
        global $post;
        $my_reads_template = MY_READS_PATH . '/templates/single-my_reads.php';

        if ( 'my_reads' === $post->post_type && file_exists( $my_reads_template ) ) {
            return $my_reads_template;
        }

        return $template;
    }
}

new My_Reads_Register_Templates();
