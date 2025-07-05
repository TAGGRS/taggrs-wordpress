<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// All datalayer injections
function tggr_inject_gtm_script()
{
    $gtm_code = get_option('tggr_code', '');
    
    if (empty($gtm_code)) {
        return;
    }

    $gtm_options = get_option('tggr_options', array());

    // Check if the option isn't an array or if it doesn't contain the expected key.
    if (!is_array($gtm_options) || !isset($gtm_options['tggr_url_toggle']) || $gtm_options['tggr_url_toggle'] == '') {
        $gtm_url = 'googletagmanager.com'; // Default value
    } else {
        $gtm_url = $gtm_options['tggr_url_toggle'];
    }

    if (!preg_match('/^https?:\/\//', $gtm_url)) {
        $gtm_url = 'https://' . $gtm_url;
    }
    $gtm_url = rtrim($gtm_url, '/');

    $parameter = "id";
    if (isset($gtm_options['tggr_enhanced_tracking_v2']) && $gtm_options['tggr_enhanced_tracking_v2']) {
        $container_id = $gtm_options['tggr_enhanced_tracking_v2_container_id'];
        $gtm_url = $gtm_url . "/$container_id.js";
        $gtm_code = str_replace('GTM-', '', $gtm_code);
        $parameter = "tg";
    } else {
        $gtm_url = $gtm_url . '/gtm.js';
    }

    // Use wp_enqueue_script instead of direct script output
    wp_register_script('tggr-gtm-script', false, array(), TGGR_VERSION, false);
    wp_enqueue_script('tggr-gtm-script');
    
    $gtm_script = "(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    '" . esc_js($gtm_url) . "?" . esc_js($parameter) . "='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','" . esc_js($gtm_code) . "');";
    
    wp_add_inline_script('tggr-gtm-script', $gtm_script, 'after');
}
add_action('wp_enqueue_scripts', 'tggr_inject_gtm_script');


function tggr_inject_gtm_noscript()
{
    $gtm_code = get_option('tggr_code', '');

    if (empty($gtm_code)) {
        return;
    }

    $gtm_options = get_option('tggr_options', array());
    if (!is_array($gtm_options) || !isset($gtm_options['tggr_url_toggle']) || $gtm_options['tggr_url_toggle'] == '') {
        $gtm_url = 'googletagmanager.com'; // Default value
    } else {
        $gtm_url = $gtm_options['tggr_url_toggle'];
    }

    if (!preg_match('/^https?:\/\//', $gtm_url)) {
        $gtm_url = 'https://' . $gtm_url;
    }
    $gtm_url = rtrim($gtm_url, '/');

    $parameter = "id";
    if (isset($gtm_options['tggr_enhanced_tracking_v2']) && $gtm_options['tggr_enhanced_tracking_v2']) {
        $container_id = $gtm_options['tggr_enhanced_tracking_v2_container_id'];
        $gtm_url = $gtm_url . "/$container_id.html";
        $gtm_code = str_replace('GTM-', '', $gtm_code);
        $parameter = "tg";
    } else {
        $gtm_url = $gtm_url . '/ns.html';
    }

    // Output noscript iframe for GTM - this is required HTML output, not enqueueable content
    echo  "<!-- Server Side Tagging by TAGGRS (noscript) -->
    <noscript><iframe src='" . esc_url($gtm_url . "?" . $parameter . "=" . $gtm_code) . "'
                      height='0' width='0' style='display:none;visibility:hidden'></iframe></noscript>
    <!-- End Server Side Tagging by TAGGRS (noscript) -->";
}
// If your theme supports the 'wp_body_open' action (introduced in WP 5.2), you can use that.
add_action('wp_body_open', 'tggr_inject_gtm_noscript');
