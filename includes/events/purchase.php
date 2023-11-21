<?php
function hash_email( $email ) {
    // Using SHA256 as recommended by Google
    return hash('sha256', strtolower(trim($email)));
}

function wc_gtm_purchase( $order_id ) {
    $options = get_option('wc_gtm_options');

    if (isset($options['purchase']) && $options['purchase']) {
        $order = wc_get_order( $order_id );
        $items = $order->get_items();
        $products = [];

        foreach ( $items as $item ) {
            $product = $item->get_product();
            $products[] = [
                'item_id' => $product->get_id(),
                'item_name' => $product->get_name(),
                'quantity' => $item->get_quantity(),
                'price' => $product->get_price()
            ];
        }

        $hashed_email = hash_email($order->get_billing_email()); // Assume you have the hash_email function from previous example
        ?>
        <script>
            window.dataLayer = window.dataLayer || [];
            dataLayer.push({
                'event': 'purchase',
                'currency': '<?php echo $order->get_currency(); ?>',
                'value': <?php echo $order->get_total(); ?>,
                'transaction_id': '<?php echo $order->get_order_number(); ?>',
                'user_data': {
                    'email': '<?php echo $order->get_billing_email() ?>',
                    'email_hashed': '<?php echo $hashed_email ?>',
                    'first_name': '<?php echo $order->get_billing_first_name(); ?>',
                    'last_name': '<?php echo $order->get_billing_last_name(); ?>',
                    'address_1': '<?php echo $order->get_billing_address_1(); ?>',
                    'address_2': '<?php echo $order->get_billing_address_2(); ?>',
                    'city': '<?php echo $order->get_billing_city(); ?>',
                    'postcode': '<?php echo $order->get_billing_postcode(); ?>',
                    'country': '<?php echo $order->get_billing_country(); ?>',
                    'state': '<?php echo $order->get_billing_state(); ?>',
                    'phone': '<?php echo $order->get_billing_phone(); ?>'
                },
                'items': <?php echo json_encode($products); ?>
            });
        </script>
        <?php
    }
}
add_action('woocommerce_thankyou', 'wc_gtm_purchase');
?>