<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function tggr_gtm_purchase($order_id)
{
    $options = get_option('tggr_options');

    if (isset($options['purchase']) && $options['purchase']) {
        $order = wc_get_order($order_id);
        $items = $order->get_items();
        $products = [];

        foreach ($items as $item) {
            $product = $item->get_product();
            $products[] = tggr_format_item($product->get_id(), $item->get_quantity());
        }

        $hashed_email = tggr_hash_email($order->get_billing_email());
        $hashed_phone = tggr_hash_email($order->get_billing_phone());

        $purchase_data = array(
            'event' => 'purchase',
            'ecommerce' => array(
                'currency' => $order->get_currency(),
                'transaction_id' => $order->get_order_number(),
                'value' => floatval($order->get_total()),
                'tax' => floatval($order->get_total_tax()),
                'shipping' => floatval($order->get_shipping_total()),
                'coupon' => implode(', ', $order->get_coupon_codes()),
                'items' => $products,
                'user_data' => array(
                    'email' => $order->get_billing_email(),
                    'email_hashed' => $hashed_email,
                    'first_name' => $order->get_billing_first_name(),
                    'last_name' => $order->get_billing_last_name(),
                    'address_1' => $order->get_billing_address_1(),
                    'address_2' => $order->get_billing_address_2(),
                    'city' => $order->get_billing_city(),
                    'postcode' => $order->get_billing_postcode(),
                    'country' => $order->get_billing_country(),
                    'state' => $order->get_billing_state(),
                    'phone' => $order->get_billing_phone(),
                    'phone_hashed' => $hashed_phone
                )
            )
        );

        tggr_add_ga4_event_data('ga4-purchase', 'ga4PurchaseData', $purchase_data);
    }
}
add_action('woocommerce_thankyou', 'tggr_gtm_purchase');
?>