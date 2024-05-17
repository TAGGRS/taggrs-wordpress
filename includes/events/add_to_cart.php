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

    $cart = WC()->cart;
    $item = tggr_format_item($product_id, $quantity);
    $tggr_event_data = array(
        'event'     => 'add_to_cart',
        'ecommerce' => array(
            'currency' => get_woocommerce_currency(),
            'value' => floatval($product->get_price()),
            'items'    => array($item),
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
    $options = get_option('tggr_options');
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
                    var product_categories = getProductCategories($thisbutton[0].parentNode);

                    // Hashed email and email are assumed here as data attributes, otherwise, these should be obtained in another way
                    var email = $thisbutton.data('email');
                    var hashed_email = $thisbutton.data('hashed_email');

                    var item = {
                        'item_id': product_id,
                        'item_name': product_name,
                        'quantity': quantity,
                        'price': product_price
                    };

                    if (product_categories.length > 0) {
                        for (let i = 0; i < product_categories.length; i++) {
                            const product_category = product_categories[i];
                            if(i > 0)
                                item['item_category' + (i + 1)] = product_category;
                            else 
                                item['item_category'] = product_category;
                        }
                        item['item_category'] = product_categories[0];
                    }

                    window.dataLayer = window.dataLayer || [];
                    window.dataLayer.push({
                        'event': 'add_to_cart',
                        'ecommerce': {
                            'currency': '<?php echo esc_js(get_woocommerce_currency()); ?>',
                            'value': product_price,
                            'items': [item],
                            'user_data': {
                                'email_hashed': hashed_email,
                                'email': email
                            }
                        }
                    });
                });
            });

            // Above each add to cart button an li element has all the product's categories. This is stored in the classlist. Eg: "product_cat-{category}".
            const getProductCategories = (element) => {
                const allClasses = element.className.split(/\s+/);
                const filteredClasses = allClasses.filter(cls => cls.startsWith('product_cat-'));
                const categorySuffixes = filteredClasses.map(cls => cls.substring('product_cat-'.length));

                return categorySuffixes;
            }
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

    $options = get_option('tggr_options');
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