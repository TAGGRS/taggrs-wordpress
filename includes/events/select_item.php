<?php
if (! defined('ABSPATH')) exit;

function tggr_select_item_event()
{
    $options = get_option('tggr_options');
    if (!isset($options['select_item']) || !$options['select_item']) {
        return;
    }

    global $product, $wp_query;

    // Check if we have a product
    if (!$product) {
        return;
    }

    $current_user = wp_get_current_user();
    $email = $current_user->exists() ? $current_user->user_email : '';
    $hashed_email = $email ? tggr_hash_email($email) : '';

    // Bepaal de context voor item_list_id en item_list_name
    $item_list_id = 'default_list_id';
    $item_list_name = 'Default List';

    if (is_product_category()) {
        $queried_object = get_queried_object();
        $item_list_id = 'category_' . $queried_object->term_id;
        $item_list_name = $queried_object->name;
    } elseif (is_search()) {
        $item_list_id = 'search_results';
        $item_list_name = 'Search Results for "' . get_search_query() . '"';
    } elseif (is_shop()) {
        $item_list_id = 'shop_page';
        $item_list_name = 'Shop Page';
    }

    $item = tggr_format_item($product->get_id());

    $event_data = array(
        'event' => 'select_item',
        'ecommerce' => array(
            'item_list_id' => $item_list_id,
            'item_list_name' => $item_list_name,
            'items' => array($item)
        ),
        'user_data' => array(
            'email_hashed' => $hashed_email,
            'email' => $email
        )
    );

    // Store data in cookie instead of inline script
    $cookie_value = base64_encode(wp_json_encode($event_data));
    setcookie('tggr_select_item_data', $cookie_value, time() + 300, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), false);
}

function tggr_print_select_item_script()
{
    $options = get_option('tggr_options');
    if (!isset($options['select_item']) || !$options['select_item']) {
        return;
    }

    wp_register_script('ga4-select-item', false, array('jquery'), TGGR_VERSION, true);
    wp_enqueue_script('ga4-select-item');
    
    $script_data = array(
        'cookiePath' => COOKIEPATH,
        'cookieDomain' => COOKIE_DOMAIN
    );
    wp_localize_script('ga4-select-item', 'tggr_select_item', $script_data);
    
    $inline_script = '
        jQuery(document).ready(function($) {
            function tggrPushSelectItemData() {
                var cookieValue = document.cookie.split("; ").find(row => row.startsWith("tggr_select_item_data="));
                if (cookieValue) {
                    try {
                        var data = JSON.parse(atob(decodeURIComponent(cookieValue.split("=")[1])));
                        window.dataLayer = window.dataLayer || [];
                        window.dataLayer.push(data);
                        // Delete cookie after pushing data
                        document.cookie = "tggr_select_item_data=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=" + tggr_select_item.cookiePath + "; domain=" + tggr_select_item.cookieDomain;
                    } catch (e) {
                        console.error("Select item data error:", e);
                    }
                }
            }

            // Check on load
            tggrPushSelectItemData();

            // Check after updates (for potential AJAX calls)
            $(document.body).on("updated_wc_div", function() {
                setTimeout(tggrPushSelectItemData, 500);
            });
        });
    ';
    
    wp_add_inline_script('ga4-select-item', $inline_script);
}
add_action('wp_footer', 'tggr_print_select_item_script');
?>