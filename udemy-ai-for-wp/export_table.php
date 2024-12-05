<?php
// Function to render the export page
function uci_export_page() {
    ?>
    <div class="wrap">
        <h1>Export Udemy Courses</h1>
        <form method="post" action="" id="export-form">
            <?php wp_nonce_field('uci_export_nonce', 'uci_export_nonce_field'); ?>
            <h2>Select Export Format</h2>
            <select name="export_format">
                <option value="csv">CSV</option>
                <option value="json">JSON</option>
                <option value="xml">XML</option>
            </select>
            <h2>Select Columns to Export</h2>
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
                echo "<label><input type='checkbox' name='columns[]' value='$column' checked> $display_name</label><br>";
            }
            ?>
            <input type="submit" name="export_table" value="Export" class="button button-primary"/>
            <div id="loading-indicator" style="display:none;">Exporting, please wait...</div>
        </form>
    </div>
    <script type="text/javascript">
        document.getElementById('export-form').addEventListener('submit', function(event) {
            event.preventDefault();
            document.getElementById('loading-indicator').style.display = 'block';
            
            var formData = new FormData(this);
            fetch('', {
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
                document.querySelector('.wrap').insertAdjacentHTML('beforeend', '<div class="notice notice-success"><p>Export successful!</p></div>');
            })
            .catch(error => {
                document.getElementById('loading-indicator').style.display = 'none';
                document.querySelector('.wrap').insertAdjacentHTML('beforeend', '<div class="notice notice-error"><p>An error occurred during export. Please try again.</p></div>');
            });
        });
    </script>
    <?php
    // Handle form submission
    if (isset($_POST['export_table'])) {
        if (!isset($_POST['uci_export_nonce_field']) || !wp_verify_nonce($_POST['uci_export_nonce_field'], 'uci_export_nonce')) {
            echo '<div class="notice notice-error"><p>Nonce verification failed. Please try again.</p></div>';
            return;
        }

        $format = sanitize_text_field($_POST['export_format']);
        $selected_columns = isset($_POST['columns']) ? array_map('sanitize_text_field', $_POST['columns']) : array();

        // Check if at least one column is selected
        if (empty($selected_columns)) {
            echo '<div class="notice notice-error"><p>Please select at least one column to export.</p></div>';
        } else {
            // Call the function to export data
            uci_export_data($format, $selected_columns);
        }
    }
}

// Function to export data in the selected format
function uci_export_data($format, $columns) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'udemy_courses';
    $columns_list = implode(',', $columns);
    // Fetch the data from the database
    $courses = $wpdb->get_results("SELECT $columns_list FROM $table_name", ARRAY_A);

    // Check if there are any courses to export
    if (empty($courses)) {
        echo '<div class="notice notice-warning"><p>No courses found to export.</p></div>';
        return;
    }

    $filename = 'udemy_courses_' . date('Ymd') . '.' . $format;
    header('Content-Disposition: attachment;filename=' . $filename);

    // Clear the output buffer to prevent any extra information from being included
    ob_clean();
    flush();

    try {
        // Export data in the selected format
        switch ($format) {
            case 'json':
                header('Content-Type: application/json');
                echo json_encode($courses);
                break;
            case 'xml':
                header('Content-Type: text/xml');
                $xml = new SimpleXMLElement('<courses/>');
                foreach ($courses as $course) {
                    $course_xml = $xml->addChild('course');
                    foreach ($course as $key => $value) {
                        $course_xml->addChild($key, htmlspecialchars($value));
                    }
                }
                echo $xml->asXML();
                break;
            case 'csv':
            default:
                header('Content-Type: text/csv');
                $output = fopen('php://output', 'w');
                // Write the column headers
                fputcsv($output, $columns);
                // Write the data rows
                foreach ($courses as $course) {
                    fputcsv($output, $course);
                }
                fclose($output);
                break;
        }
        echo '<div class="notice notice-success"><p>Export successful!</p></div>';
    } catch (Exception $e) {
        // Log the error and display a message to the user
        error_log('Export error: ' . $e->getMessage());
        echo '<div class="notice notice-error"><p>An error occurred during export. Please check the error log for details.</p></div>';
    }
    exit;
}
