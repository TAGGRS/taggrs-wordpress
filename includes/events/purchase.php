<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function tggr_gtm_purchase($order_id)
{
    $options = get_option('tggr_options');

    if (isset($options['purchase']) && $options['purchase']) {
        $order = wc_get_order($order_id);

        if ($order->get_meta('_tggr_datalayer_fired', true)) {
            return;
        }

        $items = $order->get_items();
        $products = [];

        foreach ($items as $item) {
            $product = $item->get_product();
            $products[] = tggr_format_item($product->get_id(), $item->get_quantity());
        }

        $hashed_email = tggr_hash_email($order->get_billing_email());
        $hashed_phone = tggr_hash_email($order->get_billing_phone());
?>
        <script>
            window.dataLayer = window.dataLayer || [];
            dataLayer.push({
                'event': 'purchase',
                'ecommerce': {
                    'currency': '<?php echo esc_js($order->get_currency()); ?>',
                    'transaction_id': '<?php echo esc_js($order->get_order_number()); ?>',
                    'value': <?php echo esc_js($order->get_total()); ?>,
                    'tax': <?php echo esc_js($order->get_total_tax()); ?>,
                    'shipping': <?php echo esc_js($order->get_shipping_total()); ?>,
                    'coupon': '<?php echo esc_js(implode(', ', $order->get_coupon_codes())); ?>',
                    'items': <?php echo wp_json_encode($products); ?>, // Use wp_json_encode for encoding JSON in WP.
                    'user_data': {
                        'email': '<?php echo esc_js($order->get_billing_email()); ?>',
                        'email_hashed': '<?php echo esc_js($hashed_email); ?>',
                        'first_name': '<?php echo esc_js($order->get_billing_first_name()); ?>',
                        'last_name': '<?php echo esc_js($order->get_billing_last_name()); ?>',
                        'address_1': '<?php echo esc_js($order->get_billing_address_1()); ?>',
                        'address_2': '<?php echo esc_js($order->get_billing_address_2()); ?>',
                        'city': '<?php echo esc_js($order->get_billing_city()); ?>',
                        'postcode': '<?php echo esc_js($order->get_billing_postcode()); ?>',
                        'country': '<?php echo esc_js($order->get_billing_country()); ?>',
                        'state': '<?php echo esc_js($order->get_billing_state()); ?>',
                        'phone': '<?php echo esc_js($order->get_billing_phone()); ?>',
                        'phone_hashed': '<?php echo esc_js($hashed_phone); ?>'
                    },
                }
            });
        </script>
<?php
        $order->update_meta_data('_tggr_datalayer_fired', true);
        $order->save();
    }
}
add_action('woocommerce_thankyou', 'tggr_gtm_purchase');
?>