<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function tggr_gtm_view_item()
{
    $options = get_option('tggr_options');
    $current_user = wp_get_current_user();
    $hashed_email = '';
    $email = '';
    
    if ($current_user->exists()) {
        $hashed_email = tggr_hash_email($current_user->user_email);
        $email = $current_user->user_email;
    }
    
    if (isset($options['view_item']) && $options['view_item']) {
        if (is_product()) {
            global $product;
            // If the global product isn't set, get it based on the current ID.
            if (!$product) {
                $product = wc_get_product(get_the_ID());
            }

            $item = tggr_format_item($product->get_id());

            if ($product) {
                $view_item_data = array(
                    'event' => 'view_item',
                    'ecommerce' => array(
                        'currency' => get_woocommerce_currency(),
                        'value' => floatval($product->get_price()),
                        'items' => array($item),
                        'user_data' => array(
                            'email' => $email,
                            'email_hashed' => $hashed_email
                        )
                    )
                );

                tggr_add_ga4_event_data('ga4-view-item', 'ga4ViewItemData', $view_item_data);
            }
        }
    }
}
add_action('wp_enqueue_scripts', 'tggr_gtm_view_item');

?>