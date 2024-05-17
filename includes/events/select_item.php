<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function tggr_select_item()
{
    $options = get_option('tggr_options');
    global $product, $wp_query;

    $current_user = wp_get_current_user();
    $hashed_email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
    }

    if (isset($options['select_item']) && $options['select_item']) {
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

?>
        <script>
            window.dataLayer = window.dataLayer || [];
            dataLayer.push({
                'event': 'select_item',
                'ecommerce': {
                    'item_list_id': '<?php echo esc_js($item_list_id); ?>',
                    'item_list_name': '<?php echo esc_js($item_list_name); ?>',
                    'items': [<?php echo wp_json_encode($item); ?>]
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
add_action('woocommerce_before_single_product', 'tggr_select_item');
?>