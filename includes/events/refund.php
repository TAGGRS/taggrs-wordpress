<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function tggr_refund($order_id)
{
    $options = get_option('tggr_options');
    if (!isset($options['refund']) || !$options['refund']) {
        return;
    }

    $order = wc_get_order($order_id);

    if (!$order) {
        return;
    }

    $items = array();

    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();
        if (empty($product) === True) {
            continue;
        }

        $items[] = tggr_format_item($product->get_id(), $item->get_quantity());
    }

    $event_data = array(
        'event' => 'refund',
        'ecommerce' => array(
            'currency' => $order->get_currency(),
            'transaction_id' => $order_id,
            'value' => floatval($order->get_total()),
            'shipping' => floatval($order->get_shipping_total()),
            'tax' => floatval($order->get_total_tax()),
            'items' => $items,
        )
    );

    $cookie_value = base64_encode(wp_json_encode($event_data));
    setcookie('tggr_refund_data', $cookie_value, time() + 300, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), false);
}
add_action('woocommerce_order_status_refunded', 'tggr_refund');

function tggr_print_refund_script()
{
    $options = get_option('tggr_options');
    if (!isset($options['refund']) || !$options['refund']) {
        return;
    }

    wp_register_script('ga4-refund', false, array('jquery'), '1.0.0', true);
    wp_enqueue_script('ga4-refund');
    
    $script_data = array(
        'cookiePath' => COOKIEPATH,
        'cookieDomain' => COOKIE_DOMAIN
    );
    wp_localize_script('ga4-refund', 'tggr_refund', $script_data);
    
    $inline_script = '
        jQuery(document).ready(function($) {
            var tggrRefundPushed = false;

            function tggrPushRefundData() {
                if (tggrRefundPushed) {
                    return;
                }
                var cookieValue = document.cookie.split("; ").find(row => row.startsWith("tggr_refund_data="));
                if (cookieValue) {
                    tggrRefundPushed = true;
                    // Delete cookie before pushing to prevent duplicate pushes
                    document.cookie = "tggr_refund_data=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=" + tggr_refund.cookiePath + "; domain=" + tggr_refund.cookieDomain;
                    try {
                        var data = JSON.parse(atob(decodeURIComponent(cookieValue.split("=")[1])));
                        window.dataLayer = window.dataLayer || [];
                        window.dataLayer.push({ecommerce: null});
                        window.dataLayer.push(data);
                    } catch(e) {
                        console.error("Refund data error:", e);
                    }
                }
            }

            // Check on load
            tggrPushRefundData();

            // Check again after AJAX calls (e.g. admin refund action)
            $(document).ajaxComplete(function() {
                setTimeout(tggrPushRefundData, 500);
            });
        });
    ';
    wp_add_inline_script('ga4-refund', $inline_script);
}
add_action('wp_footer', 'tggr_print_refund_script');
add_action('admin_footer', 'tggr_print_refund_script');
?>