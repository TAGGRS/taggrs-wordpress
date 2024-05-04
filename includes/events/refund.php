<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function tggr_refund($order_id)
{
    $options = get_option('tggr_options');
    $order = wc_get_order($order_id);
    $items = [];

    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();
        if(empty($product) === True)
        {
            continue;
        }
        
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
            'item_category' => implode(', ', $item_categories) ?? '',
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
                'currency': '<?php echo esc_js($order->get_currency()); ?>',
                'transaction_id': '<?php echo esc_js($order_id); ?>',
                'value': '<?php echo esc_js($order->get_total()); ?>',
                'shipping': '<?php echo esc_js($order->get_shipping_total()); ?>',
                'tax': '<?php echo esc_js($order->get_total_tax()); ?>',
                'items': <?php echo wp_json_encode($items); ?>
            }
        });
    </script>
<?php
}

add_action('woocommerce_order_status_refunded', 'tggr_refund');
?>