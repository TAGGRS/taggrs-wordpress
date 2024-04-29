<?php
if (!defined('ABSPATH')) exit;

function tggr_view_promotion()
{
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

        foreach ($promotions as $promotion) {
            $coupon = new WC_Coupon($promotion['coupon_code']);

            $promotion_id = '';
            $promotion_code = '';
            $promotion_amount = '';

            if ($coupon !== null && method_exists($coupon, 'get_id')) {
                $promotion_id = $coupon->get_id();
            }
            if ($coupon !== null && method_exists($coupon, 'get_code')) {
                $promotion_code = $coupon->get_code();
            }
            if ($coupon !== null && method_exists($coupon, 'get_amount')) {
                $promotion_amount = $coupon->get_amount();
            }
            // Voeg logica toe om andere relevante informatie over de promotie te verzamelen indien nodig
            $promotion_data = [
                'item_id' => $promotion_id,
                'item_name' => $promotion_code,
                'coupon' => $promotion_code,
                'discount' => $promotion_amount,
            ];

            $promotion_items[] = $promotion_data;
        }

?>

        <?php
        foreach ($promotions as $promotion) {
            $promotion_id = $promotion['item_id'];
            $promotion_id = $promotion['item_name'];
            $promotion_code = $promotion['coupon'];
            $promotion_items = [];
        ?>
            <script>
                var_dump($promotion_id);
                window.dataLayer = window.dataLayer || [];
                dataLayer.push({
                    'event': 'view_promotion',
                    'ecommerce': {
                        'promotion_id': '<?php echo esc_js($promotion_id); ?>',
                        'promotion_name': '<?php echo esc_js($promotion_code); ?>',
                        'items': <?php echo wp_json_encode($promotion_items); ?>
                    },
                    'user_data': {
                        'email_hashed': '<?php echo esc_js($hashed_email); ?>',
                        'email': '<?php echo esc_js($email); ?>'
                    }
                });
            </script>

<?php
        }
    }
}

add_action('wp_footer', 'tggr_view_promotion');
?>