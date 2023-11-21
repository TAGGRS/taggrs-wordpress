<?php
function wc_gtm_begin_checkout() {
    // If the user is logged in, get their email. Otherwise, use a placeholder (it will be filled out later in the checkout process).
    $options = get_option('wc_gtm_options');

    $current_user = wp_get_current_user();
    $hashed_email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
    }

    if (isset($options['begin_checkout']) && $options['begin_checkout']) {

    // You can capture additional details here. For this example, I'm just capturing the email.
    ?>
    <script>
        window.dataLayer = window.dataLayer || [];
        dataLayer.push({
            'event': 'begin_checkout',
            'email': '<?php echo $current_user->user_email ?>',
            'email_hashed': '<?php echo $hashed_email ?>',
        });
    </script>
    <?php
    }
}

add_action('woocommerce_before_checkout_form', 'wc_gtm_begin_checkout');
?>