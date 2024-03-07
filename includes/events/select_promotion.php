<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function tggr_select_promotion($coupon_code)
{
    $options = get_option('wc_gtm_options');
    $coupon = new WC_Coupon($coupon_code);

    $current_user = wp_get_current_user();
    $hashed_email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
    }

    if (isset($options['select_promotion']) && $options['select_promotion']) {
        $cart_items = WC()->cart->get_cart();
        $items = array();
        foreach ($cart_items as $cart_item_key => $cart_item) {
            $product_id = $cart_item['product_id'];
            $product = wc_get_product($product_id);
            $discount_amount = 0;

            // Voeg logica toe om de korting voor dit specifieke item te berekenen
            // Dit hangt af van hoe je kortingen configureert in WooCommerce

            $items[] = array(
                'item_id' => $product_id,
                'item_name' => $product->get_name(),
                'coupon' => $coupon->get_code(),
                'discount' => $coupon->get_amount(),
                'price' => $product->get_price(),
                'quantity' => $cart_item['quantity']
                // Voeg indien nodig meer attributen toe
            );
        }

?>
        <script>
            window.dataLayer = window.dataLayer || [];
            dataLayer.push({
                'event': 'select_item',
                'ecommerce': {
                    'item_list_id': '<?php echo esc_js($item_list_id); ?>',
                    'item_list_name': '<?php echo esc_js($item_list_name); ?>',
                    'items': [{
                        'item_id': '<?php echo esc_js($product->get_id()); ?>',
                        'item_name': '<?php echo esc_js($product->get_name()); ?>',
                        'item_category': '<?php echo esc_js($category_name); ?>',
                        'price': '<?php echo esc_js($product->get_price()); ?>',
                        'currency': '<?php echo esc_js(get_woocommerce_currency()); ?>'
                    }]
                },
                'user_data': {
                    'email_hashed': '<?php echo esc_js($hashed_email); ?>',
                    'email': '<?php echo esc_js($current_user->user_email); ?>'
                }
            });
        </script>

<?php
    }
}

add_action('woocommerce_applied_coupon', 'tggr_select_promotion');
?>