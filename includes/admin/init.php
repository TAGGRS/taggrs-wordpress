<?php

function wc_gtm_options_page() {
    add_menu_page(
        'TAGGRS',    // Page title
        'TAGGRS',             // Menu title
        'manage_options',            // Capability
        'wc-gtm-settings',           // Menu slug
        'wc_gtm_options_page_html',  // Callback function
        'https://i.ibb.co/6b4yhy9/wp-logo-taggrs.png',       // Icon URL (using a WordPress dashicon)
        25                           // Position
    );
}
add_action('admin_menu', 'wc_gtm_options_page');

function wc_gtm_admin_styles() {
    echo esc_html( "
    <style>
        #adminmenu .toplevel_page_wc-gtm-settings img {
            padding-top: 6px;  /* Adjust as needed */
        }
    </style>
    ");
}
add_action('admin_head', 'wc_gtm_admin_styles');

function wc_gtm_enqueue_admin_styles() {
    // Ensure it's only loaded in the WordPress dashboard.
    if ( is_admin() ) {
        wp_enqueue_style( 'wc-gtm-admin-styles', plugins_url('/css/style.css', __FILE__), array(), '1.0.0' );
    }
}
add_action( 'admin_enqueue_scripts', 'wc_gtm_enqueue_admin_styles' );


function wc_gtm_options_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }

    settings_errors('wc_gtm_messages');

    // Container details for the new block
    $id = '9';  // Replace with your actual container ID or fetch it dynamically
    $bearer_token = '9';  // This should ideally be kept secret, stored securely, or fetched dynamically

    $data = fetch_container_data($id, $bearer_token);

    if ($data) {
        $container_name = $data['custom_name'];
        $requests = $data['requests'];
        $request_limit = get_request_limit($data['plan']);
        $tagging_url = $data['domain'];
        $plan = get_plan_name($data['plan']);
        $plan_number = $data['plan'];
        $percentage = ($requests / $request_limit) * 100;
    }

    // Select tab, can be: gtm, events
    $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'gtm';
    switch($active_tab):
        case 'gtm':
        case 'events':
            break;
        default:
            $active_tab = 'gtm';
        endswitch;

    ?>
    <style>
        .custom-container {
            margin: 0;
            text-align: center;
        }

        .custom-heading {
            font-weight: 600;
            color: #000;
            line-height: 1.5;
            font-size: 18px;
            margin-bottom: 36px; /* 9 * 4px */
        }

        .custom-heading span {
            display: block;
        }

        .custom-heading .bolder {
            font-weight: 700;
        }

        .custom-heading .smaller {
            font-size: 15px;
        }

        .custom-paragraph {
            text-align: center;
        }

        .custom-button {
            display: inline-block;
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.2rem;
            text-decoration: none;
            margin: 4px;
        }

        .btn-primary {
            color: #fff;
            background-color: #299f15;
            border-color: #299f15;
        }

        .btn-secondary {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
    <div class="wrap">
        <?php $image_url = PLUGIN_PATH . 'includes/admin/images/taggrs-logo-blauw.png'; ?>
        <img src="<?php echo esc_url($image_url)  ?>" style="width: 250px; height: auto; margin-top: 25px; margin-bottom: 25px;"></img>
        <h2 class="nav-tab-wrapper">
            <a href="?page=wc-gtm-settings&tab=gtm" class="nav-tab <?php $active_tab == 'gtm' ? 'nav-tab-active' : ''; ?>">GTM Settings</a>
            <a href="?page=wc-gtm-settings&tab=events" class="nav-tab <?php $active_tab == 'events' ? 'nav-tab-active' : ''; ?>">Events</a>
        </h2>

        <div style="display: flex; justify-content: space-between;">
            <!-- Main Settings/Events Section -->
            <div style="flex: 70%; max-width: 70%; padding-right: 2%;">
                <div class="postbox">
                    <div class="inside">
                        <form action="options.php" method="post">
                            <?php
                            settings_fields('wc_gtm'); // This registers nonces etc. for the page

                            // Display both settings, but use a PHP condition to hide the non-active section
                            ?>
                            <div <?php $active_tab == 'gtm' ? '' : 'style="display:none"'; ?>>
                                <?php do_settings_sections('wc-gtm-settings'); ?>
                            </div>
                            <div <?php $active_tab == 'events' ? '' : 'style="display:none"'; ?>>
                                <?php do_settings_sections('wc-gtm-settings-events'); ?>
                            </div>
                            <?php
                            submit_button('Save Changes');
                            ?>
                        </form>
                    </div>
                </div>
            </div>
            <div style="flex: 28%; max-width: 28%; height: 100%;">
                 <div class="postbox">
                     <div class="custom-container">
                         <h1 class="custom-heading">
                             <span class="bolder">Welcome in the world of TAGGRS!</span>
                             <span class="smaller">Check out Server Side Tracking</span>
                         </h1>
                         <p class="custom-paragraph">Enhance your website's performance and data privacy with server side tracking through Google Tag Manager. Discover the ease and efficiency of managing your tags on TAGGRS' reliable and user-friendly platform. Start optimizing your tagging strategy today!</p>
                         <div>
                             <a href="https://taggrs.io/" class="custom-button btn-primary" target="_blank">Check out TAGGRS</a>
                         </div>
                     </div>
                 </div>
            </div>
        </div>
    </div>
    <?php
}


