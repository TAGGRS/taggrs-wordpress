<?php

function wc_gtm_view_item() {
    $options = get_option('wc_gtm_options');
    $current_user = wp_get_current_user();
    $hashed_email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
    }
    if (isset($options['view_item']) && $options['view_item']) {
        if ( is_product() ) {
            global $product;
            // If the global product isn't set, get it based on the current ID.
            if ( ! $product ) {
                $product = wc_get_product( get_the_ID() );
            }
            if ( $product ) {
                ?>
                <script>
                    window.dataLayer = window.dataLayer || [];
                    window.dataLayer.push({
                        'event': 'view_item',
                        'email': '<?php echo $current_user->user_email ?>',
                        'email_hashed': '<?php echo $hashed_email ?>',
                        'items': [{
                            'item_id': '<?php echo $product->get_id(); ?>',
                            'item_name': '<?php echo esc_js($product->get_name()); ?>',
                            'price': '<?php echo $product->get_price(); ?>',
                            'currency': '<?php echo get_woocommerce_currency(); ?>',
                            'item_category': '<?php echo implode(", ", $product->get_category_ids()); ?>',
                        }]
                    });
                </script>
                <?php
            }
        }
    }
}
add_action('wp_footer', 'wc_gtm_view_item');
?>