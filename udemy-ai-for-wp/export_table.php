<?php
// Function to render the export page
function uci_export_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Export Udemy Courses', 'text-domain'); ?></h1>
        <form method="post" action="" id="export-form">
            <?php wp_nonce_field('uci_export_nonce', 'uci_export_nonce_field'); ?>
            <h2><?php esc_html_e('Select Export Format', 'text-domain'); ?></h2>
            <select name="export_format">
                <option value="csv"><?php esc_html_e('CSV', 'text-domain'); ?></option>
                <option value="json"><?php esc_html_e('JSON', 'text-domain'); ?></option>
                <option value="xml"><?php esc_html_e('XML', 'text-domain'); ?></option>
            </select>
            <h2><?php esc_html_e('Select Columns to Export', 'text-domain'); ?></h2>
            <?php
            // Define the columns available for export
            $columns = array(
                'course_id' => 'Course ID',
                'course_title' => 'Title',
                'headline' => 'Headline',
                'is_paid' => 'Paid',
                'is_published' => 'Published',
                'num_reviews' => 'Reviews',
                'published_time' => 'Published Time',
                'published_title' => 'Published Title',
                'rating' => 'Rating',
                'url' => 'URL',
                'created' => 'Created'
            );
            // Display checkboxes for each column
            foreach ($columns as $column => $display_name) {
                echo "<label><input type='checkbox' name='columns[]' value='" . esc_attr($column) . "' checked> " . esc_html($display_name) . "</label><br>";
            }
            ?>
            <input type="submit" name="export_table" value="<?php esc_attr_e('Export', 'text-domain'); ?>" class="button button-primary"/>
            <div id="loading-indicator" style="display:none;"><?php esc_html_e('Exporting, please wait...', 'text-domain'); ?></div>
        </form>
    </div>
    <script type="text/javascript">
        document.getElementById('export-form').addEventListener('submit', function(event) {
            event.preventDefault();
            document.getElementById('loading-indicator').style.display = 'block';
            
            var formData = new FormData(this);
            formData.append('action', 'uci_export_data'); // Add action parameter for AJAX request
            fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.blob();
            })
            .then(blob => {
                document.getElementById('loading-indicator').style.display = 'none';
                var url = window.URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = 'udemy_courses_export.' + formData.get('export_format');
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
                document.querySelector('.wrap').insertAdjacentHTML('beforeend', '<div class="notice notice-success"><p><?php esc_html_e('Export successful!', 'text-domain'); ?></p></div>');
            })
            .catch(error => {
                document.getElementById('loading-indicator').style.display = 'none';
                document.querySelector('.wrap').insertAdjacentHTML('beforeend', `<div class="notice notice-error"><p><?php esc_html_e('An error occurred during export:', 'text-domain'); ?> ${error.message}</p></div>`);
            });
        });
    </script>
    <?php
}

// Add action to handle the AJAX request
add_action('wp_ajax_uci_export_data', 'uci_export_data');

// Function to export data in the selected format
function uci_export_data() {
    if (!isset($_POST['uci_export_nonce_field']) || !wp_verify_nonce($_POST['uci_export_nonce_field'], 'uci_export_nonce')) {
        wp_send_json_error('Nonce verification failed.');
        return;
    }

    $format = sanitize_text_field($_POST['export_format']);
    $selected_columns = isset($_POST['columns']) ? array_map('sanitize_text_field', $_POST['columns']) : array();

    // Check if at least one column is selected
    if (empty($selected_columns)) {
        wp_send_json_error('Please select at least one column to export.');
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'udemy_courses';
    $columns_list = implode(',', $selected_columns);

    // Attempt to get cached data
    $cache_key = 'udemy_courses_' . md5($columns_list);
    $courses = wp_cache_get($cache_key);

    if ($courses === false) {
        // Fetch the data from the database if not cached
        $courses = $wpdb->get_results("SELECT DISTINCT $columns_list FROM $table_name", ARRAY_A);
        // Cache the data
        wp_cache_set($cache_key, $courses);
    }

    // Check if there are any courses to export
    if (empty($courses)) {
        wp_send_json_error('No courses found to export.');
        return;
    }

    $filename = 'udemy_courses_' . gmdate('Ymd') . '.' . $format;
    header('Content-Disposition: attachment;filename=' . $filename);

    // Clear the output buffer to prevent any extra information from being included
    ob_clean();
    flush();

    try {
        // Export data in the selected format
        switch ($format) {
            case 'json':
                header('Content-Type: application/json');
                echo wp_json_encode($courses);
                break;
            case 'xml':
                header('Content-Type: text/xml');
                $xml = new SimpleXMLElement('<courses/>');
                foreach ($courses as $course) {
                    $course_xml = $xml->addChild('course');
                    foreach ($course as $key => $value) {
                        $course_xml->addChild($key, esc_html($value));
                    }
                }
                echo $xml->asXML();
                break;
            case 'csv':
            default:
                header('Content-Type: text/csv');
                $output = fopen('php://output', 'w');
                // Write the column headers
                fputcsv($output, array_map('esc_html', array_keys($courses[0])));
                // Write the data rows
                foreach ($courses as $course) {
                    fputcsv($output, array_map('esc_html', $course));
                }
                fclose($output);
                break;
        }
        exit;
    } catch (Exception $e) {
        // Log the error and display a message to the user
        error_log('Export error: ' . $e->getMessage());
        wp_send_json_error('An error occurred during export: ' . esc_html($e->getMessage()));
    }
}
