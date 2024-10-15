<?php


/**
 * Plugin Name: Custom WooCommerce Product Note
 * Plugin URI: https://github.com/CavojovaEmma/custom-woocommerce-product-note
 * Description: This WooCommerce plugin provides an intuitive way for customers to add notes to products, while ensuring these details
                are preserved from the cart through to order completion and email notifications.
 * Version: 1.0
 * Author: Emma Čavojová
 * Author URI: https://dev-emma-cavojova.pantheonsite.io/
 * Requires at least: 2.1.0
 * Tested up to: 6.6.2
 * Requires PHP: 5.6+
 * Text Domain: custom-woocommerce-product-note
 * License: GPL v3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * Custom WooCommerce Product Note is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Custom WooCommerce Product Note is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Custom WooCommerce Product Note. If not, see https://www.gnu.org/licenses/.
 */


if ( ! defined('ABSPATH') ) {
    exit;
}

/**
 * Define plugin constants
 */
if ( ! defined( 'CWPN_VERSION' ) ) {

    define('CWPN_VERSION', '1.0.0');

}

if ( ! defined( 'CWPN_PATH' ) ) {

    define('CWPN_PATH', plugin_dir_path(__FILE__));

}

if ( ! defined( 'CWPN_URL' ) ) {

    define('CWPN_URL', plugin_dir_url(__FILE__));

}

/**
 * Include the main class
 */
if ( ! class_exists('CW_Product_Note') ) {

    include_once CWPN_PATH . 'includes/class-cw-product-note.php';

}


/**
 * Initialize the plugin
 *
 * @return void
 */
function initialize_custom_woocommerce_product_note_plugin(): void
{
    CW_Product_Note::get_instance();
}
add_action( 'woocommerce_init', 'initialize_custom_woocommerce_product_note_plugin' );
