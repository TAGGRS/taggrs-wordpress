<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function tggr_gtm_view_item()
{

    $options = get_option('tggr_options');
    $current_user = wp_get_current_user();
    $hashed_email = '';
    if ($current_user->exists()) {
        $hashed_email = tggr_hash_email($current_user->user_email);
    }
    if (isset($options['view_item']) && $options['view_item']) {
        if (is_product()) {
            global $product;
            // If the global product isn't set, get it based on the current ID.
            if (!$product) {
                $product = wc_get_product(get_the_ID());
            }

            $item = tggr_format_item($product->get_id());

            if ($product) {
?>
                <script>
                    window.dataLayer = window.dataLayer || [];
                    window.dataLayer.push({
                        'event': 'view_item',
                        'ecommerce': {
                            'currency': '<?php echo esc_js(get_woocommerce_currency()); ?>',
                            'value': '<?php echo esc_js($product->get_price()); ?>',
                            'items': [<?php echo wp_json_encode($item); ?>],
                            'user_data': {
                                'email': '<?php echo esc_js($current_user->user_email); ?>',
                                'email_hashed': '<?php echo esc_js($hashed_email); ?>'
                            }
                        }
                    });
                </script>

<?php
            }
        }
    }
}
add_action('wp_footer', 'tggr_gtm_view_item');

?>