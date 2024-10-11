<?php


/**
 * Plugin Name: Custom WooCommerce Product Note
 * Plugin URI: https://yourwebsite.com
 * Description: A WooCommerce plugin that allows customers to add a custom note to products and edit it in the cart and checkout.
 * Version: 1.0
 * Author: Your Name
 * Author URI: https://dev-emma-cavojova.pantheonsite.io/
 * Text Domain: custom-woocommerce-product-note
 * Domain Path: /
 * License: GPL2
 */


if ( ! defined('ABSPATH') ) {
    exit;
}

// Define plugin constants
define('CWPN_VERSION', '1.0');
define('CWPN_PATH', plugin_dir_path(__FILE__));
define('CWPN_URL', plugin_dir_url(__FILE__));

// Include the main class
if ( ! class_exists('CW_Product_Note') ) {

    include_once CWPN_PATH . 'includes/class-cw-product-note.php';

}

// Initialize the plugin
add_action('woocommerce_init', array('CW_Product_Note', 'init'));
