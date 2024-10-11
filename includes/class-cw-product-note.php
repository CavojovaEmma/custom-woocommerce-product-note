<?php


if ( ! defined('ABSPATH') ) {
    exit;
}

class CW_Product_Note
{

    /**
     * Initialize the plugin
     */
    public static function init(): void
    {
        // Hook into WooCommerce actions
        add_action('woocommerce_before_add_to_cart_quantity', array(__CLASS__, 'add_custom_field_to_product_page'));
        add_action('woocommerce_checkout_create_order_line_item', array(__CLASS__, 'save_custom_field_to_order'), 10, 4);

        add_filter('woocommerce_add_cart_item_data', array(__CLASS__, 'save_custom_field_to_cart'), 10, 2);
        add_filter('woocommerce_get_item_data', array(__CLASS__, 'display_custom_field_in_cart'), 10, 2);
        add_filter('woocommerce_cart_item_name', array(__CLASS__, 'display_custom_field_in_checkout'), 10000, 3 );

        // Load frontend assets
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));

        // Handle AJAX for updating the cart
        add_action('wp_ajax_cw_save_cart_note', array(__CLASS__, 'ajax_save_cart_note'));
        add_action('wp_ajax_nopriv_cw_save_cart_note', array(__CLASS__, 'ajax_save_cart_note'));

        // WooCommerce Customizer settings
        add_action( 'customize_register', array(__CLASS__, 'cwpn_customize_register') );
    }

    /**
     * Enqueue necessary JavaScript for handling the AJAX request
     */
    public static function enqueue_scripts(): void
    {
        if ( is_cart() || is_checkout() || is_product() ) {

            wp_enqueue_script(
                'cwpn-ajax-script',
                CWPN_URL . 'assets/js/cw-product-note.js',
                array('jquery'),
                CWPN_VERSION,
                true
            );

            wp_enqueue_style(
                'cwpn-ajax-style',
                CWPN_URL . 'assets/css/cw-product-note.css',
            );

            wp_enqueue_style(
                'cwpn-font-awesome',
                'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css'
            );


            wp_localize_script('cwpn-ajax-script', 'cw_ajax_obj', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('cw_save_note_nonce'),
            ));

        }
    }

    /**
     * Add custom field to the product page
     */
    public static function add_custom_field_to_product_page(): void
    {
        $label_value = get_theme_mod( 'cwpn_product_note_product_label' );
        $placeholder_value = get_theme_mod( 'cwpn_product_note_product_placeholder' );
        $small_value = get_theme_mod( 'cwpn_product_note_product_small' );
        $show_icon = get_theme_mod( 'cwpn_product_note_product_icon' );

        echo '<label for="product_note">' . esc_html( $label_value ) . '</label>';
        echo '<div class="product-note-container">';
        echo $show_icon ? '<i class="fas fa-comment-dots"></i>' : '';
        echo '<div><textarea name="product_note" placeholder="' . esc_attr( $placeholder_value ) . '" /></textarea>';
        echo $small_value ? '<small>' . esc_attr( $small_value ) . '</small>' : '';
        echo '</div></div>';
    }

    /**
     * Save the custom field value when adding to the cart
     */
    public static function save_custom_field_to_cart($cart_item_data, $product_id)
    {
        if ( isset($_POST['product_note']) ) {

            $cart_item_data['product_note'] = sanitize_textarea_field($_POST['product_note']);
            $cart_item_data['unique_key'] = md5(microtime() . rand());

        }

        return $cart_item_data;
    }

    /**
     * Display custom field data in the cart
     */
    public static function display_custom_field_in_cart($item_data, $cart_item): void
    {
        $content = '';

        if ( isset($cart_item['product_note']) ) {

            $content = esc_html($cart_item['product_note']);

        }

        $label_value = get_theme_mod( 'cwpn_product_note_cart_checkout_label' );
        $placeholder_value = get_theme_mod( 'cwpn_product_note_cart_checkout_placeholder' );
        $show_icon = get_theme_mod( 'cwpn_product_note_cart_checkout_icon' );

        if ( is_cart() ) {

            echo '<label for="product_note">' . esc_html__( $label_value ) . '</label>';
            echo '<div class="product-note-container">';
            echo $show_icon ? '<i class="fas fa-comment-dots"></i>' : '';
            echo '<textarea name="product_note"  rows="1" class="edit-product-note" placeholder="' . esc_attr__( $placeholder_value ) . '" data-cart-item-key="'
                    . esc_attr( $cart_item['key'] ) . '">' . $content . '</textarea>
            </div>';

        }
    }

    /**
     * Display custom field data in the checkout
     */
    public static function display_custom_field_in_checkout($item_name, $cart_item, $cart_item_key): string
    {
        if ( is_checkout() && isset( $cart_item['product_note'] ) ) {

            $content = esc_html( $cart_item['product_note'] );
            $item_name .= '<br><span>Poznámka: ' . $content . '</span>';

        }

        return $item_name;
    }

    /**
     * Save the custom field data in the order meta
     */
    public static function save_custom_field_to_order($item, $cart_item_key, $values, $order): void
    {
        if ( isset( $values['product_note'] ) ) {

            $item->add_meta_data(__('Poznámka', 'custom-woocommerce-product-note'), $values['product_note'], true);
            $item->save();

        }

    }

    /**
     * Handle the AJAX request to save the cart note
     */
    public static function ajax_save_cart_note(): void
    {
        check_ajax_referer('cw_save_note_nonce', 'security');

        if ( isset( $_POST['cart_item_key'], $_POST['product_note'] ) ) {

            $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
            $product_note = sanitize_textarea_field($_POST['product_note']);

            $cart = WC()->cart->get_cart();

            if ( isset( $cart[$cart_item_key] ) ) {

                WC()->cart->cart_contents[$cart_item_key]['product_note'] = $product_note;
                WC()->cart->set_session();
                wp_send_json_success();

            } else {

                wp_send_json_error(__('Cart item not found.', 'custom-woocommerce-product-note'));

            }

        }

        wp_send_json_error(__('Failed to save product note.', 'custom-woocommerce-product-note'));
    }

    /**
     * Create a new section in the WordPress Customizer for modifying the product note
     */
    public static function cwpn_customize_register( $wp_customize ): void
    {
        /** Add a new section for Product Note */
        $wp_customize->add_section( 'cwpn_section', array(
            'title'      => __( 'Product Note', 'custom-woocommerce-product-note' ),
            'priority'   => 30,
            'description'=> __( 'Modify product note for WooCommerce products', 'custom-woocommerce-product-note' ),
        ) );

        /** Add a setting for the product note Icon (Product Page) */
        self::add_checkbox_setting_to_customizer(
            $wp_customize,
            'cwpn_product_note_product_icon',
            'cwpn_product_note_icon_product_control',
            'Show Icon (Product Page)'
        );

        /**  Add a setting for the product note Label (Single Product Page) */
        self::add_text_setting_to_customizer(
            $wp_customize,
            'cwpn_product_note_product_label',
            'cwpn_product_note_product_label_control',
            'Label (Product Page)'
        );

        /**  Add a setting for the product note Placeholder (Single Product Page) */
        self::add_text_setting_to_customizer(
            $wp_customize,
            'cwpn_product_note_product_placeholder',
            'cwpn_product_note_placeholder_product_control',
            'Placeholder (Product Page)'
        );

        /**  Add a setting for the product note Placeholder (Single Product Page) */
        self::add_text_setting_to_customizer(
            $wp_customize,
            'cwpn_product_note_product_placeholder',
            'cwpn_product_note_placeholder_product_control',
            'Placeholder (Product Page)'
        );

        /** Add a setting for the product note Small text (Single Product Page) */
        self::add_checkbox_setting_to_customizer(
            $wp_customize,
            'cwpn_product_note_product_small',
            'cwpn_product_note_product_small_control',
            'Small text (Product Page)'
        );


        /** Add a setting for the product note Label (Cart | Checkout Page) */
        self::add_text_setting_to_customizer(
            $wp_customize,
            'cwpn_product_note_cart_checkout_label',
            'cwpn_product_note_cart_checkout_label_control',
            'Label (Cart | Checkout Page)'
        );

        /** Add a setting and control for the product note Placeholder (Cart | Checkout Page) */
        self::add_text_setting_to_customizer(
            $wp_customize,
            'cwpn_product_note_cart_checkout_placeholder',
            'cwpn_product_note_placeholder_cart_control',
            'Placeholder (Cart | Checkout Page)'
        );
    }

    static public function add_text_setting_to_customizer( $wp_customize, $setting_title, $control_title, $label ): void
    {
        // Add a setting for the product note attribute
        $wp_customize->add_setting( $setting_title, array(
            'default'           => '',
            'sanitize_callback' => 'sanitize_text_field',
        ) );

        // Add control for product note attribute
        $wp_customize->add_control( $control_title, array(
            'label'    => __( $label, 'custom-woocommerce-product-note' ),
            'section'  => 'cwpn_section',
            'settings' => $setting_title,
            'type'     => 'text',
        ) );
    }

    static public function add_checkbox_setting_to_customizer( $wp_customize, $setting_title, $control_title, $label ): void
    {
        // Add a setting for the product note icon
        $wp_customize->add_setting( $setting_title, array(
            'default'           => 'true',
        ) );

        // Add control for product note icon
        $wp_customize->add_control( $control_title, array(
            'label'    => __( $label, 'custom-woocommerce-product-note' ),
            'section'  => 'cwpn_section',
            'settings' => $setting_title,
            'type'     => 'checkbox',
        ) );
    }

}
