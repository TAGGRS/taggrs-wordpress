<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function tggr_add_to_cart_event($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data)
{
    $product = wc_get_product($product_id);
    $current_user = wp_get_current_user();
    $hashed_email = '';
    $email = '';
    if ($current_user->exists()) {
        $hashed_email = tggr_hash_email($current_user->user_email);
        $email = $current_user->user_email;
    }
    $categories = wp_get_post_terms($product_id, 'product_cat');
    $category_names = array();
    foreach ($categories as $category) {
        $category_names[] = $category->name;
    }
    $category_list = implode(', ', $category_names);
    $tggr_event_data = array(
        'event'     => 'add_to_cart',
        'ecommerce' => array(
            'currency' => get_woocommerce_currency(),
            'value' => floatval($product->get_price()),
            'items'    => array(array(
                'item_id'    => $product_id,
                'item_name'  => $product->get_name(),
                'item_category' => $category_list,
                'quantity'   => $quantity,
                'price'      => floatval($product->get_price()),
            )),
            'user_data' => array(
                'email_hashed' => $hashed_email,
                'email' => $email
            )
        )
        // Voeg hier eventuele extra event parameters toe
    );

    wp_register_script('ga4-add-to-cart', false);
    wp_enqueue_script('ga4-add-to-cart');
    wp_add_inline_script('ga4-add-to-cart', 'window.ga4AddToCartData = ' . wp_json_encode($tggr_event_data) . ';', 'before');
}
add_action('woocommerce_add_to_cart', 'tggr_add_to_cart_event', 10, 6);



function tggr_ajax_add_to_cart_script()
{

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
                    var product_category = $thisbutton.data('category');

                    // Hashed email and email are assumed here as data attributes, otherwise, these should be obtained in another way
                    var email = $thisbutton.data('email');
                    var hashed_email = $thisbutton.data('hashed_email');

                    window.dataLayer = window.dataLayer || [];
                    window.dataLayer.push({
                        'event': 'add_to_cart',
                        'ecommerce': {
                            'currency': '<?php echo esc_js(get_woocommerce_currency()); ?>',
                            'value': product_price,
                            'items': [{
                                'item_id': product_id,
                                'item_name': product_name,
                                'item_category': product_category,
                                'quantity': quantity,
                                'price': product_price
                            }],
                            'user_data': {
                                'email_hashed': hashed_email,
                                'email': email
                            }
                        }
                    });
                });
            });
        </script>

    <?php
    }
}
add_action('wp_footer', 'tggr_ajax_add_to_cart_script');



function tggr_print_add_to_cart_script()
{
    if (!wp_script_is('ga4-add-to-cart', 'enqueued')) {
        return;
    }

    $options = get_option('wc_gtm_options');
    if (isset($options['add_to_cart']) && $options['add_to_cart']) {
    ?>
        <script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function() {
                if (window.ga4AddToCartData) {
                    window.dataLayer = window.dataLayer || [];
                    window.dataLayer.push(window.ga4AddToCartData);
                }
            });
        </script>
<?php
    }
}
add_action('wp_footer', 'tggr_print_add_to_cart_script');



?>