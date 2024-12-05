<?php
/*
Plugin Name: Udemy API for WP
Plugin URI: https://github.com/tylerkeithullery/UdemyAPI4WP
Description: A plugin to fetch and display information from Udemy Instructor API.
Version: 1.0
Author: Tyler K Ullery
Author URI: https://github.com/tylerkeithullery

* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
* even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

// Hook to create custom table on plugin activation
register_activation_hook(__FILE__, 'uci_create_table');

function uci_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'udemy_courses';
    $charset_collate = $wpdb->get_charset_collate();

    // SQL query to create the custom table
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        course_id varchar(255) NOT NULL,
        course_title text NOT NULL,
        headline text NOT NULL,
        is_paid tinyint(1) NOT NULL,
        is_published tinyint(1) NOT NULL,
        num_reviews int NOT NULL,
        published_time datetime NOT NULL,
        published_title text NOT NULL,
        rating float NOT NULL,
        url text NOT NULL,
        created datetime NOT NULL,
        last_updated datetime NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Schedule the cron job on plugin activation
register_activation_hook(__FILE__, 'uci_schedule_cron_job');
function uci_schedule_cron_job() {
    if (!wp_next_scheduled('uci_update_courses_event')) {
        wp_schedule_event(time(), 'twicedaily', 'uci_update_courses_event');
    }
}

// Clear the cron job on plugin deactivation
register_deactivation_hook(__FILE__, 'uci_clear_cron_job');
function uci_clear_cron_job() {
    $timestamp = wp_next_scheduled('uci_update_courses_event');
    wp_unschedule_event($timestamp, 'uci_update_courses_event');
}

// Hook the cron job to the data update function
add_action('uci_update_courses_event', 'uci_update_table');

// Add admin menu
add_action('admin_menu', 'uci_admin_menu');

function uci_admin_menu() {
    // Add main menu page
    add_menu_page('Udemy Course Info', 'Udemy Course Info', 'manage_options', 'udemy-course-info', 'uci_admin_page');
    // Add submenu pages
    add_submenu_page('udemy-course-info', 'Setup', 'Setup', 'manage_options', 'udemy-course-info-setup', 'uci_setup_page');
    add_submenu_page('udemy-course-info', 'Export', 'Export', 'manage_options', 'udemy-course-info-export', 'uci_export_page');
    add_submenu_page('udemy-course-info', 'Debug', 'Debug', 'manage_options', 'udemy-course-info-debug', 'uci_debug_page');
}

// Main admin page
function uci_admin_page() {
    $secret_token = get_option('udemy_secret_token', '');

    // Redirect to setup page if secret token is not set
    if (empty($secret_token)) {
        wp_redirect(admin_url('admin.php?page=udemy-course-info-setup&redirected=true'));
        exit;
    }

    // Handle form submissions
    if (isset($_POST['update_table'])) {
        uci_update_table();
    }

    // Fetch data from the database
    global $wpdb;
    $table_name = $wpdb->prefix . 'udemy_courses';

    $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'last_updated';
    $order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';

    $courses = $wpdb->get_results("SELECT * FROM $table_name ORDER BY $orderby $order");
    $last_updated = $wpdb->get_var("SELECT MAX(last_updated) FROM $table_name");

    ?>
    <div class="wrap">
        <h1>Udemy Course Info</h1>
        <form method="post" action="" id="uci-export-form">
            <input type="submit" name="update_table" value="Update Table" class="button button-primary"/>
            <input type="button" id="uci-export-button" value="Export Table" class="button button-secondary"/>
        </form>
        <h2>Courses <em>(Last Updated: <?php echo esc_html($last_updated); ?>)</em></h2>
        <style>
            .widefat th, .widefat td {
                padding: 8px 10px;
                border-bottom: 1px solid #ddd;
            }
            .widefat th {
                background-color: #f9f9f9;
            }
            .widefat tr:nth-child(even) {
                background-color: #f1f1f1;
            }
        </style>
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
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
                        $sort_order = ($orderby === $column && $order === 'ASC') ? 'DESC' : 'ASC';
                        $sort_icon = ($orderby === $column) ? ($order === 'ASC' ? '↑' : '↓') : '';
                        echo "<th><a href='?page=udemy-course-info&orderby=$column&order=$sort_order'>$display_name $sort_icon</a></th>";
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($courses)) : ?>
                    <?php foreach ($courses as $course) : ?>
                        <tr>
                            <td><?php echo esc_html($course->course_id); ?></td>
                            <td><?php echo esc_html($course->course_title); ?></td>
                            <td><?php echo esc_html($course->headline); ?></td>
                            <td><?php echo esc_html($course->is_paid ? 'Yes' : 'No'); ?></td>
                            <td><?php echo esc_html($course->is_published ? 'Yes' : 'No'); ?></td>
                            <td><?php echo esc_html($course->num_reviews); ?></td>
                            <td><?php echo esc_html($course->published_time); ?></td>
                            <td><?php echo esc_html($course->published_title); ?></td>
                            <td><?php echo esc_html($course->rating); ?></td>
                            <td><a href="<?php echo esc_url($course->url); ?>" target="_blank"><?php echo esc_html($course->url); ?></a></td>
                            <td><?php echo esc_html($course->created); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="11">No courses found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <script>
        document.getElementById('uci-export-button').addEventListener('click', function() {
            window.location.href = '<?php echo admin_url('admin.php?page=udemy-course-info-export'); ?>';
        });
    </script>
    <?php
}

// Function to update the table with data from Udemy API
function uci_update_table() {
    $api_url = 'https://www.udemy.com/instructor-api/v1/taught-courses/courses?fields[course]=id,title,headline,is_paid,is_published,num_reviews,published_time,published_title,rating,url,created';
    $secret_token = get_option('udemy_secret_token', '');

    if (empty($secret_token)) {
        echo '<div class="notice notice-error"><p>Please set your Udemy secret token in the setup page.</p></div>';
        return;
    }

    $response = wp_remote_get($api_url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $secret_token
        )
    ));

    if (is_wp_error($response)) {
        update_option('udemy_last_api_error', $response->get_error_message());
        echo '<div class="notice notice-error"><p>Failed to connect to Udemy API. Please try again later.</p></div>';
        return;
    }

    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);

    if ($response_code >= 200 && $response_code < 300) {
        update_option('udemy_last_api_error', 'None');
        $courses = json_decode($response_body, true);

        if (!empty($courses['results'])) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'udemy_courses';

            // Clear existing data
            $wpdb->query("TRUNCATE TABLE $table_name");

            $current_time = current_time('mysql');

            foreach ($courses['results'] as $course) {
                $full_url = 'https://www.udemy.com' . $course['url'];

                $wpdb->insert($table_name, array(
                    'course_id' => $course['id'],
                    'course_title' => $course['title'],
                    'headline' => $course['headline'],
                    'is_paid' => $course['is_paid'],
                    'is_published' => $course['is_published'],
                    'num_reviews' => $course['num_reviews'],
                    'published_time' => $course['published_time'],
                    'published_title' => $course['published_title'],
                    'rating' => $course['rating'],
                    'url' => $full_url,
                    'created' => $course['created'],
                    'last_updated' => $current_time
                ));
            }

            update_option('udemy_manual_update', $current_time);
            echo '<div class="notice notice-success"><p>Table updated successfully.</p></div>';
        } else {
            echo '<div class="notice notice-warning"><p>No courses found in the Udemy API response.</p></div>';
        }
    } else {
        $error_message = 'An unexpected error occurred. Please try again later.';
        switch ($response_code) {
            case 400:
                $error_message = 'Bad Request. Please check the API request parameters.';
                break;
            case 401:
                $error_message = 'Unauthorized. Please check your API credentials.';
                break;
            case 403:
                $error_message = 'Forbidden. You do not have permission to access this resource.';
                break;
            case 404:
                $error_message = 'Not Found. The requested resource could not be found.';
                break;
            case 429:
                $error_message = 'Too Many Requests. Please slow down your request rate.';
                break;
            case 500:
                $error_message = 'Internal Server Error. Please try again later.';
                break;
            case 503:
                $error_message = 'Service Unavailable. The server is currently unable to handle the request. Please try again later.';
                break;
        }
        update_option('udemy_last_api_error', $error_message);
        echo '<div class="notice notice-error"><p>' . esc_html($error_message) . '</p></div>';
    }
}

// Include the setup and export pages
include plugin_dir_path(__FILE__) . 'setup.php';
include plugin_dir_path(__FILE__) . 'export_table.php';
include plugin_dir_path(__FILE__) . 'debuguafw.php';