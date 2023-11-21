<?php
function ga4_add_to_cart_event( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {

    $product = wc_get_product( $product_id );
    $current_user = wp_get_current_user();
    $hashed_email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
    }

    $ga4_event_data = array(
        'event'         => 'add_to_cart',
        'product_name'  => $product->get_name(),
        'product_id'    => $product_id,
        'quantity'      => $quantity,
        'price'         => $product->get_price(),
        'currency'      => get_woocommerce_currency(),
        'hashed_email'    => $hashed_email,
        'email'    => $current_user->user_email,
        // Add any other data you want to send to GA4 here...
    );

    // We'll add this data to the footer of the page in a script tag.
    wp_register_script( 'ga4-add-to-cart', false );
    wp_enqueue_script( 'ga4-add-to-cart' );
    wp_add_inline_script( 'ga4-add-to-cart', 'window.ga4AddToCartData = ' . json_encode( $ga4_event_data ) . ';', 'before' );

}
add_action( 'woocommerce_add_to_cart', 'ga4_add_to_cart_event', 10, 6 );

function ga4_ajax_add_to_cart_script() {

    $options = get_option('wc_gtm_options');
    if (isset($options['add_to_cart']) && $options['add_to_cart']) {

    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $(document.body).on('added_to_cart', function(event, fragments, cart_hash, $thisbutton) {

                // Extract product data from the clicked button
                var product_id = $thisbutton.data('product_id');
                var product_name = $thisbutton.data('product_name'); // Ensure this attribute is set
                var quantity = $thisbutton.data('quantity'); // Ensure this attribute is set
                var product_price = $thisbutton.data('price'); // Ensure this attribute is set
                var email = $thisbutton.data('email'); // Ensure this attribute is set
                var hashed_email = $thisbutton.data('hashed_email'); // Ensure this attribute is set

                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push({
                    'event': 'add_to_cart',
                    'email': email,
                    'hashed_email': hashed_email,
                    'ecommerce': {
                        'currency': '<?php echo get_woocommerce_currency(); ?>',
                        'add': {
                            'products': [{
                                'name': product_name,
                                'id': product_id,
                                'price': product_price,
                                'quantity': quantity
                                // Add other GA4 data parameters here...
                            }]
                        }
                    }
                });
            });
        });
    </script>
    <?php
    }
}
add_action( 'wp_footer', 'ga4_ajax_add_to_cart_script' );


function ga4_print_add_to_cart_script() {
    if( ! wp_script_is( 'ga4-add-to-cart', 'enqueued' ) ) {
        return;
    }

    $options = get_option('wc_gtm_options');
    if (isset($options['add_to_cart']) && $options['add_to_cart']) {
    ?>
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            if( window.ga4AddToCartData ) {
                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push({
                    'event': 'add_to_cart',
                    'email': window.ga4AddToCartData.email,
                    'hashed_email': window.ga4AddToCartData.hashed_email,
                    'ecommerce': {
                        'currency': window.ga4AddToCartData.currency,
                        'add': {
                            'products': [{
                                'name': window.ga4AddToCartData.product_name,
                                'id': window.ga4AddToCartData.product_id,
                                'price': window.ga4AddToCartData.price,
                                'quantity': window.ga4AddToCartData.quantity
                                // Add other GA4 data parameters here...
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
add_action( 'wp_footer', 'ga4_print_add_to_cart_script' );


?>