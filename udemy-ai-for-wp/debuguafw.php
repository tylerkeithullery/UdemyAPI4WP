<?php
function uci_debug_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    check_admin_referer('uci_debug_nonce');

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
        <p>When reporting a bug or issue on GitHub (<a href="https://github.com/tylerkeithullery/UdemyAPI4WP/issues" target="_blank">https://github.com/tylerkeithullery/UdemyAPI4WP/issues</a>) please copy all this information into the report.</p>
        <form method="post">
            <?php wp_nonce_field('uci_debug_nonce'); ?>
            <textarea readonly rows="20" cols="100"><?php
            echo "Plugin Version: 1.0\n";
            echo "WordPress Version: " . esc_html(get_bloginfo('version')) . "\n";
            echo "PHP Version: " . esc_html(phpversion()) . "\n";
            echo "Number of Courses: " . esc_html($courses_count) . "\n";
            echo "Last Updated: " . esc_html($last_updated) . "\n";
            echo "Secret Token Set: " . esc_html(!empty($secret_token) ? 'Yes' : 'No') . "\n";
            echo "Last API Error: " . esc_html($last_api_error) . "\n";
            echo "Last Row/Table Change: " . esc_html($last_row_change) . "\n";
            echo "Manual Update: " . esc_html($manual_update) . "\n";
            echo "Valid Database: " . esc_html($db_valid) . "\n";
            echo "Active Theme: " . esc_html($active_theme->get('Name')) . " (Version: " . esc_html($active_theme->get('Version')) . ")\n";
            echo "Active Plugins:\n";
            foreach ($active_plugins as $plugin) {
                echo "- " . esc_html($plugin) . "\n";
            }
            echo "Server Information: " . esc_html($server_info) . "\n";
            ?>
            </textarea>
        </form>
    </div>
    <?php
}
?>
