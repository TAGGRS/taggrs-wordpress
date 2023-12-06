<?php
/*
Plugin Name: TAGGRS - Server Side Tracking
Description: This integration introduces a Google Analytics 4 (GA4) data layer with server-side tracking capabilities into WooCommerce. It enhances customer interaction tracking by combining traditional client-side events with server-side data collection. This approach offers more reliable analytics, improved privacy compliance, and a comprehensive understanding of user behavior and e-commerce performance in your WooCommerce store.
Version: 1.1.2
Author: TAGGRS BV
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function ga4_enqueue_jquery() {
    wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'ga4_enqueue_jquery');


// Widgets
require_once plugin_dir_path(__FILE__) . 'includes/widgets/taggrs_container_stats.php';

// admin Page
require_once plugin_dir_path(__FILE__) . 'includes/admin/init.php';

// inject gtm codes
require_once plugin_dir_path(__FILE__) . 'includes/inject_gtm.php';

// load events
require_once plugin_dir_path(__FILE__) . 'includes/events/view_item.php';
require_once plugin_dir_path(__FILE__) . 'includes/events/add_to_cart.php';
require_once plugin_dir_path(__FILE__) . 'includes/events/view_cart.php';
require_once plugin_dir_path(__FILE__) . 'includes/events/begin_checkout.php';
require_once plugin_dir_path(__FILE__) . 'includes/events/purchase.php';
require_once plugin_dir_path(__FILE__) . 'includes/events/view_item_list.php';
require_once plugin_dir_path(__FILE__) . 'includes/events/refund.php';
require_once plugin_dir_path(__FILE__) . 'includes/events/add_to_wishlist.php';
require_once plugin_dir_path(__FILE__) . 'includes/events/add_shipping_info.php';
require_once plugin_dir_path(__FILE__) . 'includes/events/remove_from_cart.php';
require_once plugin_dir_path(__FILE__) . 'includes/events/select_item.php';
require_once plugin_dir_path(__FILE__) . 'includes/events/view_promotion.php';
require_once plugin_dir_path(__FILE__) . 'includes/events/select_promotion.php';













