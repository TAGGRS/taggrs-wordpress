<?php
function wc_gtm_refund($order_id) {
    $options = get_option('wc_gtm_options');
    $current_user = wp_get_current_user();
    $hashed_email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
    }
    if (isset($options['refund']) && $options['refund']) {
        $order = wc_get_order($order_id);
        ?>
        <script>
            window.dataLayer = window.dataLayer || [];
            dataLayer.push({
                'event': 'refund',
                'email': '<?php echo $current_user->user_email ?>',
                'email_hashed': '<?php echo $hashed_email ?>',
                'order_id': '<?php echo $order_id ?>',
                'value': '<?php echo $order->get_total() ?>',
                'currency': '<?php echo $order->get_currency() ?>',
                // Add additional variables here.
            });
        </script>
        <?php
    }
}

add_action('woocommerce_order_status_refunded', 'wc_gtm_refund');

?>