<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function tggr_add_to_cart_event($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data)
{
    $options = get_option('tggr_options');
    if (!isset($options['add_to_cart']) || !$options['add_to_cart']) {
        return;
    }

    $product = wc_get_product($product_id);
    $current_user = wp_get_current_user();
    $email = $current_user->exists() ? $current_user->user_email : '';
    $hashed_email = $email ? tggr_hash_email($email) : '';

    $item = tggr_format_item($product_id, $quantity);
    
    $event_data = array(
        'event' => 'add_to_cart',
        'ecommerce' => array(
            'currency' => get_woocommerce_currency(),
            'value' => floatval($product->get_price()) * $quantity,
            'items' => array($item),
        ),
        'user_data' => array(
            'email_hashed' => $hashed_email,
            'email' => $email
        )
    );

    // Store data in cookie instead of inline script
    $cookie_value = base64_encode(wp_json_encode($event_data));
    setcookie('tggr_add_to_cart_data', $cookie_value, time() + 300, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), false);
}
add_action('woocommerce_add_to_cart', 'tggr_add_to_cart_event', 10, 6);

function tggr_print_add_to_cart_script()
{
    $options = get_option('tggr_options');
    if (!isset($options['add_to_cart']) || !$options['add_to_cart']) {
        return;
    }

    wp_register_script('ga4-add-to-cart', false, array('jquery'), TGGR_VERSION, true);
    wp_enqueue_script('ga4-add-to-cart');
    
    $script_data = array(
        'cookiePath' => COOKIEPATH,
        'cookieDomain' => COOKIE_DOMAIN
    );
    wp_localize_script('ga4-add-to-cart', 'tggr_add_to_cart', $script_data);
    
    $inline_script = '
        jQuery(document).ready(function($) {
            function tggrPushAddToCartData() {
                var cookieValue = document.cookie.split("; ").find(row => row.startsWith("tggr_add_to_cart_data="));
                if (cookieValue) {
                    try {
                        var data = JSON.parse(atob(decodeURIComponent(cookieValue.split("=")[1])));
                        window.dataLayer = window.dataLayer || [];
                        window.dataLayer.push(data);
                        
                        // Delete cookie after pushing data
                        document.cookie = "tggr_add_to_cart_data=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=" + tggr_add_to_cart.cookiePath + "; domain=" + tggr_add_to_cart.cookieDomain;
                    } catch(e) {
                        console.error("Add to cart data error:", e);
                    }
                }
            }
            
            // Check on load (for non-AJAX calls)
            tggrPushAddToCartData();
            
            // Check after AJAX add to cart events
            $(document.body).on("added_to_cart updated_wc_div wc_fragments_refreshed", function() {
                setTimeout(tggrPushAddToCartData, 500);
            });
        });
    ';
    
    wp_add_inline_script('ga4-add-to-cart', $inline_script);
}
add_action('wp_footer', 'tggr_print_add_to_cart_script');

?>