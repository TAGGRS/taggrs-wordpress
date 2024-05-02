<?php
if ( ! defined( 'ABSPATH' ) ) exit;

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
    $total_value = 0; // Initialiseren van de totale waarde

    if ($cart) {
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            $categories = wp_get_post_terms($product->get_id(), 'product_cat');
            $category_name = !empty($categories) ? $categories[0]->name : ''; // Neem de naam van de eerste categorie

            $item_total = $cart_item['line_total'];
            $total_value += $item_total; // Update de totale waarde

            $items[] = array(
                'item_name' => $product->get_name(),
                'item_id' => $product->get_id(),
                'item_category' => $category_name,
                'price' => $cart_item['line_price'],
                'quantity' => $cart_item['quantity']
            );
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
                // Voeg hier eventueel extra GA4-specifieke variabelen toe.
            });
        </script>

<?php
    }
}

add_action('woocommerce_after_checkout_shipping_form', 'tggr_add_shipping_info');
?>