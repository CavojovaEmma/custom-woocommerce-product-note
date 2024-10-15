# Custom Woocommerce Product Note
## WordPress Plugin
The WooCommerce Product Note plugin allows your customers to associate a personalized note with individual products 
when placing an order. Perfect for specifying product variations, customization requests, or special instructions, 
this plugin ensures seamless communication between your customers and your store.

- Author: Emma Čavojová
- Version: 20241014
- Plugin URL: https://github.com/CavojovaEmma/custom-woocommerce-product-note
- Demo URL: https://dev-emma-cavojova.pantheonsite.io/custom-plugins#custom-woocommerce-product-note
- Author URL: https://dev-emma-cavojova.pantheonsite.io
- License: GNU General Public License v3 or later
- License URI: http://www.gnu.org/licenses/gpl-3.0.html

DESCRIPTION
-----------

Key features of the plugin:
* **Product-Specific Notes:** Adds a customizable (more info below) textarea on each product page and cart page, 
allowing customers to write a note when selecting a product (e.g., for custom requests or variations).
* **AJAX Cart Editing:** Customers can easily edit their product note directly from the cart without needing to reload
the page, enhancing user experience and efficiency.
* **Order Item Meta Storage:** The note is saved with the product as part of the order item's metadata when the order is placed.
* **Email Integration:** The product note is included in the order confirmation email sent to the customer,
ensuring all custom details are communicated clearly.
* **Fully Customizable via Theme Customizer:** Customize the textarea attributes, such as the label, placeholder, icon, 
and small text under the textarea, directly from the Theme Customizer. These customizations are available in 
a dedicated "Product Note" section for both the product and cart pages.
* **Developer-Friendly Hooks:** The plugin provides hooks that developers can use to customize the textarea HTML programmatically. Modify the appearance of the textarea on the product page or cart with ease, making the plugin highly adaptable to custom themes or advanced functionality.

This plugin provides an intuitive way for customers to add notes to products, while ensuring these details
are preserved from the cart through to order completion and email notifications.


INSTALLATION
------------

1. [x] Upload extracted content of `custom-woocommerce-product-note` repo to the `/wp-content/plugins/custom-woocommerce-product-note/` directory

OR

1. [ ] ~~Install plugin from WordPress repository (not yet)~~

2. [x] Activate the plugin through the 'Plugins' menu in WordPress
3. [x] Customize the textareas's attributes (icon, placeholder, label, small text) by opening a Theme's Customizer > "Product Note"

REQUIREMENTS
------------

Server

* WordPress 2.1+ (May work with older versions too)
* WooCommerce 2.1.0+
* PHP 5.6+ (Required)
* jQuery 1.9.1+ 

Browsers

* Modern Browsers
* Firefox, Chrome, Safari, Opera, IE 10+
* Tested on Firefox, Chrome


FURTHER USE OF ORDER ITEM'S NOTE META AFTER ORDER SUBMISSION
---
If you need to use the order item's note beyond the functionality of this plugin:
* The order item's note is stored in metadata under a **key** that matches the note's title. You can set this title in the Customizer under the *Product Note* section.
* To get the order item's note title, use:<br>
  `$order_item_product_note_title = get_theme_mod( 'cwpn_order_item_product_note_title' );`<br>
* To retrieve the order item's note, use the title as the key:<br>
` $order_item->get_meta( $order_item_product_note_title );`



LICENSE DETAILS
---------------
The GPL license of Custom Woocommerce Product Note grants you the right to use, study, share (copy), modify and (re)distribute the software, as long as these license terms are retained.

SUPPORT | UPDATES | CONTRIBUTIONS
-----------------------------

If you're using my program, I would **really appreciate any suggestions or feedback** you have. If you resolve an issue, fix a bug, or add a new feature, please share it with me or submit a pull request. (That said, I’m not obligated to follow every suggestion or implement all changes.)
My **updates are sporadic**, as I only work on the program when I encounter a bug or when the current version no longer meets my needs.
**I don't offer support**. This program was created for my own use—for fun, in my free time, and for free. It’s not guaranteed to work for everyone. That doesn’t mean I’m unwilling to help, though.
While I test my code thoroughly, I can't cover every possible scenario. Most issues can likely be resolved with a quick Google search. That’s what I do when I encounter problems with other plugins or tools I use.


DISCLAIMER
---------

NO WARRANTY OF ANY KIND! USE THIS SOFTWARE AND INFORMATION AT YOUR OWN RISK!
License: GNU General Public License v3