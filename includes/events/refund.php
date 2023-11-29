<?php
function wc_ga4_refund($order_id) {
    $options = get_option('wc_gtm_options');
    $order = wc_get_order($order_id);
    $items = [];

    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();
        $categories = get_the_terms($product->get_id(), 'product_cat');
        $item_categories = array();
        foreach ($categories as $category) {
            $item_categories[] = $category->name;
        }

        // Voeg hier aanvullende productcategorieën toe indien nodig
        // Voorbeeld: $item_category2 = 'Adult';

        $items[] = [
            'item_id' => $product->get_id(),
            'item_name' => $product->get_name(),
            'item_category' => $item_categories[0] ?? '',
            'price' => $order->get_item_total($item),
            'quantity' => $item->get_quantity()
        ];
    }

    ?>
    <script>
        window.dataLayer = window.dataLayer || [];
        dataLayer.push({
            'event': 'refund',
            'ecommerce': {
                'currency': '<?php echo $order->get_currency(); ?>',
                'transaction_id': '<?php echo $order_id; ?>',
                'value': '<?php echo $order->get_total(); ?>',
                'shipping': '<?php echo $order->get_shipping_total(); ?>',
                'tax': '<?php echo $order->get_total_tax(); ?>',
                'items': <?php echo json_encode($items); ?>
            }
        });
    </script>
    <?php
}

add_action('woocommerce_order_status_refunded', 'wc_ga4_refund');
?>
