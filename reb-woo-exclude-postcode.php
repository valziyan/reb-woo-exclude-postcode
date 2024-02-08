<?php
/*
Plugin Name: Exclude Postcodes from WooCommerce Orders
Description: Plugin to exclude certain postcodes from ordering in WooCommerce.
Version: 1.0
Author: Ryan Balisi
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Exclude_Postcodes_Plugin
{
    public function __construct()
    {
        // Check if WooCommerce is active
        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            // WooCommerce is active, initialize the plugin
            $this->init();
        } else {
            // WooCommerce is not active, display a notice
            add_action('admin_notices', array($this, 'woocommerce_not_active_notice'));
        }
    }

    // Initialize the plugin
    private function init()
    {
        // Add settings page
        add_action('admin_menu', array($this, 'add_settings_page'));

        // Handle form submission
        add_action('admin_init', array($this, 'handle_form_submission'));

        // Exclude postcodes from ordering
        add_action('woocommerce_checkout_process', array($this, 'exclude_postcodes_from_ordering'));
    }

    // WooCommerce not active notice
    public function woocommerce_not_active_notice()
    {
        ?>
        <div class="error">
            <p><?php _e('The "Exclude Postcodes from WooCommerce Orders" plugin requires WooCommerce to be installed and activated.', 'woocommerce-exclude-postcodes'); ?></p>
        </div>
        <?php
    }

    // Add settings page under WooCommerce menu
    public function add_settings_page()
    {
        add_submenu_page(
            'woocommerce',
            __('Excluded Postcodes', 'woocommerce'),
            __('Excluded Postcodes', 'woocommerce'),
            'manage_options',
            'exclude-postcodes-settings',
            array($this, 'render_settings_page')
        );
    }

    // Render settings page content
    public function render_settings_page()
    {
        ?>
        <div class="wrap">
            <h2><?php _e('Excluded Postcodes', 'woocommerce'); ?></h2>
            <form method="post" action="options.php">
                <?php settings_fields('exclude_postcodes_settings'); ?>
                <?php do_settings_sections('exclude_postcodes_settings'); ?>

                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e('Excluded Postcodes:', 'woocommerce'); ?></th>
                        <td>
                        <input type="text" name="excluded_postcodes" value="<?php echo esc_attr(get_option('excluded_postcodes', '')); ?>" style="width: 300px;" pattern="^\s*(?:\d+(?:,\s*)?)+\s*$" title="Please enter valid postcodes separated by commas." />
                        <p class="description"><?php _e('Enter the postcodes to exclude, separated by commas.', 'woocommerce'); ?></p>
                        <p class="description"><?php _e('Example: 9000, 9100, 9200', 'woocommerce'); ?></p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(__('Save Changes', 'woocommerce')); ?>
            </form>
        </div>
        <?php
    }

    // Handle form submission
    public function handle_form_submission()
    {
        register_setting('exclude_postcodes_settings', 'excluded_postcodes');
    }

    // Exclude certain postcodes from ordering
    public function exclude_postcodes_from_ordering()
    {
        // Get excluded postcodes from settings
        $excluded_postcodes = get_option('excluded_postcodes', '');
        $excluded_postcodes = explode(',', $excluded_postcodes);

        // Get customer's shipping postcode
        $postcode = WC()->customer->get_shipping_postcode();

        // Check if the postcode is in the excluded list
        if (in_array($postcode, $excluded_postcodes)) {
            // Display an error message
            wc_add_notice(__('Sorry, ordering is not available for your location.', 'woocommerce'), 'error');
        }
    }
}

new Exclude_Postcodes_Plugin();
