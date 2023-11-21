<?php
function wc_gtm_add_shipping_info() {
    $options = get_option('wc_gtm_options');
    $current_user = wp_get_current_user();
    $hashed_email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
    }
    if (isset($options['add_shipping_info']) && $options['add_shipping_info']) {
        ?>
        <script>
            window.dataLayer = window.dataLayer || [];
            dataLayer.push({
                'event': 'add_shipping_info',
                'email': '<?php echo $current_user->user_email ?>',
                'email_hashed': '<?php echo $hashed_email ?>',
                // Add additional variables here.
            });
        </script>
        <?php
    }
}

add_action('woocommerce_after_checkout_shipping_form', 'wc_gtm_add_shipping_info');

?>