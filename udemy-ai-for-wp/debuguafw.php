<?php
function uci_debug_page() {
    global $wpdb;
    $courses_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}udemy_courses");
    $last_updated = $wpdb->get_var("SELECT MAX(last_updated) FROM {$wpdb->prefix}udemy_courses");
    $secret_token = get_option('udemy_secret_token', '');
    $last_api_error = get_option('udemy_last_api_error', 'None');
    $last_row_change = $wpdb->get_var("SELECT MAX(UPDATE_TIME) FROM information_schema.tables WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$wpdb->prefix}udemy_courses'");
    $manual_update = get_option('udemy_manual_update', 'Never');
    $db_valid = $wpdb->check_connection() ? 'Yes' : 'No';
    $active_theme = wp_get_theme();
    $active_plugins = get_option('active_plugins');
    $server_info = $_SERVER['SERVER_SOFTWARE'];

    ?>
    <div class="wrap">
        <h1>Debug Information</h1>
        <textarea readonly rows="20" cols="100">
        <?php
        echo "Plugin Version: 1.0\n";
        echo "WordPress Version: " . get_bloginfo('version') . "\n";
        echo "PHP Version: " . phpversion() . "\n";
        echo "Number of Courses: $courses_count\n";
        echo "Last Updated: $last_updated\n";
        echo "Secret Token Set: " . (!empty($secret_token) ? 'Yes' : 'No') . "\n";
        echo "Last API Error: $last_api_error\n";
        echo "Last Row/Table Change: $last_row_change\n";
        echo "Manual Update: $manual_update\n";
        echo "Valid Database: $db_valid\n";
        echo "Active Theme: " . $active_theme->get('Name') . " (Version: " . $active_theme->get('Version') . ")\n";
        echo "Active Plugins:\n";
        foreach ($active_plugins as $plugin) {
            echo "- " . $plugin . "\n";
        }
        echo "Server Information: $server_info\n";
        ?>
        </textarea>
    </div>
    <?php
}
?>
