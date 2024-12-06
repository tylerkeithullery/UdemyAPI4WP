<?php

// Clear the cron job on plugin deactivation
add_action('deactivated_plugin', 'uci_clear_cron_job', 10, 2);
function uci_clear_cron_job($plugin, $network_deactivating) {
    if (plugin_basename(__FILE__) === $plugin) {
        if (function_exists('error_log')) {
            error_log('uci_clear_cron_job function triggered');
        }
        uci_unschedule_cron_job();
        uci_delete_custom_table();
        uci_delete_secret_token();
    }
}

function uci_unschedule_cron_job() {
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

function uci_delete_custom_table() {
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

function uci_delete_secret_token() {
    if (function_exists('error_log')) {
        error_log('uci_delete_secret_token function triggered');
    }
    if (delete_option('uci_secret_token')) {
        error_log('uci_secret_token deleted');
    } else {
        error_log('uci_secret_token not found');
    }
}