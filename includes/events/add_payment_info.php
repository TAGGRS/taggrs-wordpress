<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function tggr_add_payment_info()
{
    $options = get_option('wc_gtm_options');

    $current_user = wp_get_current_user();
    $hashed_email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
    }

    // Controleer of we in de checkout zijn
    if (is_checkout()) {
        $order_id = WC()->session->get('order_awaiting_payment');
        $order = wc_get_order($order_id);

        $items = array();
        $total_value = 0; // Initialiseren van de totale waarde
        if ($order) {
            foreach ($order->get_items() as $item_id => $item) {
                $product = $item->get_product();
                $categories = wp_get_post_terms($product->get_id(), 'product_cat');
                $category_name = !empty($categories) ? $categories[0]->name : ''; // Neem de naam van de eerste categorie

                $item_total = $order->get_item_total($item, false, false);
                $total_value += $item_total * $item->get_quantity(); // Update de totale waarde

                $items[] = array(
                    'item_name' => $product->get_name(),
                    'item_id' => $product->get_id(),
                    'item_category' => $category_name,
                    'price' => $item_total,
                    'quantity' => $item->get_quantity()
                );
            }
        }
    }

    if (isset($options['add_payment_info']) && $options['add_payment_info']) {
?>
        <script>
            window.dataLayer = window.dataLayer || [];
            dataLayer.push({
                'event': 'add_payment_info',
                'ecommerce': {
                    'currency': '<?php echo esc_js(get_woocommerce_currency()); ?>',
                    'value': <?php echo esc_js($total_value); ?>, // Total value of the order
                    'items': <?php echo wp_json_encode($items); ?>
                },
                'user_data': {
                    'email': '<?php echo esc_js($current_user->user_email); ?>',
                    'email_hashed': '<?php echo esc_js($hashed_email); ?>'
                }
                // Add any additional GA4-specific variables here.
            });
        </script>

<?php
    }
}

add_action('woocommerce_after_checkout_billing_form', 'tggr_add_payment_info');
?>