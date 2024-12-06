<?php

// Register the table creation function on plugin activation
register_activation_hook(__FILE__, 'uci_create_table');

// Create the custom table on plugin activation
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