function wc_gtm_get_defaults() {
    return [
        'view_item' => 1,
        'add_to_cart' => 1,
        'purchase' => 1,
        'view_item_list' => 1,
        'begin_checkout' => 1,
        'view_cart' => 1,
        'refund' => 1,
        'add_to_wishlist' => 1,
        'add_payment_info' => 1,
        'add_shipping_info' => 1,
        'remove_from_cart' => 1,
        'select_item' => 1,
        'view_promotion' => 1,
        'select_promotion' => 1,
        'wc_gtm_url' => 'https://googletagmanager.com/'
    ];
}

add_filter('default_option_wc_gtm_options', 'wc_gtm_get_defaults');

function wc_gtm_admin_scripts($hook) {
    if ('settings_page_wc-gtm-settings' != $hook) {
        return;
    }
    wp_enqueue_script('wc-gtm-admin', plugins_url('/js/admin.js', __FILE__), array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'wc_gtm_admin_scripts');


function fetch_container_data($id, $bearer_token) {
    $url = 'https://dev.taggrs.io/api/v1/integration/containerinfo/' . $id;

    $response = wp_remote_get($url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $bearer_token
        )
    ));

    if (is_wp_error($response)) {
        // Handle the error accordingly
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    return $data;
}

function get_plan_name($plan_number) {
    $plans = array(
        0 => 'Free',
        1 => 'Basic',
        2 => 'Pro+',
        3 => 'Ultimate'
    );

    return isset($plans[$plan_number]) ? $plans[$plan_number] : 'Unknown';
}

function get_request_limit($plan_number) {
    $plans = array(
        0 => 10000,
        1 => 750000,
        2 => 3000000,
        3 => 10000000
    );

    return isset($plans[$plan_number]) ? $plans[$plan_number] : 'Unknown';
}

function wc_gtm_admin_notices() {
    if ($error = get_transient('wc_gtm_settings_error')) {
        echo esc_html('<div class="error"><p>' . $error . '</p></div>');
        delete_transient('wc_gtm_settings_error');  // Remove the error now that we've displayed it.
    }
}

add_action('admin_notices', 'wc_gtm_admin_notices');

function wc_gtm_code_sanitize($input) {
    if (strpos($input, 'GTM-') !== 0) { // If the input doesn't start with 'GTM-'
        set_transient('wc_gtm_settings_error', 'The GTM Code must start with "GTM-".', 45);
        return get_option('wc_gtm_code'); // Return the old value
    }
    return sanitize_text_field($input);
}

function wc_gtm_events_sanitize($input) {

    return $input;
}

function wc_gtm_options_sanitize($input) {
    if (isset($input['wc_gtm_url_toggle']) && $input['wc_gtm_url_toggle'] == 'on') {
        $id = '9';  // Fetch the ID from wherever you have it.
        $bearer_token = '9';  // Again, fetch this securely.
        $data = fetch_container_data($id, $bearer_token);

        if ($data && isset($data['domain'])) {
            $input['wc_gtm_url'] = 'https://' . $data['domain'] . '/';
        }
    } else {
        $input['wc_gtm_url'] = 'https://googletagmanager.com/';
    }

    return $input;
}


function wc_gtm_success_message($old_value, $value, $option) {
    if ($old_value !== $value) { // Only show if the value has changed.
        add_settings_error('wc_gtm_messages', 'wc_gtm_message', 'Settings Saved', 'updated');
    }
}
add_action('update_option_wc_gtm_code', 'wc_gtm_success_message', 10, 3);
add_action('update_option_wc_gtm_url', 'wc_gtm_success_message', 10, 3);
add_action('update_option_wc_gtm_options', 'wc_gtm_success_message', 10, 3);

