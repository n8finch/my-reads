<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

class MyReads_CPT {
    /**
     * MyReads_CPT constructor.
     */
    public function __construct() {
        add_action( 'init', [ $this, 'maybe_flush_rewrites' ] );
        add_action( 'init', [ $this, 'register_post_type_settings' ] );
        add_action( 'init', [ $this, 'register_custom_taxonomies' ] );
        add_filter( 'manage_edit-myreads_columns', [ $this, 'myreads_columns' ] );
        add_action( 'manage_myreads_posts_custom_column', [ $this, 'manage_myreads_columns' ], 10, 2 );
        add_action( 'restrict_manage_posts', [ $this, 'myreads_restrict_manage_posts' ] );
        add_action( 'pre_get_posts', [ $this, 'myreads_genre_taxonomy_sort_order' ] );
        add_filter( 'default_content', [ $this, 'inject_detached_pattern_content' ], 999, 2 );
    }

    public function maybe_flush_rewrites() {
        // Get the plugin data.
        if ( ! function_exists( 'get_plugin_data' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        $data = get_plugin_data( MYREADS_PLUGIN_FILE );

        if ( get_transient( 'myreads_flush_rewrites' ) || get_option( 'myreads_plugin_version' ) !== $data['Version'] ) {
            flush_rewrite_rules();
            delete_transient( 'myreads_flush_rewrites' );
            update_option( 'myreads_plugin_version', $data['Version'], true );
        }
    }

    /**
     * Inject the raw content of a wp_block into new 'myreads' posts.
     *
     * @param string $content
     * @param object $post
     *
     * @return string
     */
    public function inject_detached_pattern_content( $content, $post ) {

        if ( $post->post_type !== 'myreads' || ! empty( $post->post_content ) ) {
            return $content;
        }

        $default_pattern = get_option( 'myreads_default_pattern', 'my-reads-default' );

        // If no custom pattern is set, or if the default pattern is selected, return the original content (which will be the default pattern).
        if ( ! $default_pattern || $default_pattern === 'my-reads-default' ) {
            return file_get_contents( MYREADS_PATH . '/patterns/my-reads-default.php' ) ?? $content;
        }

        // 1. Fetch the User-Created Pattern by ID (or slug)
        $pattern_post = get_page_by_path( $default_pattern, OBJECT, 'wp_block' );

        // Check if the pattern post exists and is of the correct post type.
        if ( $pattern_post && 'wp_block' === $pattern_post->post_type ) {
            // Return the raw block HTML.
            return $pattern_post->post_content;
        }


        return $content;
    }

    /**
     * register_post_type_settings
     *
     * @return void
     */
    public function register_post_type_settings() {
        $labels = [
            'name' => 'My Reads',
            'singular_name' => 'My Read',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Read',
            'edit_item' => 'Edit Read',
            'new_item' => 'New Read',
            'view_item' => 'View Read',
            'search_items' => 'Search Reads',
            'not_found' => 'No Reads found',
            'not_found_in_trash' => 'No Reads found in Trash',
            'parent_item_colon' => '',
            'menu_name' => 'My Reads'
        ];
        $rewrite = [
            'slug' => 'my-reads',
            'with_front' => true,
            'pages' => true,
            'feeds' => true,
        ];
        $args = [
            'capability_type' => 'post',
            'exclude_from_search' => false,
            'has_archive' => false,
            'hierarchical' => false,
            'labels' => $labels,
            'menu_icon' => 'dashicons-book',
            'menu_position' => '5',
            'public' => true,
            'publicly_queryable' => true,
            'query_var' => true,
            'rewrite' => $rewrite,
            'show_in_rest' => true,
            'show_ui' => true,
            'supports' => [ 'title', 'editor', 'excerpt', 'comments', 'revisions', 'help', 'custom-fields', 'thumbnail' ],
        ];

        // add a filter for customizing post types from a child theme or plugin
        $args = apply_filters( __CLASS__ . '/myreads_ctp_args', $args, 'myreads' );

        register_post_type( 'myreads', $args );
    }

    /**
     * register_custom_taxonomies
     *
     * @return void
     */
    public function register_custom_taxonomies() {
        $this->register_taxonomy_settings( 'myreads_genre', 'Genres', 'Genre', 'my-reads-genre', [ 'myreads' ], true );
        $this->register_taxonomy_settings( 'myreads_year', 'Years', 'Year', 'my-reads-year', [ 'myreads' ], true );
    }

    /**
     * register_taxonomy_settings
     *
     * @param string $key_name
     * @param string $name
     * @param string $singular_name
     * @param string $url_slug
     * @param array  $post_type_keys
     * @param bool   $is_hierarchical
     * @return void
     */
    public function register_taxonomy_settings( $key_name, $name, $singular_name, $url_slug, $post_type_keys, $is_hierarchical ) {
        $labels = [
            'name' => $name,
            'singular_name' => $singular_name,
            'menu_name' => null,
            'all_items' => 'All ' . $name,
            'edit_item' => 'Edit ' . $singular_name,
            'view_item' => 'View ' . $singular_name,
            'update_item' => 'Update ' . $singular_name,
            'add_new_item' => 'Add New ' . $singular_name,
            'new_item_name' => 'New ' . $singular_name . ' Name',
            'parent_item' => 'Parent Category',
            'parent_item_colon' => 'Parent Category',
            'search_items' => 'Search ' . $name,
            'popular_items' => 'Common ' . $name,
            'separate_items_with_commas' => 'Separate ' . $name . ' with commas',
            'add_or_remove_items' => 'Add or remove ' . $name,
            'choose_from_most_used' => 'Choose from the most used ' . $name,
            'not_found' => 'No ' . $name . ' found'
        ];

        $rewrite = [
            'slug' => $url_slug,
            'with_front' => true,
            'hierarchical' => true,
        ];

        $args = [
            'labels' => $labels,
            'hierarchical' => $is_hierarchical,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_admin_column' => true,
            'show_in_quick_edit' => true,
            'show_in_nav_menus' => true,
            'show_in_rest' => true,
            'show_tagcloud' => true,
            'query_var' => true,
            'rewrite' => $rewrite,
        ];

        register_taxonomy( $key_name, $post_type_keys, $args );
    }

    /**
     * Add custom columns to the 'Reads' list view
     * @param array $columns
     * @return array
     */
    public function myreads_columns( $columns ) {
        $columns = [
            'cb' =>   '<input type="checkbox" />',
            'title' => __( 'Title', 'my-reads' ),
            'cover-image' => __( 'Cover', 'my-reads' ),
            'myreads_genre' => __( 'Genre', 'my-reads' ),
            'year' => __( 'Year read', 'my-reads' ),
            'date' => __( 'Date', 'my-reads' )
        ];
        return $columns;
    }

    /**
     * Adds Genre Filter to Dashboard admin
     *
     * @return void
     */
    public function myreads_restrict_manage_posts() {
        // only display these taxonomy filters on desired custom post_type listings
        global $typenow;
        $post_type = 'myreads';
        if ( $typenow == $post_type ) {

            // create an array of taxonomy slugs you want to filter by - if you want to retrieve all taxonomies, could use get_taxonomies() to build the list
            $filters = [ 'myreads_genre', 'myreads_year' ];

            foreach ( $filters as $tax_slug ) {
                // retrieve the taxonomy object
                $tax_obj = get_taxonomy( $tax_slug );
                $tax_name = $tax_obj->labels->name;

                // retrieve array of term objects per taxonomy
                $terms = get_terms( $tax_slug );
                $current_v = isset( $_GET[$tax_slug] ) ? sanitize_text_field( wp_unslash( $_GET[$tax_slug] ) ) : '';

                // Output html for taxonomy dropdown filter.
                ?>
                <select name="<?php echo esc_attr( $tax_slug ) ?>" id="<?php echo esc_attr( $tax_slug ) ?>" class="postform">
                  <option value="">All <?php echo esc_html( $tax_name ) ?></option>
                  <?php
                    foreach ( $terms as $term ) {
                        // output each select option line, check against the last $_GET to show the current option selected
                        ?>
                    <option value="<?php echo esc_attr( $term->slug ) ?>" <?php echo esc_attr( $current_v === $term->slug ? ' selected="selected"' : '' ) ?> ><?php echo esc_attr( $term->name ) ?> ( <?php echo esc_attr( $term->count ) ?> )</option>
                    <?php
                    }?>
                </select>
                <?php
            }
        }
    }

    /**
     * Displays the correct columns in the My Reads list view.
     *
     * @param string $column
     * @param int    $post_id
     * @return void
     */
    public function manage_myreads_columns( $column, $post_id ) {
        switch ( $column ) {
            case 'cover-image':
                $thumbnail = get_the_post_thumbnail_url( $post_id, 'myreads_image', true );
                if ( $thumbnail ) {
                    echo wp_kses_post( '<img src="' . esc_url( $thumbnail ) . '" alt="' . get_the_title() . '" style="max-width: 100px; max-height: 100px;" />' );
                } else {
                    echo wp_kses_post( __( 'No Image', 'my-reads' ) );
                }
                break;

            case 'myreads_genre':
                $term_names = wp_get_post_terms( $post_id, 'myreads_genre', ["fields" => "names"] );
                if ( $term_names ) {
                    foreach ( $term_names as $term ) {
                        echo wp_kses_post( $term ) . '<br/>';
                    }
                }
                break;

            case 'year':
                $term_names = wp_get_post_terms( $post_id, 'myreads_year', ["fields" => "names"] );
                if ( $term_names ) {
                    foreach ( $term_names as $term ) {
                        echo wp_kses_post( $term ) . '<br/>';
                    }
                }
                break;
            default:
                break;
        }
    }

    /**
     * Display the archive page for the Genre taxonomy in alphabetical order
     *
     * @param object $query
     * @return void
     */
    public function myreads_genre_taxonomy_sort_order( $query ) {
        if ( $query->is_tax( 'myreads_genre' ) ) {
            $query->set( 'orderby', 'title' );
            $query->set( 'order', 'ASC' );
        }
    }
}

new MyReads_CPT();
