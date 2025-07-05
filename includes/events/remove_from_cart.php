<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function tggr_remove_from_cart_event($cart_item_key, $instance)
{
    // Check if instance is valid and has get_cart_item method
    if (!is_object($instance) || !method_exists($instance, 'get_cart_item')) {
        return;
    }

    $cart_item = $instance->get_cart_item($cart_item_key);
    $current_user = wp_get_current_user();
    $hashed_email = '';
    $email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
        $email = $current_user->user_email;
    }

    if (!$cart_item || !isset($cart_item['product_id'])) {
        return;
    }

    $product = wc_get_product($cart_item['product_id']);

    if (!$product) {
        return;
    }

    $item = tggr_format_item($product->get_id(), $cart_item['quantity']);

    $tggr_event_data = array(
        'event'     => 'remove_from_cart',
        'ecommerce' => array(
            'currency' => get_woocommerce_currency(),
            'value' => floatval($product->get_price()),
            'items'    => array($item),
            'user_data' => array(
                'email_hashed' => $hashed_email,
                'email' => $email
            )
        )
        // Add any extra event parameters here
    );

    // Enqueue the data as an inline script
    tggr_add_ga4_event_data('ga4-remove-from-cart', 'ga4RemoveFromCartData', $tggr_event_data);
}
add_action('woocommerce_cart_item_removed', 'tggr_remove_from_cart_event', 10, 2);


function tggr_ajax_remove_from_cart_script()
{
    $current_user = wp_get_current_user();
    $hashed_email = '';
    $email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
        $email = $current_user->user_email;
    }
    $options = get_option('tggr_options');
    if (isset($options['remove_from_cart']) && $options['remove_from_cart']) {
        $nonce = wp_create_nonce('tggr_get_product_details');
        
        // Register and enqueue AJAX remove from cart script
        wp_register_script('ga4-remove-from-cart-ajax', false, array('jquery'), TGGR_VERSION, true);
        wp_enqueue_script('ga4-remove-from-cart-ajax');
        
        // Add AJAX URL and nonce data
        wp_localize_script('ga4-remove-from-cart-ajax', 'tggr_remove_cart_vars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => $nonce,
            'email' => $email,
            'hashed_email' => $hashed_email
        ));
        
        // Add the inline script
        $inline_script = "
            jQuery(document).on('click', '.remove', function(e) {
                e.preventDefault();

                var product_id = jQuery(this).data('product_id');
                if (!product_id) {
                    console.error('Product ID not found.');
                    return;
                }

                jQuery.ajax({
                    url: tggr_remove_cart_vars.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'get_product_details',
                        product_id: product_id,
                        nonce: tggr_remove_cart_vars.nonce
                    },
                    success: function(response) {
                        if (response && response.success) {
                            window.dataLayer = window.dataLayer || [];
                            window.dataLayer.push({
                                'event': 'remove_from_cart',
                                'ecommerce': response.data,
                                'user_data': {
                                    'email': tggr_remove_cart_vars.email,
                                    'hashed_email': tggr_remove_cart_vars.hashed_email,
                                }
                            });
                        } else {
                            console.error('Failed to get product details:', response.data);
                        }
                    },
                    error: function() {
                        console.error('AJAX request failed.');
                    }
                });
            });
        ";
        
        wp_add_inline_script('ga4-remove-from-cart-ajax', $inline_script);
    }
}
add_action('wp_footer', 'tggr_ajax_remove_from_cart_script');

function tggr_get_product_details_callback()
{
    // Verify nonce for security
    $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
    if (!$nonce || !wp_verify_nonce($nonce, 'tggr_get_product_details')) {
        wp_send_json_error('Security check failed');
    }

    // Validate and sanitize the product ID
    if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
        wp_send_json_error('Invalid product ID');
    }

    $product_id = intval($_POST['product_id']);
    $product = wc_get_product($product_id);

    if (!$product) {
        wp_send_json_error('Product not found');
    }

    $categories = wp_get_post_terms($product_id, 'product_cat');
    $category_names = array();
    if (is_array($categories) && !is_wp_error($categories)) {
        foreach ($categories as $category) {
            $category_names[] = $category->name;
        }
    }
    $category_list = implode(', ', $category_names);

    // Construct and return the product details
    wp_send_json_success(array(
        'currency' => get_woocommerce_currency(),
        'value' => floatval($product->get_price()),
        'items' => array(array(
            'item_id' => $product->get_id(),
            'item_name' => $product->get_name(),
            'item_category' => $category_list,
            'quantity' => 1,
            'price' => floatval($product->get_price()),
        ))
    ));
}
add_action('wp_ajax_get_product_details', 'tggr_get_product_details_callback');
add_action('wp_ajax_nopriv_get_product_details', 'tggr_get_product_details_callback');

?>
