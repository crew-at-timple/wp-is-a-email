<?php
/**
 * Plugin Name:       IS a Email Customization
 * Description:       Incredibly simple email customization for all transactional emails sent by Wordpress.
 * Version:           1.0.0
 * Author:            TimpleCrew
 * Author URI:        https://timplecrew.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       is-a-email
 * 
 * Php Version 5.6
 *
 * @package WordPress
 * @author  Timple Crew <support@timplemail.com.ar>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 2024-01-24
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

define('IS_A_EMAIL_VERSION', '1.0.0');


/**
 * Prepares and sets up custom HTML to be included in emails sent by WordPress.
 * This function is used with the 'wp_mail' filter to modify the email content
 * and add a custom HTML layout, optionally including a logo.
 * 
 * @param array $args An associative array of email arguments, including 'message' and 'headers'.
 *                    - 'message' (string) The original email content.
 *                    - 'headers' (mixed) Existing email headers.
 * @return array The modified array of arguments, with the message wrapped in custom HTML.
 * @since 1.0.0
 */

function is_a__email_custom_html_layout($args) {

    // Get the logo URL from WordPress options and escape it for security
    $logo_url = esc_attr(get_option('is_a_email_logo'));

    // Start the custom HTML for the message
    $html_start = "
    <html>
    <body>
        <div style='margin: 0 auto; max-width: 600px; padding: 20px;'>
    ";

    // If a logo URL is set, add it to the HTML
    if (!empty($logo_url)) {

        $html_start .= "
                <div style='text-align: center;'>
                    <img src='{$logo_url}' alt='Logo' style='max-width: 300px; height: auto;'>
                </div>
        ";

    }

    // Continue the HTML layout
    $html_start .= "
            <div style='text-align: center; margin-top: 60px;'>
                <div>";

    $html_end = "
                </div>
            </div>
        </div>
    </body>
    </html>";

    // Wrap the original message in the custom HTML layout
    $args['message'] = $html_start . $args['message'] . $html_end;

    // Set the content type to HTML
    $args['headers'] = "Content-Type: text/html; charset=UTF-8";

    return $args;
}

add_filter('wp_mail', 'is_a__email_custom_html_layout');

/**
 * Registers a new menu item under the 'Options' menu in the WordPress admin area.
 * This function adds a submenu page titled 'Logo Email Configuration', which is used
 * for configuring settings related to the email logo. It is accessible to users with the
 * 'manage_options' capability (typically administrators).
 *
 * The menu page is registered with a slug 'is-a-email-logo', and it uses the 'is_a__email_options'
 * function to render its settings page.
 *
 * @since 1.0.0
 */

function is_a__email_menu() {
    // Add a submenu page under the 'Options' menu
    add_options_page('Logo Email Configuration', 'Logo Email Configuration', 'manage_options', 'is-a-email-logo', 'is_a__email_options');
}
add_action('admin_menu', 'is_a__email_menu');

/**
 * Renders the settings page for email logo configuration in the WordPress admin area.
 * This function is used as a callback by the 'is_a__email_menu' function and is called
 * when the 'Logo Email Configuration' page is accessed in the admin menu. It displays
 * the interface for the user to update and save the email logo settings.
 *
 * @since 1.0.0
 */

function is_a__email_options() {
    ?>
    <div class="wrap">
        <h1><?php _e('Email configuration') ?></h1>

        <form method="post" action="options.php">
            <?php
            settings_fields('is-a-email-options-group');
            do_settings_sections('is-a-email-logo');
            ?>
            <p>The configuration is very simple. Just upload a logo. We recommend JPG or PNG to maximize compatibility with email clients. The logo will be displayed with a maximum width of 300px.</</p>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Logo:</th>
                    <td>
                        <input type="text" id="is_a_email_logo" name="is_a_email_logo" value="<?php echo esc_attr(get_option('is_a_email_logo')); ?>" />
                        <button type="button" id="IS_a_upload_logo_button" class="button"><?php _e('Upload Logo') ?></button>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <script type="text/javascript">
    jQuery(document).ready(function($){
        $('#IS_a_upload_logo_button').click(function(e) {
            e.preventDefault();
            var image = wp.media({ 
                title: '<?php _e('Upload Logo') ?>',
                multiple: false
            }).open()
            .on('select', function(e){
                var uploaded_image = image.state().get('selection').first();
                console.log(uploaded_image);
                var logo_url = uploaded_image.toJSON().url;
                $('#is_a_email_logo').val(logo_url);
            });
        });
    });
    </script>
    <?php
}

/**
 * Registers a new setting for the WordPress admin area.
 * This function is used to register a setting specifically for storing the email logo URL.
 * It associates the setting with a settings group, which is used on a custom admin page.
 * The setting is named 'is_a_email_logo' and it is part of the 'is-a-email-options-group' group.
 *
 * This setting can be used to store and retrieve the logo URL to be used in custom email layouts.
 *
 * @since 1.0.0
 */

 
function is_a__email_register_options() {
    register_setting('is-a-email-options-group', 'is_a_email_logo');
}
add_action('admin_init', 'is_a__email_register_options');


/**
 * Enqueues scripts and stylesheets needed for the WordPress admin area, specifically for the 
 * 'Logo Email Configuration' settings page. This function ensures that scripts such as 
 * WordPress Media Uploader and jQuery are available for use on this specific admin page.
 *
 * @param string $hook The current admin page hook. Used to determine if the enqueued scripts
 *                     and styles should be loaded on this page.
 *
 * @since 1.0.0
 */

function is_a__email_enqueue_scripts($hook) {
    if ('settings_page_is-a-email-logo' !== $hook) {
        return;
    }
    wp_enqueue_media();
    wp_enqueue_script('jquery');
}
add_action('admin_enqueue_scripts', 'is_a__email_enqueue_scripts');