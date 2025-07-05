<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function tggr_gtm_view_cart()
{
    $options = get_option('tggr_options');

    $current_user = wp_get_current_user();
    $hashed_email = '';
    $email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
        $email = $current_user->user_email;
    }

    if (isset($options['view_cart']) && $options['view_cart']) {
        if (is_cart() && WC()->cart) {
            $cart = WC()->cart->get_cart();
            $products = [];
            $total_value = 0;
            foreach ($cart as $cart_item) {
                $product = $cart_item['data'];
                $price = $product->get_price();
                $qty = $cart_item['quantity'];
                if (!is_numeric($price)) $price = 0;
                if (!is_numeric($qty)) $qty = 0;
                $item_total = (float) $price * (float) $qty;

                $products[] = tggr_format_item($product->get_id(), $cart_item['quantity']);
                $total_value += $item_total;
            }

            $view_cart_data = array(
                'event' => 'view_cart',
                'ecommerce' => array(
                    'currency' => get_woocommerce_currency(),
                    'value' => $total_value,
                    'items' => $products
                ),
                'user_data' => array(
                    'email_hashed' => $hashed_email,
                    'email' => $email
                )
            );

            tggr_add_ga4_event_data('ga4-view-cart', 'ga4ViewCartData', $view_cart_data);
        }
    }
}
add_action('wp_enqueue_scripts', 'tggr_gtm_view_cart');



?>