<?php
function uci_debug_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'udemy_courses';
    $courses_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $last_updated = $wpdb->get_var("SELECT MAX(last_updated) FROM $table_name");
    $secret_token = get_option('udemy_secret_token', '');
    $last_api_error = get_option('udemy_last_api_error', 'None');
    $last_row_change = $wpdb->get_var("SELECT MAX(UPDATE_TIME) FROM information_schema.tables WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table_name'");
    $manual_update = get_option('udemy_manual_update', 'Never');
    $db_valid = $wpdb->check_connection() ? 'Yes' : 'No';

    ?>
    <div class="wrap">
        <h1>Debug Information</h1>
        <textarea readonly rows="20" cols="100">
        <?php
        echo "Plugin Version: 1.0\n";
        echo "WordPress Version: " . get_bloginfo('version') . "\n";
        echo "PHP Version: " . phpversion() . "\n";
        echo "Database Table: $table_name\n";
        echo "Number of Courses: $courses_count\n";
        echo "Last Updated: $last_updated\n";
        echo "Secret Token Set: " . (!empty($secret_token) ? 'Yes' : 'No') . "\n";
        echo "Last API Error: $last_api_error\n";
        echo "Last Row/Table Change: $last_row_change\n";
        echo "Manual Update: $manual_update\n";
        echo "Valid Database: $db_valid\n";
        ?>
        </textarea>
    </div>
    <?php
}
?>
