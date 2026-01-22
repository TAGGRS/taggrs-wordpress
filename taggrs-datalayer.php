<?php
/*
Plugin Name: TAGGRS - Server Side Tracking
Description: This integration introduces a Google Analytics 4 (GA4) data layer with server-side tracking capabilities into WooCommerce. It enhances customer interaction tracking by combining traditional client-side events with server-side data collection. This approach offers more reliable analytics, improved privacy compliance, and a comprehensive understanding of user behavior and e-commerce performance in your WooCommerce store.
Text Domain: taggrs-datalayer
Version: 1.1.9
Author: TAGGRS BV 
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function tggr_enqueue_jquery() {
    wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'tggr_enqueue_jquery');

// Constant plugin path
define('PLUGIN_PATH', plugin_dir_url( __FILE__ ));

// admin Page
require_once plugin_dir_path(__FILE__) . 'includes/admin/init.php';

// inject gtm codes
require_once plugin_dir_path(__FILE__) . 'includes/inject_gtm.php';

// load functions
require_once plugin_dir_path(__FILE__) . 'includes/functions.php';

// load updater
require_once plugin_dir_path(__FILE__) . 'includes/updater.php';

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













