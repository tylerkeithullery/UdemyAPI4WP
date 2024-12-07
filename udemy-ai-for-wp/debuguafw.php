<?php
function uci_debug_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['uci_debug_nonce']) && !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['uci_debug_nonce'])), 'uci_debug_nonce')) {
        return;
    }

    global $wpdb;
    
    // Cache key for courses count
    $courses_count_cache_key = 'uci_courses_count';
    $courses_count = wp_cache_get($courses_count_cache_key);
    if ($courses_count === false) {
        $courses_count = (int) get_transient($courses_count_cache_key);
        if ($courses_count === false) {
            $courses_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}udemy_courses");
            set_transient($courses_count_cache_key, $courses_count, HOUR_IN_SECONDS);
        }
    }

    // Cache key for last updated
    $last_updated_cache_key = 'uci_last_updated';
    $last_updated = wp_cache_get($last_updated_cache_key);
    if ($last_updated === false) {
        $last_updated = get_transient($last_updated_cache_key);
        if ($last_updated === false) {
            $last_updated = $wpdb->get_var("SELECT MAX(last_updated) FROM {$wpdb->prefix}udemy_courses");
            set_transient($last_updated_cache_key, $last_updated, HOUR_IN_SECONDS);
        }
    }

    // Cache key for last row change
    $last_row_change_cache_key = 'uci_last_row_change';
    $last_row_change = wp_cache_get($last_row_change_cache_key);
    if ($last_row_change === false) {
        $last_row_change = get_transient($last_row_change_cache_key);
        if ($last_row_change === false) {
            $last_row_change = $wpdb->get_var("SELECT MAX(UPDATE_TIME) FROM information_schema.tables WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$wpdb->prefix}udemy_courses'");
            set_transient($last_row_change_cache_key, $last_row_change, HOUR_IN_SECONDS);
        }
    }

    // Cache key for reviews count
    $reviews_count_cache_key = 'uci_reviews_count';
    $reviews_count = wp_cache_get($reviews_count_cache_key);
    if ($reviews_count === false) {
        $reviews_count = (int) get_transient($reviews_count_cache_key);
        if ($reviews_count === false) {
            $reviews_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}udemy_reviews");
            set_transient($reviews_count_cache_key, $reviews_count, HOUR_IN_SECONDS);
        }
    }

    // Cache key for last review date
    $last_review_date_cache_key = 'uci_last_review_date';
    $last_review_date = wp_cache_get($last_review_date_cache_key);
    if ($last_review_date === false) {
        $last_review_date = get_transient($last_review_date_cache_key);
        if ($last_review_date === false) {
            $last_review_date = $wpdb->get_var("SELECT MAX(created) FROM {$wpdb->prefix}udemy_reviews");
            set_transient($last_review_date_cache_key, $last_review_date, HOUR_IN_SECONDS);
        }
    }

    $secret_token = get_option('udemy_secret_token', '');
    $last_api_error = get_option('udemy_last_api_error', 'None');
    $manual_update = get_option('udemy_manual_update', 'Never');
    $db_valid = $wpdb->check_connection() ? 'Yes' : 'No';
    $active_theme = wp_get_theme();
    $active_plugins = get_option('active_plugins');
    $server_info = isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : 'Unknown';
    $setup_complete = get_option('uci_setup_complete', false);

    // Fetch row count after truncate and insert from options
    $row_count_after_truncate = 'N/A';
    $row_count_after_insert = 'N/A';

    if (isset($_POST['export_format']) && isset($_POST['columns'])) {
        $export_format = sanitize_text_field(wp_unslash($_POST['export_format']));
        $columns = array_map('sanitize_text_field', wp_unslash($_POST['columns']));
        $columns_list = implode(', ', $columns);
        $table_name = $wpdb->prefix . 'udemy_courses';
        $query = $wpdb->prepare("SELECT $columns_list FROM $table_name WHERE export_format = %s", $export_format);
        $results = $wpdb->get_results($query);
    }

    ?>
    <div class="wrap">
        <h1>Debug Information</h1>
        <p>When reporting a bug or issue on (<a href="https://github.com/tylerkeithullery/UdemyAPI4WP/issues" target="_blank">GitHub</a> please copy all this information into the report.</p>
        <form method="post">
            <?php wp_nonce_field('uci_debug_nonce'); ?>
            <textarea id="debug-info" readonly rows="20" cols="100"><?php
            echo "Plugin Version: 1.0\n";
            echo "WordPress Version: " . esc_html(get_bloginfo('version')) . "\n";
            echo "PHP Version: " . esc_html(phpversion()) . "\n";
            echo "Setup Completed: " . esc_html($setup_complete ? 'Yes' : 'No') . "\n";
            echo "Number of Courses: " . esc_html($courses_count) . "\n";
            echo "Last Updated: " . esc_html($last_updated) . "\n";
            echo "Secret Token Set: " . esc_html(!empty($secret_token) ? 'Yes' : 'No') . "\n";
            echo "Number of Reviews: " . esc_html($reviews_count) . "\n";
            echo "Last Review Date: " . esc_html($last_review_date) . "\n";
            echo "Last API Error: " . esc_html($last_api_error) . "\n";
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
            <br>
            <button type="button" onclick="copyDebugInfo()">Copy</button>
        </form>
    </div>
    <script type="text/javascript">
        function copyDebugInfo() {
            var copyText = document.getElementById("debug-info");
            copyText.select();
            copyText.setSelectionRange(0, 99999); // For mobile devices
            document.execCommand("copy");
            alert("Debug content copied to clipboard");
        }
    </script>
    <?php
}
?>
