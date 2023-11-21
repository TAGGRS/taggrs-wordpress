<?php
function wc_gtm_view_item_list() {
    $options = get_option('wc_gtm_options');

    $current_user = wp_get_current_user();
    $hashed_email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
    }

    if (isset($options['view_item_list']) && $options['view_item_list']) {
        if ( is_shop() || is_product_category() || is_product_tag() ) {
            global $wp_query;
            $products = [];
            foreach ( $wp_query->posts as $post ) {
                $product = wc_get_product( $post->ID );
                $products[] = [
                    'item_id' => $product->get_id(),
                    'item_name' => $product->get_name(),
                    'price' => $product->get_price()
                ];
            }
            ?>
            <script>
                window.dataLayer = window.dataLayer || [];
                dataLayer.push({
                    'event': 'view_item_list',
                    'email': '<?php echo $current_user->user_email ?>',
                    'email_hashed': '<?php echo $hashed_email ?>',
                    'items': <?php echo json_encode($products); ?>
                });
            </script>
            <?php
        }
    }
}
add_action('wp_footer', 'wc_gtm_view_item_list');
?>