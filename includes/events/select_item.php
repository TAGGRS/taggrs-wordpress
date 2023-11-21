<?php
function wc_gtm_select_item() {
    $options = get_option('wc_gtm_options');
    global $product;

    $current_user = wp_get_current_user();
    $hashed_email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
    }
    if (isset($options['select_item']) && $options['select_item']) {
        $categories = get_the_terms( $product->get_id(), 'product_cat' );
        if ( ! empty( $categories ) ) {
            $category = $categories[0]->name;
        } else {
            $category = '';  // or some default value
        }
        ?>
        <script>
            window.dataLayer = window.dataLayer || [];
            dataLayer.push({
                'event': 'select_item',
                'email': '<?php echo $current_user->user_email ?>',
                'email_hashed': '<?php echo $hashed_email ?>',
                'item': {
                    'id': '<?php echo $product->get_id(); ?>',
                    'name': '<?php echo $product->get_name(); ?>',
                    'category': '<?php echo $category; ?>',
                    'price': '<?php echo $product->get_price(); ?>'
                }
            });
        </script>
        <?php
    }
}
add_action('woocommerce_before_single_product', 'wc_gtm_select_item');
?>