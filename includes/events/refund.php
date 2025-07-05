<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function tggr_refund($order_id)
{
    $options = get_option('tggr_options');
    
    if (isset($options['refund']) && $options['refund']) {
        $order = wc_get_order($order_id);
        $items = array();

        foreach ($order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            if(empty($product) === True) {
                continue;
            }
            
            $items[] = tggr_format_item($product->get_id(), $item->get_quantity());
        }

        $refund_data = array(
            'event' => 'refund',
            'ecommerce' => array(
                'currency' => $order->get_currency(),
                'transaction_id' => (string)$order_id,
                'value' => floatval($order->get_total()),
                'shipping' => floatval($order->get_shipping_total()),
                'tax' => floatval($order->get_total_tax()),
                'items' => $items
            )
        );

        tggr_add_ga4_event_data('ga4-refund', 'ga4RefundData', $refund_data);
    }
}

add_action('woocommerce_order_status_refunded', 'tggr_refund');
?>