<?php
if (!defined('ABSPATH')) exit;

// Classic checkout: add_shipping_info event
// Note: For Blocks checkout, this is handled by js/wc-blocks-checkout.js
function tggr_add_shipping_info()
{
    $options = get_option('tggr_options');

    if (!isset($options['add_shipping_info']) || !$options['add_shipping_info']) {
        return;
    }

    $current_user = wp_get_current_user();
    $hashed_email = '';
    $email = '';
    
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
        $email = $current_user->user_email;
    }

    $items = array();
    $total_value = 0;
    $cart = WC()->cart;
    
    if ($cart) {
        $items = tggr_format_cart_items($cart);
        $total_value = $cart->cart_contents_total;
    }
?>
    
    <script>
        // Only track for classic checkout (Blocks checkout is handled separately)
        if (!document.querySelector('.wc-block-checkout')) {
            window.dataLayer = window.dataLayer || [];
            
            // Track when shipping method is selected
            jQuery(document).ready(function($) {
                var lastTrackedShippingMethod = null;
                
                function trackShippingInfo() {
                    var shippingMethod = $('input[name^="shipping_method"]:checked').val() || '';
                    
                    // Don't track if no method selected or same as last tracked
                    if (!shippingMethod || shippingMethod === lastTrackedShippingMethod) {
                        return;
                    }
                    
                    dataLayer.push({
                        'event': 'add_shipping_info',
                        'ecommerce': {
                            'currency': '<?php echo esc_js(get_woocommerce_currency()); ?>',
                            'value': <?php echo esc_js($total_value); ?>,
                            'items': <?php echo wp_json_encode($items); ?>,
                            'shipping_tier': shippingMethod
                        },
                        'user_data': {
                            'email': '<?php echo esc_js($email); ?>',
                            'email_hashed': '<?php echo esc_js($hashed_email); ?>'
                        }
                    });
                    
                    lastTrackedShippingMethod = shippingMethod;
                }
                
                // Track when shipping method is selected
                $(document.body).on('change', 'input[name^="shipping_method"]', function() {
                    trackShippingInfo();
                });
                
                // Also track if shipping method is already selected
                if ($('input[name^="shipping_method"]:checked').length > 0) {
                    setTimeout(trackShippingInfo, 500);
                }
            });
        }
    </script>

<?php
}

add_action('woocommerce_after_checkout_shipping_form', 'tggr_add_shipping_info');
?>
