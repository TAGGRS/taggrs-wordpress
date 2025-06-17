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

    $categories = get_the_terms($product->get_id(), 'product_cat');
    $category_name = !empty($categories) ? $categories[0]->name : '';

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

    $cookie_value = base64_encode(wp_json_encode($event_data));
    setcookie('tggr_select_item_data', $cookie_value, time() + 300, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), false);
}
add_action('woocommerce_before_single_product', 'tggr_select_item_event');

function tggr_print_select_item_script()
{
    $options = get_option('tggr_options');
    if (!isset($options['select_item']) || !$options['select_item']) {
        return;
    }
    ?>
    <script>
        jQuery(document).ready(function($) {
            function pushSelectItemData() {
                var cookieValue = document.cookie.split('; ').find(row => row.startsWith('tggr_select_item_data='));
                if (cookieValue) {
                    try {
                        var data = JSON.parse(atob(decodeURIComponent(cookieValue.split('=')[1])));
                        window.dataLayer = window.dataLayer || [];
                        window.dataLayer.push(data);
                        // Delete cookie after pushing data
                        document.cookie = "tggr_select_item_data=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=<?php echo COOKIEPATH; ?>; domain=<?php echo COOKIE_DOMAIN; ?>";
                    } catch (e) {
                        console.error('Select item data error:', e);
                    }
                }
            }

            // Check on load
            pushSelectItemData();

            // Check after updates (for potential AJAX calls)
            $(document.body).on('updated_wc_div', function() {
                setTimeout(pushSelectItemData, 500);
            });
        });
    </script>
    <?php
}
add_action('wp_footer', 'tggr_print_select_item_script');
?>