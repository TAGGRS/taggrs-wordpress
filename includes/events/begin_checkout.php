<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function tggr_begin_checkout_event() {
    $options = get_option('tggr_options');
    $current_user = wp_get_current_user();
    $hashed_email = '';
    $email = '';

    if ($current_user->exists()) {
        $hashed_email = tggr_hash_email($current_user->user_email);
        $email = $current_user->user_email;
    }

    if (isset($options['begin_checkout']) && $options['begin_checkout']) {
        $cart = WC()->cart;
        $items = tggr_format_cart_items($cart);
        $cart_total = $cart->cart_contents_total;
        $applied_coupons = $cart->get_applied_coupons();
        $coupon_code = !empty($applied_coupons) ? $applied_coupons[0] : '';

        $checkout_data = array(
            'event' => 'begin_checkout',
            'ecommerce' => array(
                'currency' => get_woocommerce_currency(),
                'value' => floatval($cart_total),
                'coupon' => $coupon_code,
                'items' => $items,
                'user_data' => array(
                    'email_hashed' => $hashed_email,
                    'email' => $email
                )
            )
        );

        wp_register_script('ga4-begin-checkout', false, array(), '1.0.0', true);
        wp_enqueue_script('ga4-begin-checkout');
        wp_add_inline_script('ga4-begin-checkout', 'window.ga4CheckoutData = ' . wp_json_encode($checkout_data) . ';', 'before');
    }
}
add_action('woocommerce_before_checkout_form', 'tggr_begin_checkout_event');

function tggr_print_checkout_script() {
    if (!wp_script_is('ga4-begin-checkout', 'enqueued')) {
        return;
    }

    $options = get_option('tggr_options');
    if (isset($options['begin_checkout']) && $options['begin_checkout']) {
        ?>
        <script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function() {
                if (window.ga4CheckoutData) {
                    window.dataLayer = window.dataLayer || [];
                    window.dataLayer.push(window.ga4CheckoutData);
                }
            });
        </script>
        <?php
    }
}
add_action('wp_footer', 'tggr_print_checkout_script');