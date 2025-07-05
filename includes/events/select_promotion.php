<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function tggr_select_promotion_event()
{
    $options = get_option('tggr_options');
    if (!isset($options['select_promotion']) || !$options['select_promotion']) {
        return;
    }

    $current_user = wp_get_current_user();
    $email = $current_user->exists() ? $current_user->user_email : '';
    $hashed_email = $email ? tggr_hash_email($email) : '';
   
    $event_data = array(
        'event' => 'select_promotion',
        'ecommerce' => array(
            'item_list_id' => 'cart',
            'item_list_name' => 'Shopping Cart',
        ),
        'user_data' => array(
            'email_hashed' => $hashed_email,
            'email' => $email
        )
    );
   
    // Store data in cookie instead of inline script
    $cookie_value = base64_encode(wp_json_encode($event_data));
    setcookie('tggr_promotion_data', $cookie_value, time() + 300, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), false);
}
add_action('woocommerce_applied_coupon', 'tggr_select_promotion_event');

function tggr_print_promotion_script()
{
    $options = get_option('tggr_options');
    if (!isset($options['select_promotion']) || !$options['select_promotion']) {
        return;
    }

    wp_register_script('ga4-select-promotion', false, array('jquery'), TGGR_VERSION, true);
    wp_enqueue_script('ga4-select-promotion');
    
    $script_data = array(
        'cookiePath' => COOKIEPATH,
        'cookieDomain' => COOKIE_DOMAIN
    );
    wp_localize_script('ga4-select-promotion', 'tggr_select_promotion', $script_data);
    
    $inline_script = '
        jQuery(document).ready(function($) {
            function tggrPushPromotionData() {
                var cookieValue = document.cookie.split("; ").find(row => row.startsWith("tggr_promotion_data="));
                if (cookieValue) {
                    try {
                        var data = JSON.parse(atob(decodeURIComponent(cookieValue.split("=")[1])));
                        window.dataLayer = window.dataLayer || [];
                        window.dataLayer.push(data);
                        
                        // Delete cookie after pushing data
                        document.cookie = "tggr_promotion_data=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=" + tggr_select_promotion.cookiePath + "; domain=" + tggr_select_promotion.cookieDomain;
                    } catch (e) {
                        console.error("Promotion data error:", e);
                    }
                }
            }

            // Check on load
            tggrPushPromotionData();

            // Check after coupon updates
            $(document.body).on("updated_wc_div wc_fragments_refreshed", function() {
                setTimeout(tggrPushPromotionData, 500);
            });
        });
    ';
    
    wp_add_inline_script('ga4-select-promotion', $inline_script);
}
add_action('wp_footer', 'tggr_print_promotion_script');
?>