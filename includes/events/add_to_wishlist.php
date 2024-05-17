<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function tggr_add_to_wishlist()
{
    $options = get_option('tggr_options');

    $current_user = wp_get_current_user();
    $hashed_email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
    }
    if (isset($options['add_to_wishlist']) && $options['add_to_wishlist']) {
        $wishlist_items = get_user_wishlist_items(); // Vervang dit door de daadwerkelijke logica om verlanglijst items op te halen.

        $cart = WC()->cart;
        $items = tggr_format_cart_items($cart);

        $total_value = 0;
        foreach ($wishlist_items as $item) {
            $product = wc_get_product($item->product_id);
            $item_price = $product->get_price();
            $total_value += $item_price * $item->quantity;
        }

?>
        <script>
            window.dataLayer = window.dataLayer || [];
            dataLayer.push({
                'event': 'add_to_wishlist',
                'ecommerce': {
                    'currency': '<?php echo esc_js(get_woocommerce_currency()); ?>', // De valuta van de winkel
                    'value': <?php echo esc_js($total_value); ?>, // Totale waarde van de toegevoegde items
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

// Pas de hook aan op basis van je verlanglijst functionaliteit.
add_action('your_wishlist_add_action', 'tggr_add_to_wishlist');
?>