<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Classic checkout: begin_checkout event
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

// Classic checkout: print script
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

// Blocks checkout: enqueue tracking script
function tggr_enqueue_blocks_checkout_script() {
    // Only run on checkout page
    if (!is_checkout() && !has_block('woocommerce/checkout')) {
        return;
    }

    $options = get_option('tggr_options');
    
    // Check if any checkout events are enabled
    $any_enabled = (isset($options['begin_checkout']) && $options['begin_checkout']) ||
                   (isset($options['add_shipping_info']) && $options['add_shipping_info']) ||
                   (isset($options['add_payment_info']) && $options['add_payment_info']);
    
    if (!$any_enabled) {
        return;
    }

    // Enqueue the Blocks checkout tracking script
    wp_enqueue_script(
        'tggr-wc-blocks-checkout',
        plugins_url('/js/wc-blocks-checkout.js', dirname(dirname(__FILE__))),
        array(),
        '1.0.0',
        true
    );

    // Prepare cart data for JavaScript
    $cart = WC()->cart;
    if (!$cart) {
        return;
    }

    $current_user = wp_get_current_user();
    $hashed_email = '';
    $email = '';

    if ($current_user->exists()) {
        $hashed_email = tggr_hash_email($current_user->user_email);
        $email = $current_user->user_email;
    }

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

    // Localize script with cart data and settings
    wp_localize_script('tggr-wc-blocks-checkout', 'tggr_checkout_data', array(
        'cart_data' => $checkout_data,
        'begin_checkout_enabled' => isset($options['begin_checkout']) && $options['begin_checkout'],
        'add_shipping_info_enabled' => isset($options['add_shipping_info']) && $options['add_shipping_info'],
        'add_payment_info_enabled' => isset($options['add_payment_info']) && $options['add_payment_info'],
    ));
}
add_action('wp_enqueue_scripts', 'tggr_enqueue_blocks_checkout_script', 20);