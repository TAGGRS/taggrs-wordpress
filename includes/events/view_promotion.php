<?php
function wc_ga4_view_promotion() {
    $options = get_option('wc_gtm_options');
    $current_user = wp_get_current_user();
    $hashed_email = '';
    $email = '';
    if ($current_user->exists()) {
        $hashed_email = hash('sha256', $current_user->user_email);
        $email = $current_user->user_email;
    }

    if (isset($options['view_promotion']) && $options['view_promotion']) {
        // Veronderstelt dat er een manier is om de getoonde promoties op te halen
        $promotions = []; // Hier moet een logica komen om promoties op te halen

        foreach ( $promotions as $promotion ) {
            $coupon = new WC_Coupon($promotion['coupon_code']);

            // Voeg logica toe om andere relevante informatie over de promotie te verzamelen indien nodig
            $promotion_data = [
                'item_id' => $coupon->get_id(),
                'item_name' => $coupon->get_code(),
                'coupon' => $coupon->get_code(),
                'discount' => $coupon->get_amount(),
            ];

            $promotion_items[] = $promotion_data;
        }

        ?>
        <script>
            window.dataLayer = window.dataLayer || [];
            dataLayer.push({
                'event': 'view_promotion',
                'ecommerce': {
                    'promotion_id': '<?php echo$coupon->get_id(); ?>, // Of een andere unieke identifier voor de promotie
                    'promotion_name': '<?php echo$coupon->get_code(); ?>, // De
                    'items': <?php echo json_encode($promotion_items); ?>
                },
                'user_data': {
                    'email_hashed': '<?php echo $hashed_email ?>',
                    'email': '<?php echo $email ?>'
                }
            });
        </script>
        <?php
    }
}

add_action('wp_footer', 'wc_ga4_view_promotion');
?>
