<?php

// Hook to clear the cron job and perform cleanup tasks on plugin deactivation
add_action('deactivated_plugin', 'uci_clear_cron_job', 10, 2);
function uci_clear_cron_job($plugin, $network_deactivating) {
    if (plugin_basename(__FILE__) === $plugin) {
        // Log the function trigger for debugging purposes
        if (function_exists('error_log')) {
            error_log('uci_clear_cron_job function triggered');
        }
        // Unschedule the cron job
        uci_unschedule_cron_job();
        // Delete the custom database table
        uci_delete_custom_table();
        // Delete the secret token option
        uci_delete_secret_token();
    }
}

// Unschedule the custom cron job if it exists
function uci_unschedule_cron_job() {
    // Log the function trigger for debugging purposes
    if (function_exists('error_log')) {
        error_log('uci_unschedule_cron_job function triggered');
    }
    $timestamp = wp_next_scheduled('uci_update_courses_event');
    if ($timestamp !== false) {
        wp_unschedule_event($timestamp, 'uci_update_courses_event');
        error_log('uci_update_courses_event unscheduled');
    } else {
        error_log('uci_update_courses_event not found');
    }
}

// Delete the custom database table used by the plugin
function uci_delete_custom_table() {
    // Log the function trigger for debugging purposes
    if (function_exists('error_log')) {
        error_log('uci_delete_custom_table function triggered');
    }
    global $wpdb;
    $table_name = $wpdb->prefix . 'uci_courses';
    $result = $wpdb->query("DROP TABLE IF EXISTS `{$table_name}`");
    if ($result !== false) {
        error_log("Table {$table_name} deleted");
    } else {
        error_log("Failed to delete table {$table_name}");
    }
}

// Delete the secret token option from the database
function uci_delete_secret_token() {
    // Log the function trigger for debugging purposes
    if (function_exists('error_log')) {
        error_log('uci_delete_secret_token function triggered');
    }
    if (delete_option('uci_secret_token')) {
        error_log('uci_secret_token deleted');
    } else {
        error_log('uci_secret_token not found');
    }
}