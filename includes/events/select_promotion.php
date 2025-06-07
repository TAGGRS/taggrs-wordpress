<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function tggr_select_promotion_event()
{
    $options = get_option('tggr_options');
    if (!isset($options['select_promotion']) || !$options['select_promotion']) {
        return;
    }

    $current_user = wp_get_current_user();
    $email = $current_user->exists() ? $current_user->user_email : '';
    $hashed_email = $email ? tggr_hash_email($email) : '';
   
    $event_data = array(
        'event' => 'select_promotion',
        'ecommerce' => array(
            'item_list_id' => 'cart',
            'item_list_name' => 'Shopping Cart',
        ),
        'user_data' => array(
            'email_hashed' => $hashed_email,
            'email' => $email
        )
    );
   
    $cookie_value = base64_encode(wp_json_encode($event_data));
    setcookie('tggr_promotion_data', $cookie_value, time() + 300, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), false);
}
add_action('woocommerce_applied_coupon', 'tggr_select_promotion_event');


function tggr_print_promotion_script()
{
    $options = get_option('tggr_options');
    if (!isset($options['select_promotion']) || !$options['select_promotion']) {
        return;
    }
    ?>
    <script>
        jQuery(document).ready(function($) {
            function pushPromotionData() {
                var cookieValue = document.cookie.split('; ').find(row => row.startsWith('tggr_promotion_data='));
                if (cookieValue) {
                    try {
                        var data = JSON.parse(atob(decodeURIComponent(cookieValue.split('=')[1])));
                        window.dataLayer = window.dataLayer || [];
                        window.dataLayer.push(data);
                        
                        // Delete cookie after pushing data
                        document.cookie = "tggr_promotion_data=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=<?php echo COOKIEPATH; ?>; domain=<?php echo COOKIE_DOMAIN; ?>";
                    } catch(e) {
                        console.error('Promotion data error:', e);
                    }
                }
            }
            
            // Check on load (for non AJAX calls)
            pushPromotionData();
            
            // Check after updates (for AJAX calls)
            $(document.body).on('updated_wc_div applied_coupon fkcart_fragments_refreshed', function() {
                setTimeout(pushPromotionData, 500);
            });
        });
    </script>
    <?php
}
add_action('wp_footer', 'tggr_print_promotion_script');
?>