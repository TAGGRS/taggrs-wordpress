<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function tggr_add_payment_info()
{
    $options = get_option('tggr_options');

    $current_user = wp_get_current_user();
    $hashed_email = '';
    $email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
        $email = $current_user->user_email;
    }

    if (isset($options['add_payment_info']) && $options['add_payment_info']) {
        // Controleer of we in de checkout zijn
        if (is_checkout()) {
            $cart = WC()->cart;
            if ($cart) {
                $total_value = 0;
                $items = tggr_format_cart_items($cart);
        
                foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
                    $item_total = $cart_item['line_total'];
                    $total_value += $item_total;
                }

                $payment_info_data = array(
                    'event' => 'add_payment_info',
                    'ecommerce' => array(
                        'currency' => get_woocommerce_currency(),
                        'value' => floatval($total_value),
                        'items' => $items
                    ),
                    'user_data' => array(
                        'email' => $email,
                        'email_hashed' => $hashed_email
                    )
                );

                tggr_add_ga4_event_data('ga4-add-payment-info', 'ga4PaymentInfoData', $payment_info_data);
            }
        }
    }
}

add_action('woocommerce_after_checkout_billing_form', 'tggr_add_payment_info');
?>