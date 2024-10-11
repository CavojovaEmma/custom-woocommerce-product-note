jQuery(document).ready(function($) {
    $('.edit-product-note').on('keyup', function() {
        var cart_item_key = $(this).data('cart-item-key');
        var cw_product_note = $(this).val();

        $.ajax({
            url: cw_ajax_obj.ajax_url,
            type: 'POST',
            data: {
                action: 'cw_save_cart_note',
                cart_item_key: cart_item_key,
                product_note: cw_product_note,
                security: cw_ajax_obj.nonce
            },
            success: function(response) {
                if (response.success) {
                    console.log('Product note saved successfully.');
                } else {
                    console.log('Product note NOT saved successfully.');
                }
            }
        });
    });
});

