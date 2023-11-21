<?php

function wc_gtm_view_cart() {
    $options = get_option('wc_gtm_options');

    $current_user = wp_get_current_user();
    $hashed_email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
    }

    if (isset($options['view_cart']) && $options['view_cart']) {
        if (is_cart() && WC()->cart) {
            $cart = WC()->cart->get_cart();
            $products = [];
            foreach ($cart as $cart_item) {
                $product = $cart_item['data'];
                $products[] = [
                    'item_id' => $product->get_id(),
                    'item_name' => $product->get_name(),
                    'quantity' => $cart_item['quantity'],
                    // Add more product attributes if required
                ];
            }
            $json_products = json_encode($products);
            ?>
            <script>
                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push({
                    'event': 'view_cart',
                    'email': '<?php echo $current_user->user_email ?>',
                    'email_hashed': '<?php echo $hashed_email ?>',
                    'ecommerce': {
                        'currency': '<?php echo get_woocommerce_currency(); ?>',
                        'items': <?php echo $json_products; ?>
                    },
                    // Add other required data attributes
                });
            </script>
            <?php
        }
    }
}
add_action('wp_footer', 'wc_gtm_view_cart');


?>