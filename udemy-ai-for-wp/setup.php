<?php
// Setup page
function uci_setup_page() {
    if (isset($_POST['save_token'])) {
        // Verify nonce
        if (!isset($_POST['uci_nonce']) || !wp_verify_nonce(wp_unslash($_POST['uci_nonce']), 'uci_save_token')) {
            die('Nonce verification failed');
        }

        // Sanitize and save the token
        if (isset($_POST['udemy_secret_token'])) {
            update_option('udemy_secret_token', sanitize_text_field(wp_unslash($_POST['udemy_secret_token'])));
            echo '<div class="notice notice-success"><p>Secret token saved successfully.</p></div>';
        }
        
        // Remove the redirected query parameter after saving the token
        $url = remove_query_arg('redirected');
        echo '<script type="text/javascript">window.location.href = "' . esc_url($url) . '";</script>';
        return;
    }

    $secret_token = get_option('udemy_secret_token', '');

    ?>
    <div class="wrap">
        <h1>Udemy Course Info Setup</h1>
        <?php if (isset($_GET['redirected']) && $_GET['redirected'] == 'true') : ?>
            <script type="text/javascript">
                document.addEventListener('DOMContentLoaded', function() {
                    alert('Adding your secret key is required to use the plugin');
                });
            </script>
        <?php endif; ?>
        <form method="post" action="">
            <?php wp_nonce_field('uci_save_token', 'uci_nonce'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Udemy Secret Token</th>
                    <td><input type="password" name="udemy_secret_token" value="<?php echo esc_attr($secret_token); ?>" class="regular-text" /></td>
                </tr>
            </table>
            <input type="submit" name="save_token" value="Save Token" class="button button-primary"/>
        </form>
        <h2>Instructions</h2>
        <p>The Udemy Instructor API exposes several functionalities of Udemy to help instructors build client applications and integrations with Udemy.</p>
        <p>It is organized around REST. Our API is designed to have predictable, resource-oriented URLs and to use HTTP response codes to indicate API errors. We use built-in HTTP features, like HTTP authentication and HTTP verbs, which can be understood by off-the-shelf HTTP clients. We only accept https calls to the API. All responses will be returned in JSON format, including errors.</p>
        <p>Udemy Instructor API is currently at version 1 and the root endpoint is <a href="https://www.udemy.com/instructor-api/v1/" target="_blank">https://www.udemy.com/instructor-api/v1/</a> for all resources.</p>
        <h3>Creating an API Client</h3>
        <p>To make any calls to Udemy REST API, you will need to create an API client. API client consists of a bearer token, which is connected to a user account on Udemy.</p>
        <p>If you want to create an API client, go to <a href="https://www.udemy.com/developers/instructor/" target="_blank">API Clients page</a> in your user profile. Once you request an API client, your newly created API client will be active and immediately ready to use.</p>
        <p>Please visit <a href="https://www.udemy.com/developers/instructor/" target="_blank">https://www.udemy.com/developers/instructor/</a> for more information about the Udemy API.</p>
        <h2>Disclaimer</h2>
        <p>This plugin is in no way endorsed by Udemy in any way.</p>
    </div>
    <?php
}
?>