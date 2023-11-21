<?php


function ga4_remove_from_cart_event( $cart_item_key, $instance ) {
    $cart_item = $instance->get_cart_item( $cart_item_key );
    $current_user = wp_get_current_user();
    $hashed_email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
    }

    if (!$cart_item || !isset($cart_item['product_id'])) {
        return;
    }

    $product = wc_get_product($cart_item['product_id']);

    if (!$product) {
        return;
    }

    $ga4_event_data = array(
        'event'         => 'remove_from_cart',
        'product_name'  => $product->get_name(),
        'product_id'    => $product->get_id(),
        'hashed_email'    => $hashed_email,
        'email'    => $current_user->user_email,
        'quantity'      => 1, // Usually it's 1 item removed at a time. Adjust if needed.
        'price'         => $product->get_price(),
        'currency'      => get_woocommerce_currency(),
    );

    // Enqueue the data as an inline script
    wp_register_script( 'ga4-remove-from-cart', false );
    wp_enqueue_script( 'ga4-remove-from-cart' );
    wp_add_inline_script( 'ga4-remove-from-cart', 'window.ga4RemoveFromCartData = ' . json_encode( $ga4_event_data ) . ';', 'before' );
}
add_action( 'woocommerce_cart_item_removed', 'ga4_remove_from_cart_event', 10, 2 );


function ga4_print_remove_from_cart_script() {
    if( ! wp_script_is( 'ga4-remove-from-cart', 'enqueued' ) ) {
        return;
    }

    $options = get_option('wc_gtm_options');
    if (isset($options['remove_from_cart']) && $options['remove_from_cart']) {
    ?>
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            if( window.ga4RemoveFromCartData ) {
                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push({
                    'event': 'remove_from_cart',
                    'email': window.ga4RemoveFromCartData.email,
                    'hashed_email': window.ga4RemoveFromCartData.hashed_email,
                    'ecommerce': {
                        'currencyCode': window.ga4RemoveFromCartData.currency,
                        'remove': {
                            'products': [{
                                'name': window.ga4RemoveFromCartData.product_name,
                                'id': window.ga4RemoveFromCartData.product_id.toString(),
                                'price': window.ga4RemoveFromCartData.price.toString(),
                                'quantity': window.ga4RemoveFromCartData.quantity
                            }]
                        }
                    }
                });
            }
        });
    </script>
    <?php
    }
}
add_action( 'wp_footer', 'ga4_print_remove_from_cart_script' );



function ga4_ajax_remove_from_cart_script() {

    $options = get_option('wc_gtm_options');
    if (isset($options['remove_from_cart']) && $options['remove_from_cart']) {
    ?>
    <script type="text/javascript">
        document.body.addEventListener('click', function(e) {
            console.log('Clicked element:', e.target);
        });

        jQuery(document).on('click', '.remove', function(e) {
            e.preventDefault(); // Prevent the default action so the page doesn't navigate immediately

            // Extract the required data attributes
            var product_id = jQuery(this).data('product_id');
            var product_name = jQuery(this).data('product_name'); // It seems empty in the given example but included just in case
            var price = jQuery(this).data('price'); // It seems empty in the given example but included just in case
            var quantity = jQuery(this).data('quantity'); // It seems empty in the given example but included just in case
            var email = jQuery(this).data('email'); // It seems empty in the given example but included just in case
            var hashed_email = jQuery(this).data('hashed_email'); // It seems empty in the given example but included just in case
            // Add any other data points you want to capture here

            // Your dataLayer push logic
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                'event': 'remove_from_cart',
                'email': email,
                'hashed_email': hashed_email,
                'ecommerce': {
                    'remove': {
                        'products': [{
                            'name': product_name,
                            'id': product_id,
                            'price': price,
                            'quantity': quantity
                            // Add any other data points you want to capture here
                        }]
                    }
                }
            });

            // After a brief delay, continue with the default action (to give dataLayer time to push the event)
            setTimeout(function() {
                window.location.href = e.target.href;
            }, 500);
        });

    </script>
    <?php
    }
}
add_action( 'wp_footer', 'ga4_ajax_remove_from_cart_script' );




?>