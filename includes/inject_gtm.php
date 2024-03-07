<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// All datalayer injections
function tggr_inject_gtm_script()
{
    $gtm_code = get_option('wc_gtm_code', '');
    $gtm_options = get_option('wc_gtm_options', array());

    // Check if the option isn't an array or if it doesn't contain the expected key.
    if (!is_array($gtm_options) || !isset($gtm_options['wc_gtm_url_toggle']) || $gtm_options['wc_gtm_url_toggle'] == '') {
        $gtm_url = 'googletagmanager.com'; // Default value
    } else {
        $gtm_url = $gtm_options['wc_gtm_url_toggle'];
    }

    //    if($gtm_url == 'on'){
    //        $id = 'd6c41dc2-69f5-49d4-a510-cbe5cadad499';  // Fetch the ID from wherever you have it.
    //        $bearer_token = '1|hUgtpWxPz17M0WC023NlLZhmM5EMGnaTKFsw70nr';  // Again, fetch this securely.
    //        $data = fetch_container_data($id, $bearer_token);
    echo "<!-- Server Side Tagging by TAGGRS -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://" . esc_js($gtm_url) . "/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','" . esc_js($gtm_code) . "');</script>
    <!-- End Server Side Tagging by TAGGRS -->";
    //    } else if (!empty($gtm_code) && !empty($gtm_url)) {
    //        echo "<!-- Server Side Tagging by TAGGRS -->
    //    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    //    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    //    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    //    'https://googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    //    })(window,document,'script','dataLayer','" . esc_js($gtm_code) . "');</script>
    //    <!-- End Server Side Tagging by TAGGRS -->";
    //    }
}
add_action('wp_head', 'tggr_inject_gtm_script');


function tggr_inject_gtm_noscript()
{
    $gtm_code = get_option('wc_gtm_code', '');
    $gtm_options = get_option('wc_gtm_options', array());
    if (!is_array($gtm_options) || !isset($gtm_options['wc_gtm_url_toggle']) || $gtm_options['wc_gtm_url_toggle'] == '') {
        $gtm_url = 'googletagmanager.com'; // Default value
    } else {
        $gtm_url = $gtm_options['wc_gtm_url_toggle'];
    }


    //    if($gtm_url == 'on'){
    //        $id = 'd6c41dc2-69f5-49d4-a510-cbe5cadad499';  // Fetch the ID from wherever you have it.
    //        $bearer_token = '1|hUgtpWxPz17M0WC023NlLZhmM5EMGnaTKFsw70nr';  // Again, fetch this securely.
    //        $data = fetch_container_data($id, $bearer_token);
    echo  "<!-- Server Side Tagging by TAGGRS (noscript) -->
    <noscript><iframe src='https://" . esc_js($gtm_url) . "/ns.html?id=" . esc_js($gtm_code) . "'
                      height='0' width='0' style='display:none;visibility:hidden'></iframe></noscript>
    <!-- End Server Side Tagging by TAGGRS (noscript) -->";
    //    } else if (!empty($gtm_code) && !empty($gtm_url)) {
    //        echo "<!-- Server Side Tagging by TAGGRS (noscript) -->
    //    <noscript><iframe src='https://googletagmanager.com/ns.html?id=" . esc_js($gtm_code) . "'
    //                      height='0' width='0' style='display:none;visibility:hidden'></iframe></noscript>
    //    <!-- End Server Side Tagging by TAGGRS (noscript) -->";
    //    }
}
// If your theme supports the 'wp_body_open' action (introduced in WP 5.2), you can use that.
add_action('wp_body_open', 'tggr_inject_gtm_noscript');
