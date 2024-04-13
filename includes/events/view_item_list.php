<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function tggr_gtm_view_item_list()
{
    $options = get_option('wc_gtm_options');
    $current_user = wp_get_current_user();
    $hashed_email = '';
    $email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
        $email = $current_user->user_email;
    }

    if (isset($options['view_item_list']) && $options['view_item_list']) {
        if (is_shop() || is_product_category() || is_product_tag()) {
            global $wp_query;
            $products = [];
            foreach ($wp_query->posts as $post) {
                $product = wc_get_product($post->ID);
                $products[] = $products[] = [
                    'item_id' => $product->get_id(),
                    'item_name' => $product->get_name(),
                    'price' => $product->get_price(),
                    'item_category' => implode(', ', $product->get_category_ids()),
                ];
            }
            $item_list_id = 'default_list_id';
            $item_list_name = 'Default List';

            if (is_product_category()) {
                $queried_object = get_queried_object();
                $item_list_id = $queried_object->term_id;
                $item_list_name = $queried_object->name;
            } elseif (is_search()) {
                $item_list_id = 'search_results';
                $item_list_name = 'Search Results for "' . get_search_query() . '"';
            } elseif (is_shop()) {
                $item_list_id = 'shop_page';
                $item_list_name = 'Shop Page';
            }
?>
            <script>
                window.dataLayer = window.dataLayer || [];
                dataLayer.push({
                    'event': 'view_item_list',
                    'ecommerce': {
                        'item_list_id': '<?php echo esc_js($item_list_id); ?>',
                        'item_list_name': '<?php echo esc_js($item_list_name); ?>',
                        'items': <?php echo wp_json_encode($products); ?>
                    },
                    'user_data': {
                        'email_hashed': '<?php echo esc_js($hashed_email); ?>',
                        'email': '<?php echo esc_js($email); ?>'
                    }
                });
            </script>

<?php
        }
    }
}
add_action('wp_footer', 'tggr_gtm_view_item_list');

?>