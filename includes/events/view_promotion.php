<?php
if (!defined('ABSPATH')) exit;

function tggr_view_promotion()
{
    $options = get_option('tggr_options');
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
        $promotion_items = [];

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
            $promotion_data = array(
                'item_id' => $promotion_id,
                'item_name' => $promotion_code,
                'coupon' => $promotion_code,
                'discount' => $promotion_amount,
            );

            $promotion_items[] = $promotion_data;
        }

        if (!empty($promotion_items)) {
            $view_promotion_data = array(
                'event' => 'view_promotion',
                'ecommerce' => array(
                    'promotion_id' => isset($promotion_items[0]['item_id']) ? $promotion_items[0]['item_id'] : '',
                    'promotion_name' => isset($promotion_items[0]['coupon']) ? $promotion_items[0]['coupon'] : '',
                    'items' => $promotion_items
                ),
                'user_data' => array(
                    'email_hashed' => $hashed_email,
                    'email' => $email
                )
            );

            tggr_add_ga4_event_data('ga4-view-promotion', 'ga4ViewPromotionData', $view_promotion_data);
        }
    }
}

add_action('wp_enqueue_scripts', 'tggr_view_promotion');
?>