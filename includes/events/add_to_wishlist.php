<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function tggr_add_to_wishlist()
{
    $options = get_option('tggr_options');

    $current_user = wp_get_current_user();
    $hashed_email = '';
    $email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
        $email = $current_user->user_email;
    }
    
    if (isset($options['add_to_wishlist']) && $options['add_to_wishlist']) {
        $wishlist_items = get_user_wishlist_items();

        $cart = WC()->cart;
        $items = tggr_format_cart_items($cart);

        $total_value = 0;
        foreach ($wishlist_items as $item) {
            $product = wc_get_product($item->product_id);
            $item_price = $product->get_price();
            $total_value += $item_price * $item->quantity;
        }

        $wishlist_data = array(
            'event' => 'add_to_wishlist',
            'ecommerce' => array(
                'currency' => get_woocommerce_currency(),
                'value' => floatval($total_value),
                'items' => $items
            ),
            'user_data' => array(
                'email_hashed' => $hashed_email,
                'email' => $email
            )
        );

        // Use centralized script manager
        tggr_add_ga4_event_data('ga4-add-to-wishlist', 'ga4WishlistData', $wishlist_data);
    }
}

add_action('your_wishlist_add_action', 'tggr_add_to_wishlist');
?>