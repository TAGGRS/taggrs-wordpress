<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function tggr_gtm_view_item_list()
{
    $options = get_option('tggr_options');
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
                if ($product) {
                    $products[] = tggr_format_item($product->get_id());
                }
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

            $view_item_list_data = array(
                'event' => 'view_item_list',
                'ecommerce' => array(
                    'item_list_id' => $item_list_id,
                    'item_list_name' => $item_list_name,
                    'items' => $products
                ),
                'user_data' => array(
                    'email_hashed' => $hashed_email,
                    'email' => $email
                )
            );

            tggr_add_ga4_event_data('ga4-view-item-list', 'ga4ViewItemListData', $view_item_list_data);
        }
    }
}

add_action('wp_enqueue_scripts', 'tggr_gtm_view_item_list');

?>