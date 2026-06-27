<?php
if (! defined('ABSPATH')) exit;

function tggr_inject_gtm_script()
{
    $gtm_code = get_option('tggr_code', '');

    if (empty($gtm_code)) {
        return;
    }

    $gtm_options = get_option('tggr_options', array());

    if (!is_array($gtm_options) || !isset($gtm_options['tggr_url_toggle']) || $gtm_options['tggr_url_toggle'] == '') {
        $gtm_url = 'googletagmanager.com';
    } else {
        $gtm_url = $gtm_options['tggr_url_toggle'];
    }

    if (!preg_match('/^https?:\/\//', $gtm_url)) {
        $gtm_url = 'https://' . $gtm_url;
    }
    $gtm_url = rtrim($gtm_url, '/');

    $parameter = "id";

    // Enhanced tracking: subdomain + container_id ingevuld = enhanced mode
    $container_id = isset($gtm_options['enhanced_tracking_container_id']) ? $gtm_options['enhanced_tracking_container_id'] : '';
    $using_custom_subdomain = isset($gtm_options['tggr_url_toggle']) && $gtm_options['tggr_url_toggle'] != '';

    if ($using_custom_subdomain && !empty($container_id)) {
        $gtm_url = $gtm_url . "/$container_id.js";
        $gtm_code = str_replace('GTM-', '', $gtm_code);
        $parameter = "tg";
    } else {
        $gtm_url = $gtm_url . '/gtm.js';
    }

    echo "<!-- Server Side Tagging by TAGGRS v" . TGGR_VERSION . " -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    '" . esc_js($gtm_url) . "?" . esc_js($parameter) . "='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','" . esc_js($gtm_code) . "');</script>
    <!-- End Server Side Tagging by TAGGRS -->";
}
add_action('wp_head', 'tggr_inject_gtm_script');


function tggr_inject_gtm_noscript()
{
    $gtm_code = get_option('tggr_code', '');

    if (empty($gtm_code)) {
        return;
    }

    $gtm_options = get_option('tggr_options', array());
    if (!is_array($gtm_options) || !isset($gtm_options['tggr_url_toggle']) || $gtm_options['tggr_url_toggle'] == '') {
        $gtm_url = 'googletagmanager.com';
    } else {
        $gtm_url = $gtm_options['tggr_url_toggle'];
    }

    if (!preg_match('/^https?:\/\//', $gtm_url)) {
        $gtm_url = 'https://' . $gtm_url;
    }
    $gtm_url = rtrim($gtm_url, '/');

    $parameter = "id";

    $container_id = isset($gtm_options['enhanced_tracking_container_id']) ? $gtm_options['enhanced_tracking_container_id'] : '';
    $using_custom_subdomain = isset($gtm_options['tggr_url_toggle']) && $gtm_options['tggr_url_toggle'] != '';

    if ($using_custom_subdomain && !empty($container_id)) {
        $gtm_url = $gtm_url . "/$container_id.html";
        $gtm_code = str_replace('GTM-', '', $gtm_code);
        $parameter = "tg";
    } else {
        $gtm_url = $gtm_url . '/ns.html';
    }

    echo  "<!-- Server Side Tagging by TAGGRS v" . TGGR_VERSION . " (noscript) -->
    <noscript><iframe src='" . esc_js($gtm_url) . "?" . esc_js($parameter) . "=" . esc_js($gtm_code) . "'
                      height='0' width='0' style='display:none;visibility:hidden'></iframe></noscript>
    <!-- End Server Side Tagging by TAGGRS (noscript) -->";
}
add_action('wp_body_open', 'tggr_inject_gtm_noscript');
