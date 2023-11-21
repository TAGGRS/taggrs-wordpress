<?php
function wc_gtm_select_promotion($coupon_code) {
    $options = get_option('wc_gtm_options');
    $coupon = new WC_Coupon($coupon_code);

    $current_user = wp_get_current_user();
    $hashed_email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
    }
    if (isset($options['select_promotion']) && $options['select_promotion']) {
        ?>
        <script>
            window.dataLayer = window.dataLayer || [];
            dataLayer.push({
                'event': 'select_promotion',
                'email': '<?php echo $current_user->user_email ?>',
                'email_hashed': '<?php echo $hashed_email ?>',
                'promotion': {
                    'id': '<?php echo $coupon->get_id(); ?>',
                    'name': '<?php echo $coupon->get_code(); ?>',
                    'discount': '<?php echo $coupon->get_amount(); ?>'
                    // Add more coupon details as necessary.
                }
            });
        </script>
        <?php
    }
}
add_action('woocommerce_applied_coupon', 'wc_gtm_select_promotion');

?>