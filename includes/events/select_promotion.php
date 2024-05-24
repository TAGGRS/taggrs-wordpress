<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function tggr_select_promotion($coupon_code)
{
    $options = get_option('tggr_options');
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

            $items[] = tggr_format_item($product_id, $cart_item['quantity']);
        }

?>
        <script>
            window.dataLayer = window.dataLayer || [];
            dataLayer.push({
                'event': 'select_item',
                'ecommerce': {
                    'item_list_id': '<?php echo esc_js($item_list_id); ?>',
                    'item_list_name': '<?php echo esc_js($item_list_name); ?>',
                    'items': <?php echo wp_json_encode($items); ?>
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