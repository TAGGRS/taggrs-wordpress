<?php
function wc_ga4_begin_checkout() {
    $options = get_option('wc_gtm_options');
    $current_user = wp_get_current_user();
    $hashed_email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
    }

    if (isset($options['begin_checkout']) && $options['begin_checkout']) {
        $cart = WC()->cart;
        $cart_items = $cart->get_cart();
        $items = array();
        foreach ($cart_items as $cart_item_key => $cart_item) {
            $product_id = $cart_item['product_id'];
            $product = wc_get_product($product_id);

            $items[] = array(
                'item_id' => $product->get_id(),
                'item_name' => $product->get_name(),
                'price' => $product->get_price(),
                'item_category' => implode(', ', $product->get_category_ids()),
                'quantity' => $cart_item['quantity'],
                // Voeg indien nodig meer attributen toe, zoals item_category
            );
        }

        // Totaalwaarde van de winkelwagen
        $cart_total = $cart->get_total();

        // Voeg hier de logica toe om de gebruikte couponcode op te halen, indien aanwezig
        $applied_coupons = $cart->get_applied_coupons();
        $coupon_code = !empty($applied_coupons) ? $applied_coupons[0] : '';

        ?>
        <script>
            window.dataLayer = window.dataLayer || [];
            dataLayer.push({
                'event': 'begin_checkout',
                'ecommerce': {
                    'currency': '<?php echo get_woocommerce_currency(); ?>', // Voeg de valuta toe
                    'value': <?php echo $cart_total; ?>, // Totaalwaarde van de winkelwagen
                    'coupon': '<?php echo $coupon_code; ?>', // Gebruikte couponcode
                    'items': <?php echo json_encode($items); ?>
                },
                'user_data': {
                    'email_hashed': '<?php echo $hashed_email ?>',
                    'email': '<?php echo $current_user->user_email ?>'
                }
            });
        </script>
        <?php
    }
}

add_action('woocommerce_before_checkout_form', 'wc_ga4_begin_checkout');
?>
