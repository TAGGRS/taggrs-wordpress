<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function tggr_remove_from_cart_event($cart_item_key, $instance)
{
    $options = get_option('tggr_options');
    if (!isset($options['remove_from_cart']) || !$options['remove_from_cart']) {
        return;
    }

    // Check if instance is valid and has get_cart_item method
    if (!is_object($instance) || !method_exists($instance, 'get_cart_item')) {
        return;
    }

    $cart_item = $instance->get_cart_item($cart_item_key);
    $current_user = wp_get_current_user();
    $email = $current_user->exists() ? $current_user->user_email : '';
    $hashed_email = $email ? tggr_hash_email($email) : '';

    if (!$cart_item || !isset($cart_item['product_id'])) {
        return;
    }

    $product = wc_get_product($cart_item['product_id']);

    if (!$product) {
        return;
    }

    $item = tggr_format_item($product->get_id(), $cart_item['quantity']);

    $event_data = array(
        'event' => 'remove_from_cart',
        'ecommerce' => array(
            'currency' => get_woocommerce_currency(),
            'value' => floatval($product->get_price()) * $cart_item['quantity'],
            'items' => array($item),
        ),
        'user_data' => array(
            'email_hashed' => $hashed_email,
            'email' => $email
        )
    );

    $cookie_value = base64_encode(wp_json_encode($event_data));
    setcookie('tggr_remove_from_cart_data', $cookie_value, time() + 300, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), false);
}
add_action('woocommerce_remove_cart_item', 'tggr_remove_from_cart_event', 10, 2);

function tggr_print_remove_from_cart_script()
{
    $options = get_option('tggr_options');
    if (!isset($options['remove_from_cart']) || !$options['remove_from_cart']) {
        return;
    }

    wp_register_script('ga4-remove-from-cart', false, array('jquery'), TGGR_VERSION, true);
    wp_enqueue_script('ga4-remove-from-cart');
    
    $script_data = array(
        'cookiePath' => COOKIEPATH,
        'cookieDomain' => COOKIE_DOMAIN
    );
    wp_localize_script('ga4-remove-from-cart', 'tggr_remove_from_cart', $script_data);
    
    $inline_script = '
        jQuery(document).ready(function($) {
            function tggrPushRemoveFromCartData() {
                var cookieValue = document.cookie.split("; ").find(row => row.startsWith("tggr_remove_from_cart_data="));
                if (cookieValue) {
                    try {
                        var data = JSON.parse(atob(decodeURIComponent(cookieValue.split("=")[1])));
                        window.dataLayer = window.dataLayer || [];
                        window.dataLayer.push(data);
                        
                        // Delete cookie after pushing data
                        document.cookie = "tggr_remove_from_cart_data=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=" + tggr_remove_from_cart.cookiePath + "; domain=" + tggr_remove_from_cart.cookieDomain;
                    } catch(e) {
                        console.error("Remove from cart data error:", e);
                    }
                }
            }
            
            // Check on load (for non-AJAX calls)
            tggrPushRemoveFromCartData();
            
            // Check after AJAX remove from cart events
            $(document.body).on("removed_from_cart updated_wc_div wc_fragments_refreshed", function() {
                setTimeout(tggrPushRemoveFromCartData, 500);
            });
        });
    ';
    
    wp_add_inline_script('ga4-remove-from-cart', $inline_script);
}
add_action('wp_footer', 'tggr_print_remove_from_cart_script');

?>
