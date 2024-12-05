<?php
function uci_export_page() {
    ?>
    <div class="wrap">
        <h1>Export Udemy Courses</h1>
        <form method="post" action="">
            <h2>Select Export Format</h2>
            <select name="export_format">
                <option value="csv">CSV</option>
                <option value="json">JSON</option>
                <option value="xml">XML</option>
            </select>
            <h2>Select Columns to Export</h2>
            <?php
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
            foreach ($columns as $column => $display_name) {
                echo "<label><input type='checkbox' name='columns[]' value='$column' checked> $display_name</label><br>";
            }
            ?>
            <input type="submit" name="export_table" value="Export" class="button button-primary"/>
        </form>
    </div>
    <?php
    if (isset($_POST['export_table'])) {
        $format = sanitize_text_field($_POST['export_format']);
        $selected_columns = isset($_POST['columns']) ? array_map('sanitize_text_field', $_POST['columns']) : array();

        if (empty($selected_columns)) {
            echo '<div class="notice notice-error"><p>Please select at least one column to export.</p></div>';
        } else {
            uci_export_data($format, $selected_columns);
        }
    }
}

function uci_export_data($format, $columns) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'udemy_courses';
    $columns_list = implode(',', $columns);
    $courses = $wpdb->get_results("SELECT $columns_list FROM $table_name", ARRAY_A);

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
                fputcsv($output, $columns);
                foreach ($courses as $course) {
                    fputcsv($output, $course);
                }
                fclose($output);
                break;
        }
    } catch (Exception $e) {
        error_log('Export error: ' . $e->getMessage());
        echo '<div class="notice notice-error"><p>An error occurred during export. Please check the error log for details.</p></div>';
    }
    exit;
}
