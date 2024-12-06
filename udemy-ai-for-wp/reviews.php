<?php
global $wpdb;
$table_name = $wpdb->prefix . 'udemy_reviews';

$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE $table_name (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    course_id varchar(255) NOT NULL,
    course_title varchar(255) NOT NULL,
    content text NOT NULL,
    rating float NOT NULL,
    created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
    PRIMARY KEY  (id)
) $charset_collate;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);

function uci_reviews_page() {
    // Handle form submissions
    if (isset($_POST['sync_reviews']) && check_admin_referer('uci_sync_reviews_nonce')) {
        uci_sync_reviews();
    }

    // Fetch reviews from the database
    global $wpdb;
    $table_name = $wpdb->prefix . 'udemy_reviews';
    $reviews = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created DESC");

    ?>
    <div class="wrap">
        <h1>Udemy Course Reviews</h1>
        <form method="post" action="">
            <?php wp_nonce_field('uci_sync_reviews_nonce'); ?>
            <input type="submit" name="sync_reviews" value="Sync Reviews" class="button button-primary"/>
        </form>
        <h2>Reviews</h2>
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
                    <th>Course</th>
                    <th>Content</th>
                    <th>Rating</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($reviews)) : ?>
                    <?php foreach ($reviews as $review) : ?>
                        <tr>
                            <td><?php echo esc_html($review->course_title); ?></td>
                            <td><?php echo esc_html($review->content); ?></td>
                            <td><?php echo esc_html($review->rating); ?></td>
                            <td><?php echo esc_html($review->created); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="4">No reviews found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}