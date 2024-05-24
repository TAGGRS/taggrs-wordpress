<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function tggr_begin_checkout()
{
    $options = get_option('tggr_options');
    $current_user = wp_get_current_user();
    $hashed_email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
    }

    if (isset($options['begin_checkout']) && $options['begin_checkout']) {
        $cart = WC()->cart;
        $items = tggr_format_cart_items($cart);

        // Totaalwaarde van de winkelwagen
        $cart_total = $cart->cart_contents_total;

        // Voeg hier de logica toe om de gebruikte couponcode op te halen, indien aanwezig
        $applied_coupons = $cart->get_applied_coupons();
        $coupon_code = !empty($applied_coupons) ? $applied_coupons[0] : '';

?>
        <script>
            window.dataLayer = window.dataLayer || [];
            dataLayer.push({
                'event': 'begin_checkout',
                'ecommerce': {
                    'currency': '<?php echo esc_js(get_woocommerce_currency()); ?>', // Voeg de valuta toe
                    'value': <?php echo esc_js($cart_total); ?>, // Totaalwaarde van de winkelwagen
                    'coupon': '<?php echo esc_js($coupon_code); ?>', // Gebruikte couponcode
                    'items': <?php echo wp_json_encode($items); ?>
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

add_action('woocommerce_before_checkout_form', 'tggr_begin_checkout');
?>