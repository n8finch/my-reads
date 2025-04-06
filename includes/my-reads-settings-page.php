<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

class MyReads_Settings {
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'myreads_cpt_settings_submenu' ] );
        add_action( 'custom_menu_order', [ $this, 'myreads_cpt_settings_menu_order' ] );
        add_action( 'admin_init', [ $this, 'myreads_register_settings' ] );
        add_action( 'admin_notices', [ $this, 'myreads_admin_notice' ] );
        add_action( 'admin_init', [ $this, 'myreads_download_csv' ] );
    }

    /**
     * Add a submenu page to the My Reads menu
     * @return void
     */
    public function myreads_cpt_settings_submenu() {
        add_submenu_page(
            'edit.php?post_type=myreads',
            __( 'My Reads Settings', 'my-reads' ),
            __( 'Settings', 'my-reads' ),
            'manage_options',
            'my-reads-cpt-settings',
            [ $this, 'myreads_cpt_settings_callback' ]
        );
    }

    public function myreads_admin_notice() {
        // Check if the success message transient exists
        if ( $message = get_transient( 'myreads_csv_import_success' ) ) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . esc_html( $message ) . '</p>';
            echo '</div>';
        }
    }

    /**
     * Display the settings page as the last item in the My Reads CPT submenu
     *
     * @return void
     */
    public function myreads_cpt_settings_menu_order() {
        global $submenu;
        if ( !empty( $submenu['my-reads-cpt-settings'] ) && !empty( $submenu['my-reads-cpt-settings'][0] ) ) {
            $submenu['my-reads-cpt-settings'][100] = $submenu['my-reads-cpt-settings'][0];
            unset( $submenu['my-reads-cpt-settings'][0] );
        }
    }

    // Register the setting
    public function myreads_register_settings() {
        register_setting( 'myreads_settings_group', 'myreads_csv_file', [
            'type' => 'string',
            'description' => 'CSV file for My Reads',
            'sanitize_callback' => [ $this, 'myreads_file_upload' ]
        ] );
    }

    // Handle the file upload
    public function myreads_file_upload( $file ) {
        // Verify the nonce for file upload.
        if ( ! isset( $_POST['myreads_csv_file_nonce'] ) ||
            ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['myreads_csv_file_nonce'] ) ), 'myreads_csv_file_action' ) ) {
            wp_die( esc_html( __( 'Security check failed.', 'my-reads' ) ) );
        }

        // Check if the file is empty or not readable.
        if ( empty( $_FILES['myreads_csv_file']['name'] ) ) {
            wp_die( esc_html( __( 'The file is not readable.', 'my-reads' ) ) );
        }

        // Use WordPress's file upload functionality
        $uploaded_file = map_deep( $_FILES['myreads_csv_file'], 'sanitize_text_field' );

        // Check if file is a CSV
        $file_type = wp_check_filetype( $uploaded_file['name'] );
        if ( $file_type['ext'] !== 'csv' ) {
            wp_die( esc_html( __( 'Invalid file type. Only CSV files are allowed.', 'my-reads' ) ) );
        }

        // Move the uploaded file to the WordPress uploads directory
        $upload = wp_handle_upload( $uploaded_file, ['test_form' => false] );
        if ( isset( $upload['file'] ) ) {
            $this->import_myreads_csv( $upload['file'] );
        } else {
            wp_die( 'File upload failed.' );
        }

        // Delete the file after processing.
        wp_delete_file( $upload['file'] );
    }

    public function generate_post_content( $author ) {
        // Get the my-reads-default.php pattern content
        ob_start();
        include MYREADS_PATH . '/patterns/my-reads-default.php';
        $content = ob_get_clean();
        // Replace the placeholder with the actual author name
        $content = str_replace( 'Author:', "Author: $author", $content );
        return $content;
    }

    public function import_myreads_csv( $file ) {
        // Get the CSV data from the file.
        $csv_data = array_map( 'str_getcsv', file( $file ) );
        // Remove the header row.
        $header = array_shift( $csv_data );
        // Combine header with data
        $csv_data = array_map( function ( $row ) use ( $header ) {
            return array_combine( $header, $row );
        }, $csv_data );

        // Loop through each row of CSV data
        foreach ( $csv_data as $row ) {
            // Map CSV columns to variables
            $title = $row['Title'];
            $author = $row['Author'];
            $format = $row['Format'];
            $rating = $row['Rating'];
            $year = $row['Year'];

            // Create a new post (for "myreads" post type)
            $post_data = [
                'post_title'   => sanitize_text_field( $title ),
                'post_content' => wp_kses_post( $this->generate_post_content( $author ) ),
                'post_type'    => 'myreads',
                'post_status'  => 'publish',
            ];

            // Insert the post into the database
            $post_id = wp_insert_post( $post_data );

            // If post creation succeeded, proceed with adding meta and taxonomies
            if ( !is_wp_error( $post_id ) ) {
                // Add format meta
                update_post_meta( $post_id, '_myreads_format', sanitize_text_field( $format ) );

                // Add rating meta
                update_post_meta( $post_id, '_myreads_rating', sanitize_text_field( $rating ) );
                update_post_meta( $post_id, '_myreads_ratingStyle', 'star' ); // Default rating style

                // Mark as not favorite by default
                update_post_meta( $post_id, '_myreads_isFavorite', false );

                // Add Year taxonomy (use the Year as the term slug)
                wp_set_object_terms( $post_id, sanitize_text_field( $year ), 'myreads_year' );
            }
        }

        set_transient( 'myreads_csv_import_success', 'CSV file uploaded and imported successfully!', 5 ); // 5 seconds
    }

    /**
     * myreads_download_csv
     *
     * @return void
     */
    public function myreads_download_csv() {
        if ( isset( $_GET['action'] ) && $_GET['action'] === 'download_myreads_csv' ) {
            if ( ! current_user_can( 'edit_posts' ) ) {
                wp_die( esc_html( __( 'You do not have permission to download this file.', 'my-reads' ) ) );
            }

            // Initialize WP_Filesystem
            global $wp_filesystem;
            if ( ! function_exists( 'WP_Filesystem' ) ) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }
            WP_Filesystem();

            // Define a temporary file path
            $upload_dir = wp_upload_dir();
            $csv_path   = trailingslashit( $upload_dir['path'] ) . 'my-reads.csv';

            // Initialize CSV content as a string
            $csv_content = '';

            // Add CSV Header Row
            $csv_content .= implode( ',', [
                'ID',
                'post_title',
                'post_excerpt',
                '_myreads_author',
                '_myreads_format',
                '_myreads_rating',
                '_myreads_ratingStyle',
                '_myreads_isFavorite',
                '_myreads_amazonLink'
            ] ) . "\n";

            // Fetch My Reads Posts
            $args = [
                'post_type'      => 'myreads',
                'posts_per_page' => -1,
                'post_status'    => 'publish',
            ];
            $query = new WP_Query( $args );

            if ( $query->have_posts() ) {
                while ( $query->have_posts() ) {
                    $query->the_post();

                    // Get post data and meta
                    $row = [
                        get_the_ID(),
                        '"' . str_replace( '"', '""', get_the_title() ) . '"', // Escape quotes
                        '"' . str_replace( '"', '""', wp_strip_all_tags( get_the_excerpt() ) ) . '"',
                        '"' . str_replace( '"', '""', get_post_meta( get_the_ID(), '_myreads_author', true ) ) . '"',
                        '"' . str_replace( '"', '""', get_post_meta( get_the_ID(), '_myreads_format', true ) ) . '"',
                        '"' . str_replace( '"', '""', get_post_meta( get_the_ID(), '_myreads_rating', true ) ) . '"',
                        '"' . str_replace( '"', '""', get_post_meta( get_the_ID(), '_myreads_ratingStyle', true ) ) . '"',
                        '"' . str_replace( '"', '""', get_post_meta( get_the_ID(), '_myreads_isFavorite', true ) ) . '"',
                        '"' . str_replace( '"', '""', get_post_meta( get_the_ID(), '_myreads_amazonLink', true ) ) . '"',
                    ];

                    // Convert array to CSV format and add to content
                    $csv_content .= implode( ',', $row ) . "\n";
                }
                wp_reset_postdata();
            }

            // Write CSV file using WP_Filesystem
            if ( ! $wp_filesystem->put_contents( $csv_path, $csv_content, FS_CHMOD_FILE ) ) {
                wp_die( esc_html__( 'Failed to create the CSV file.', 'my-reads' ) );
            }

            // Serve the file for download
            header( 'Content-Type: text/csv; charset=utf-8' );
            header( 'Content-Disposition: attachment; filename=my-reads.csv' );
            echo wp_kses_post( $wp_filesystem->get_contents( $csv_path ) );

            // Cleanup: Delete the temporary file after serving
            $wp_filesystem->delete( $csv_path );

            exit; // Stop further execution
        }
    }

    /**
     * My Reads CPT settings submenu page HTML
     * @return void
     */
    public function myreads_cpt_settings_callback() {
        // Display My Reads settings page.
        ?>
      <div class="wrap">
        <h1><?php wp_kses_post( __( 'My Reads Settings', 'my-reads' ) ) ?></h1>
        <h2>Regenerate My Reads JSON</h2>
        <p>
            Click the button below to regenerate the JSON file for all reads.<br/>
            This will create an updated file based on the current reads.
        </p>
        <button id="regenerate-json-btn" class="button button-primary">
            Regenerate My Reads JSON
        </button>
        <br/>
        <br/>
        <hr/>
        <h2>Upload CSV for My Reads</h2>
        <form method="post" action="options.php" enctype="multipart/form-data">
        <?php
          settings_fields( 'myreads_settings_group' );
        do_settings_sections( 'myreads_settings' );
        ?>
          <table class="form-table">
            <tr valign="top">
              <th scope="row">CSV File Upload</th>
              <td>
                <input type="file" name="myreads_csv_file" accept=".csv" />
                <?php wp_nonce_field( 'myreads_csv_file_action', 'myreads_csv_file_nonce' ); ?>
              </td>
            </tr>
          </table>
          <?php submit_button( 'Upload file' ); ?>
        </form>
        <br/>
        <hr/>
        <h2>Download CSV for My Reads</h2>
        <form method="get" action="">
            <input type="hidden" name="action" value="download_myreads_csv">
            <?php submit_button( 'Download CSV' ); ?>
        </form>
    </div>
        <?php
    }
}

new MyReads_Settings();
