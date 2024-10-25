<?php


if ( ! defined('ABSPATH') ) {
    exit;
}

class CW_Product_Note
{
    /**
     * Static property to hold our singleton instance
     *
     */
    static $instance = false;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        /**
         * Hook into WooCommerce actions and filters
         */
        add_action( 'plugins_loaded', array( $this, 'custom-woocommerce-product-note' ) );
        add_action( 'woocommerce_before_add_to_cart_quantity', array( $this, 'cwpn_display_in_product') ); // since WC 7.0.1
        add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'cwpn_save_to_order_meta'), 10, 4 ); // since WC 3.1.0

        add_filter( 'woocommerce_add_cart_item_data', array( $this, 'cwpn_save_to_cart'), 10, 2 ); // since WC 2.5.0
        add_filter( 'woocommerce_get_item_data', array( $this, 'cwpn_display_in_cart'), 10, 2 ); // since WC 4.3.0
        add_filter( 'woocommerce_cart_item_name', array( $this, 'cwpn_display_in_checkout'), 10000, 3 ); // since 2.1.0

        /**
         * Load frontend assets
         */
        add_action( 'wp_enqueue_scripts', array( $this, 'cwpn_enqueue_scripts') );

        /**
         * Handle AJAX for updating the cart
         */
        add_action( 'wp_ajax_cw_save_cart_note', array( $this, 'cwpn_save_cart_ajax') );
        add_action( 'wp_ajax_nopriv_cw_save_cart_note', array( $this, 'cwpn_save_cart_ajax') );

        /**
         * Adds custom WooCommerce Product Note section to Customizer
         */
        add_action( 'customize_register', array( $this, 'cwpn_customize_register') );
    }

    /**
     * If an instance exists, this returns it. If not, it creates one and returns it.
     *
     * @return bool | CW_Product_Note
     */

    public static function get_instance(): CW_Product_Note | bool
    {
        if ( ! self::$instance ) {

            self::$instance = new self;

        }

        return self::$instance;
    }

    /**
     * Enqueues necessary styles and JavaScript
     *
     * @return void
     */
    public function cwpn_enqueue_scripts(): void
    {
        if ( is_cart() || is_product() ) {

            wp_enqueue_script(
                'cwpn-ajax-script',
                CWPN_URL . 'public/js/cw-product-note.js',
                array( 'jquery' ),
                CWPN_VERSION,
                true
            );

            wp_enqueue_script(
                'cwpn-textarea-autosize',
                'https://rawgit.com/jackmoore/autosize/master/dist/autosize.min.js',
            );

            wp_enqueue_style(
                'cwpn-ajax-style',
                CWPN_URL . 'public/css/cw-product-note.css',
            );

            wp_enqueue_style(
                'cwpn-font-awesome',
                'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css'
            );


            wp_localize_script('cwpn-ajax-script', 'cw_ajax_obj', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'cw_save_note_nonce' ),
            ));

        }
    }

    /**
     * Renders HTML for the custom WooCommerce Product Note
     *
     * @param $label_value
     * @param $placeholder_value
     * @param $show_icon
     * @param string $small_value
     * @param string $cart_item_key
     * @param string $textarea_content
     * @return string
     */
    public function cwpn_get_textarea_shortcode($label_value, $placeholder_value, $show_icon, string $small_value = '',
                                                string $cart_item_key = '', string $textarea_content = '' ): string
    {
        $shortcode = ! empty( $label_value ) ? '<label for="product_note">' . esc_html( $label_value ) . '</label>' : '';
        $shortcode .=  '<div class="product-note-container' . (! empty($cart_item_key) ? ' cwpn-cart' : '') . '">';
        $shortcode .= $show_icon ? '<i class="fas fa-comment-dots"></i>' : '';

        if ( $cart_item_key || $textarea_content ) {

            $shortcode .= '<div><textarea rows="1"  name="product_note" class="cwpn edit-product-note" 
                            placeholder="' . esc_attr__( $placeholder_value ) . '" 
                            data-cart-item-key="' . esc_attr( $cart_item_key ) . '">' . $textarea_content . '</textarea>';

        } else {

            $shortcode .= '<div><textarea rows="2" name="product_note" class="cwpn" placeholder="'
                . esc_attr( $placeholder_value ) . '" /></textarea>';

        }

        $shortcode .=  ! empty( $small_value ) ? '<small>' . esc_attr( $small_value ) . '</small>' : '';
        $shortcode .= '</div></div>';

        return $shortcode;
    }

    /**
     * Adds the custom WooCommerce product note to product page
     *
     * @return void
     */
    public function cwpn_display_in_product(): void
    {
        if ( ! is_product() ) {
            return;
        }

        $label_value = get_theme_mod( 'cwpn_product_note_product_label' );
        $placeholder_value = get_theme_mod( 'cwpn_product_note_product_placeholder' );
        $small_value = get_theme_mod( 'cwpn_product_note_product_small' );
        $show_icon = get_theme_mod( 'cwpn_product_note_product_icon' );
        $shortcode = self::cwpn_get_textarea_shortcode(
            $label_value,
            $placeholder_value,
            $show_icon,
            $small_value
        );

        echo apply_filters( 'cwpn_textarea_shortcode_product', $shortcode );
    }

    /**
     * Saves the custom WooCommerce product note when adding to the cart
     */
    public function cwpn_save_to_cart($cart_item_data, $product_id)
    {
        if ( isset( $_POST[ 'product_note' ] ) ) {

            $cart_item_data[ 'product_note' ] = sanitize_textarea_field( $_POST[ 'product_note' ] );
            $cart_item_data[ 'unique_key' ] = md5( microtime() . rand() );

        }

        return $cart_item_data;
    }

    /**
     * Displays the custom WooCommerce product note in the cart
     *
     * @param $item_data
     * @param $cart_item
     * @return void
     */
    public function cwpn_display_in_cart($item_data, $cart_item): void
    {
        if ( ! isset( $cart_item['product_note'] ) || ! is_cart() ) {
            return;
        }

        $content = esc_html( $cart_item[ 'product_note' ] );
        $label_value = get_theme_mod( 'cwpn_product_note_cart_label' );
        $placeholder_value = get_theme_mod( 'cwpn_product_note_cart_placeholder' );
        $small_value = get_theme_mod( 'cwpn_product_note_cart_small' );
        $show_icon = get_theme_mod( 'cwpn_product_note_cart_icon' );
        $shortcode = self::cwpn_get_textarea_shortcode(
            $label_value,
            $placeholder_value,
            $show_icon,
            $small_value,
            $cart_item[ 'key' ],
            $content
        );

        echo apply_filters( 'cwpn_textarea_shortcode_cart', $shortcode );
    }

    /**
     * Displays the custom WooCommerce product note in the checkout
     *
     * @param $item_name
     * @param $cart_item
     * @param $cart_item_key
     * @return string
     */
    public function cwpn_display_in_checkout($item_name, $cart_item, $cart_item_key): string
    {
        if ( ( ! is_checkout() ) || empty( $cart_item[ 'product_note' ] ) ) {
            return $item_name;
        }

        $order_item_title = get_theme_mod( 'cwpn_order_item_product_note_title' );
        $content = esc_html( $cart_item[ 'product_note' ] );
        $item_name .= '<span>' .  __( $order_item_title ) . ': ' . $content . '</span>';

        return $item_name;
    }

    /**
     * Saves the custom WooCommerce product note in the order meta
     *
     * @param $item
     * @param $cart_item_key
     * @param $values
     * @param $order
     * @return void
     */
    public function cwpn_save_to_order_meta($item, $cart_item_key, $values, $order): void
    {
        if ( isset( $values[ 'product_note' ] ) ) {

            $order_item_title = get_theme_mod( 'cwpn_order_item_product_note_title' );
            $item->add_meta_data( __( $order_item_title, 'custom-woocommerce-product-note' ), $values[ 'product_note' ], true );
            $item->save();

        }

    }

    /**
     * Handles the AJAX request to save the custom WooCommerce product note in cart
     *
     * @return void
     */
    public function cwpn_save_cart_ajax(): void
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
     * Creates a new section in the WordPress Customizer for modifying the custom WooCommerce product note
     *
     * @param $wp_customize
     * @return void
     */
    public function cwpn_customize_register( $wp_customize ): void
    {
        /**
         * Add a new section for Product Note
         */
        $wp_customize->add_section( 'cwpn_section', array(
            'title'      => __( 'Product Note', 'custom-woocommerce-product-note' ),
            'priority'   => 30,
            'description'=> __( 'Modify product note for WooCommerce products', 'custom-woocommerce-product-note' ),
        ) );

        /**
         * Add a settiong for the Custom Order Items Title
         */
        $this->cwpn_add_text_setting_to_customizer(
            $wp_customize,
            'cwpn_order_item_product_note_title',
            'cwpn_order_item_product_note_control',
            'Custom Order Items Title',
            'The title for the product note is shown in the order summary and is used as a metadata key to store the note in the order items.',
            'Product Note'
        );

        /**
         * Add settings for the Product Page
         */
        $this->cwpn_add_checkbox_setting_to_customizer(
            $wp_customize,
            'cwpn_product_note_product_icon',
            'cwpn_product_note_icon_product_control',
            'Show Icon (Product Page)'
        );

        $this->cwpn_add_text_setting_to_customizer(
            $wp_customize,
            'cwpn_product_note_product_label',
            'cwpn_product_note_product_label_control',
            'Label (Product Page)',
            'Label for the textarea field on the single product page.'
        );

        $this->cwpn_add_text_setting_to_customizer(
            $wp_customize,
            'cwpn_product_note_product_placeholder',
            'cwpn_product_note_placeholder_product_control',
            'Placeholder (Product Page)',
            'Placeholder for the textarea field on the single product page.',
            'You can specify product note here.'
        );

        $this->cwpn_add_text_setting_to_customizer(
            $wp_customize,
            'cwpn_product_note_product_small',
            'cwpn_product_note_product_small_control',
            'Small Text (Product Page)',
            'Small text under the textarea field on the single product page.'
        );

        /**
         * Add settings for the Cart Page
         */
        $this->cwpn_add_checkbox_setting_to_customizer(
            $wp_customize,
            'cwpn_product_note_cart_icon',
            'cwpn_product_note_cart_icon_control',
            'Show Icon (Cart Page)',
        );

        $this->cwpn_add_text_setting_to_customizer(
            $wp_customize,
            'cwpn_product_note_cart_label',
            'cwpn_product_note_cart_label_control',
            'Label (Cart Page)',
            'Label for the textarea field on the cart page.'
        );

        $this->cwpn_add_text_setting_to_customizer(
            $wp_customize,
            'cwpn_product_note_cart_placeholder',
            'cwpn_product_note_placeholder_cart_control',
            'Placeholder (Cart Page)',
            'Placeholder for the textarea field on the cart page.',
            'You can specify product note here.'
        );

        $this->cwpn_add_text_setting_to_customizer(
            $wp_customize,
            'cwpn_product_note_cart_small',
            'cwpn_product_note_cart_small_control',
            'Small Text (Cart Page)',
            'Small text under the textarea field on the cart page.'
        );
    }

    /**
     * Adds a setting of type text to the Customizer Product Note section
     *
     * @param $wp_customize
     * @param $setting_title
     * @param $control_title
     * @param $label
     * @param string $description
     * @param string $default_value
     * @return void
     */
    private function cwpn_add_text_setting_to_customizer($wp_customize, $setting_title, $control_title, $label, $description = '', $default_value = '' ): void
    {
        // Add a setting for the product note attribute
        $wp_customize->add_setting( $setting_title, array(
            'default'           => $default_value,
            'sanitize_callback' => 'sanitize_text_field',
        ) );

        // Add control for product note attribute
        $wp_customize->add_control( $control_title, array(
            'label'    => __( $label, 'custom-woocommerce-product-note' ),
            'section'  => 'cwpn_section',
            'settings' => $setting_title,
            'type'     => 'text',
            'description' => __( $description )
        ) );
    }

    /**
     * Adds a setting of type checkbox to the Customizer Product Note section
     *
     * @param $wp_customize
     * @param $setting_title
     * @param $control_title
     * @param $label
     * @return void
     */
    private function cwpn_add_checkbox_setting_to_customizer($wp_customize, $setting_title, $control_title, $label ): void
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