function wc_gtm_section_gtm_cb($args) {
    echo esc_html('Enter your Google Tag Manager settings below:');
}

function wc_gtm_code_cb($args) {
    $gtm_code = get_option('wc_gtm_code');
    echo esc_html( '<input name="wc_gtm_code" id="wc_gtm_code" type="text" value="' . esc_attr($gtm_code) . '" class="regular-text">' );
    echo esc_html( '<p class="description">You can fill in your Google Tag Manager web container ID</p>' );
}

function wc_gtm_url_cb($args) {
    $gtm_url = get_option('wc_gtm_url');
    echo esc_html( '<input name="wc_gtm_url" id="wc_gtm_url" type="text" value="' . esc_attr($gtm_url) . '" class="regular-text">' );
}

function wc_gtm_section_callback($args) {
    echo esc_html( '<p class="description"><i>You have the option to select the events of your choice, and all events include the transmission of enhanced conversions.</i></p>' );
    echo esc_html( '<p class="description">Enable or disable the following events:</p>' );
}

function wc_gtm_field_callback($args) {
    $options = get_option('wc_gtm_options');
    $checked = isset($options[$args['event_name']]) ? checked($options[$args['event_name']], 1, false) : '';
    echo esc_html("<input name='wc_gtm_options[{$args['event_name']}]' type='checkbox' id='{$args['label_for']}' value='1'  {$checked}>");
}

function wc_gtm_url_toggle_cb() {
    $options = get_option('wc_gtm_options', array());
    $value = isset($options['wc_gtm_url_toggle']) ? $options['wc_gtm_url_toggle'] : '';
    echo esc_html( '<input type="text" id="wc_gtm_url_toggle" name="wc_gtm_options[wc_gtm_url_toggle]" style="width:350px; " value="' . esc_attr($value) . '" />' );
    echo esc_html( '<p class="description">Read <a href="https://taggrs.io/en/enhanced-tracking-script/">this article</a> to find out how to use the Enhanced Tracking Script</p>' );
    echo esc_html( '<p class="description"><i>If you do not want to use the Enhanced Tracking Script, leave this field empty</i></p>' );

}


function wc_gtm_settings_init() {
    // Register the GTM code setting.
    register_setting('wc_gtm', 'wc_gtm_code', array('sanitize_callback' => 'wc_gtm_code_sanitize'));

    // Add section for GTM
    add_settings_section(
        'wc_gtm_section_gtm',
        'Google Tag Manager Settings',
        'wc_gtm_section_gtm_cb',
        'wc-gtm-settings'
    );

    // Add field to input GTM code
    add_settings_field(
        'wc_gtm_code',
        'Google Tag Manager Code',
        'wc_gtm_code_cb',
        'wc-gtm-settings',
        'wc_gtm_section_gtm'
    );

    register_setting('wc_gtm', 'wc_gtm_url', array('sanitize_callback' => 'wc_gtm_options_sanitize'));

    // Add field to input GTM url
    add_settings_field(
        'wc_gtm_url',
        'Subdomain for Enhanced Tracking Script',
        'wc_gtm_url_toggle_cb',
        'wc-gtm-settings',
        'wc_gtm_section_gtm'
    );


    // Register a new setting for our options page for the events.
    register_setting('wc_gtm', 'wc_gtm_options');


    // Add a new section to our options page for the events.
    add_settings_section(
        'wc_gtm_section_events',       // Changed this to make it more descriptive
        'Events',
        'wc_gtm_section_callback',
        'wc-gtm-settings-events'      // Different slug for the events page
    );

    // Add fields to our section.
    $events = [
        'view_item',
        'add_to_cart',
        'purchase',
        'view_cart',
        'view_item_list',
        'begin_checkout',
        'refund',
        'add_to_wishlist',
        'add_payment_info',
        'add_shipping_info',
        'remove_from_cart',
        'select_item',
        'view_promotion',
        'select_promotion'
    ];

    foreach ($events as $event) {
        add_settings_field(
            'wc_gtm_field_' . $event,
            ucfirst(str_replace('_', ' ', $event)),
            'wc_gtm_field_callback',
            'wc-gtm-settings-events',   // This matches the slug above for the events section
            'wc_gtm_section_events',    // Use the updated section ID here
            [
                'label_for' => 'wc_gtm_field_' . $event,
                'event_name' => $event
            ]
        );
    }
}


add_action('admin_init', 'wc_gtm_settings_init');


?>