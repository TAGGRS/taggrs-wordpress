<?php
if (!defined('ABSPATH')) exit;

function tggr_add_shipping_info()
{
    $options = get_option('tggr_options');
    $current_user = wp_get_current_user();
    $hashed_email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
    }

    $items = array();
    $cart = WC()->cart;
    
    if ($cart) {
        $total_value = 0;
        $items = tggr_format_cart_items($cart);


        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $item_total = $cart_item['line_total'];
            $total_value += $item_total; // Update de totale waarde
        }
    }

    if (isset($options['add_shipping_info']) && $options['add_shipping_info']) {
?>
    
        <script>
            window.dataLayer = window.dataLayer || [];
            dataLayer.push({
                'event': 'add_shipping_info',
                'ecommerce': {
                    'currency': '<?php echo esc_js(get_woocommerce_currency()); ?>',
                    'value': <?php echo esc_js($total_value); ?>, // Totale waarde van de winkelwagen
                    'items': <?php echo wp_json_encode($items); ?>,
                    'user_data': {
                        'email': '<?php echo esc_js($current_user->user_email); ?>',
                        'email_hashed': '<?php echo esc_js($hashed_email); ?>'
                    }
                }
            });
        </script>

<?php
    }
}

add_action('woocommerce_after_checkout_shipping_form', 'tggr_add_shipping_info');
?>
