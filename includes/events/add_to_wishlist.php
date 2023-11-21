<?php
function wc_gtm_add_to_wishlist() {
    $options = get_option('wc_gtm_options');

    $current_user = wp_get_current_user();
    $hashed_email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
    }
    if (isset($options['add_to_wishlist']) && $options['add_to_wishlist']) {
        $wishlist_items = get_user_wishlist_items();  // This is a fictional function, replace it with actual logic to fetch wishlist items.

        $items = [];
        foreach ($wishlist_items as $item) {
            $product = wc_get_product($item->product_id);
            $items[] = [
                'id' => $product->get_id(),
                'name' => $product->get_name(),
                'category' => $product->get_category(),
                'price' => $product->get_price(),
                'quantity' => $item->quantity  // Assuming each wishlist item has a quantity. This might not be the case for all wishlists.
            ];
        }

        ?>
        <script>
            window.dataLayer = window.dataLayer || [];
            dataLayer.push({
                'event': 'add_to_wishlist',
                'email': '<?php echo $current_user->user_email ?>',
                'email_hashed': '<?php echo $hashed_email ?>',
                'items': <?php echo json_encode($items) ?>
                // Add additional variables here.
            });
        </script>
        <?php
    }
}

// Adjust the hook based on your wishlist feature.
add_action('your_wishlist_add_action', 'wc_gtm_add_to_wishlist');
?>