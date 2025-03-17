<?php

class My_Reads_Settings {
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'my_reads_cpt_settings_submenu' ] );
        add_action( 'custom_menu_order', [ $this, 'my_reads_cpt_settings_menu_order' ] );
        add_action( 'admin_init', [ $this, 'my_reads_register_settings' ] );
        add_action( 'admin_notices', [ $this, 'my_reads_admin_notice' ] );
    }

    /**
     * Add a submenu page to the My Reads menu
     * @return void
     */
    public function my_reads_cpt_settings_submenu() {
        add_submenu_page(
            'edit.php?post_type=my_reads',
            __( 'My Reads Settings', 'my-reads' ),
            __( 'Settings', 'my-reads' ),
            'manage_options',
            'my-reads-cpt-settings',
            [ $this, 'my_reads_cpt_settings_callback' ]
        );
    }

    public function my_reads_admin_notice() {
        // Check if the success message transient exists
        if ( $message = get_transient( 'my_reads_csv_import_success' ) ) {
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
    public function my_reads_cpt_settings_menu_order() {
        global $submenu;
        if ( !empty( $submenu['my-reads-cpt-settings'] ) && !empty( $submenu['my-reads-cpt-settings'][0] ) ) {
            $submenu['my-reads-cpt-settings'][100] = $submenu['my-reads-cpt-settings'][0];
            unset( $submenu['my-reads-cpt-settings'][0] );
        }
    }

    // Register the setting
    public function my_reads_register_settings() {
        register_setting( 'my_reads_settings_group', 'my_reads_csv_file', [
            'sanitize_callback' => [ $this, 'my_reads_file_upload' ]
        ] );
    }

    // Handle the file upload
    public function my_reads_file_upload( $file ) {
        if ( !empty( $_FILES['my_reads_csv_file']['name'] ) ) {
            // Use WordPress's file upload functionality
            $uploaded_file = $_FILES['my_reads_csv_file'];

            // Check if file is a CSV
            if ( strtolower( pathinfo( $uploaded_file['name'], PATHINFO_EXTENSION ) ) !== 'csv' ) {
                wp_die( 'Only CSV files are allowed.' );
            }

            // Move the uploaded file to the WordPress uploads directory
            $upload = wp_handle_upload( $uploaded_file, ['test_form' => false] );
            if ( isset( $upload['file'] ) ) {
                $this->import_my_reads_csv( $upload['file'] );
            } else {
                wp_die( 'File upload failed.' );
            }

            // Delete the file after processing.
            wp_delete_file( $upload['file'] );
        }
    }

    public function generate_post_content( $author ) {
        // Get the my-reads-default.php pattern content
        ob_start();
        include MY_READS_PATH . '/patterns/my-reads-default.php';
        $content = ob_get_clean();
        // Replace the placeholder with the actual author name
        $content = str_replace( 'Author:', "Author: $author", $content );
        return $content;
    }

    public function import_my_reads_csv( $file ) {
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

            // Create a new post (for "my_reads" post type)
            $post_data = [
                'post_title'   => $title,
                'post_content' => $this->generate_post_content( $author ), // Custom content formatting function
                'post_type'    => 'my_reads',
                'post_status'  => 'publish',
            ];

            // Insert the post into the database
            $post_id = wp_insert_post( $post_data );

            // If post creation succeeded, proceed with adding meta and taxonomies
            if ( !is_wp_error( $post_id ) ) {
                // Add format meta
                update_post_meta( $post_id, '_my_reads_format', $format );

                // Add rating meta
                update_post_meta( $post_id, '_my_reads_rating', $rating );
                update_post_meta( $post_id, '_my_reads_ratingStyle', 'star' ); // Default rating style

                // Mark as not favorite by default
                update_post_meta( $post_id, '_my_reads_isFavorite', false );

                // Add Year taxonomy (use the Year as the term slug)
                wp_set_object_terms( $post_id, $year, 'my_reads_year' );
            }
        }

        set_transient( 'my_reads_csv_import_success', 'CSV file uploaded and imported successfully!', 5 ); // 5 seconds
    }

    /**
     * My Reads CPT settings submenu page HTML
     * @return void
     */
    public function my_reads_cpt_settings_callback() {
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
        <script>
        document.getElementById('regenerate-json-btn').addEventListener('click', function() {
            const apiUrl = '<?php echo esc_url( site_url( '/wp-json/my-reads/v1/all-the-reads/?refresh=true' ) ); ?>';

            fetch(apiUrl, {
                method: 'GET',  // Adjust the method based on what your endpoint expects
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>' // Ensures security if needed
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Success:', data);
                alert('JSON Regeneration Complete!');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error occurred while regenerating JSON.');
            });
        });
        </script>
        <br/>
        <br/>
        <hr/>
        <h2>Upload CSV for My Reads</h2>
        <form method="post" action="options.php" enctype="multipart/form-data">
          <?php
            settings_fields( 'my_reads_settings_group' );
        do_settings_sections( 'my_reads_settings' );
        ?>
          <table class="form-table">
            <tr valign="top">
              <th scope="row">CSV File Upload</th>
              <td>
                <input type="file" name="my_reads_csv_file" accept=".csv" />
              </td>
            </tr>
          </table>
          <?php submit_button( 'Upload file' ); ?>
        </form>

    </div>
        <?php
    }
}

new My_Reads_Settings();
