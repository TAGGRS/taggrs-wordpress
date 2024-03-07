<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function tggr_gtm_view_cart()
{
    $options = get_option('wc_gtm_options');

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
                $item_total = $product->get_price() * $cart_item['quantity'];
                $categories = wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'names'));
                $category_list = implode(', ', $categories);

                $products[] = [
                    'item_id' => $product->get_id(),
                    'item_name' => $product->get_name(),
                    'item_category' => $category_list,
                    'quantity' => $cart_item['quantity'],
                    'price' => $product->get_price(),
                ];
                $total_value += $item_total;
            }
?>
            <script>
                window.dataLayer = window.dataLayer || [];
                dataLayer.push({
                    'event': 'view_cart',
                    'ecommerce': {
                        'currency': '<?php echo esc_js(get_woocommerce_currency()); ?>',
                        'value': <?php echo esc_js($total_value); ?>,
                        'items': <?php echo wp_json_encode($products); ?>
                    },
                    'user_data': {
                        'email_hashed': '<?php echo esc_js($hashed_email); ?>',
                        'email': '<?php echo esc_js($email); ?>'
                        // Include any additional user data here
                    }
                });
            </script>

<?php
        }
    }
}
add_action('wp_footer', 'tggr_gtm_view_cart');



?>