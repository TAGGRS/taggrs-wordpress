<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function tggr_options_page() {
    add_menu_page(
        'TAGGRS',    // Page title
        'TAGGRS',             // Menu title
        'manage_options',            // Capability
        'wc-gtm-settings',           // Menu slug
        'tggr_options_page_html',  // Callback function
        plugins_url('/images/wp-logo-taggrs.png', __FILE__),       // Icon URL (using a WordPress dashicon)
        25                           // Position
    );
}
add_action('admin_menu', 'tggr_options_page');

function tggr_admin_styles() {
    echo "
    <style>
        #adminmenu .toplevel_page_wc-gtm-settings img {
            padding-top: 6px;  /* Adjust as needed */
        }
    </style>
    ";
}
add_action('admin_head', 'tggr_admin_styles');

function tggr_enqueue_admin_styles() {
    // Ensure it's only loaded in the WordPress dashboard.
    if ( is_admin() ) {
        wp_enqueue_style( 'wc-gtm-admin-styles', plugins_url('/css/style.css', __FILE__), array(), '1.0.0' );
    }
}
add_action( 'admin_enqueue_scripts', 'tggr_enqueue_admin_styles' );


function tggr_options_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }

    settings_errors('tggr_messages');

    // Select tab, can be: gtm, events
    $active_tab = 'gtm'; // Default tab
    
    // Only process tab parameter if it's a valid value
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Simple tab switching in admin area
    if (isset($_GET['tab']) && in_array($_GET['tab'], ['gtm', 'events'], true)) {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tab parameter validation for admin UI
        $active_tab = sanitize_key($_GET['tab']);
    }
    
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
        <?php 
        $image_url = PLUGIN_PATH . 'includes/admin/images/taggrs-logo-blauw.png';
        // Plugin logo for admin interface - not using wp_get_attachment_image as this is a bundled asset
        // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage -- Plugin bundled logo asset
        // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
        echo wp_kses(
            sprintf(
                '<img src="%s" alt="%s" style="width: 250px; height: auto; margin-top: 25px; margin-bottom: 25px;" />',
                esc_url($image_url),
                esc_attr__('TAGGRS Logo', 'taggrs-datalayer')
            ),
            array(
                'img' => array(
                    'src' => array(),
                    'alt' => array(),
                    'style' => array(),
                )
            )
        );
        // phpcs:enable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
        ?>

        <div style="display: flex; justify-content: space-between;">
            <!-- Main Settings/Events Section -->
            <div style="flex: 70%; max-width: 70%; padding-right: 2%;">
                <div class="postbox">
                    <div class="inside">
                        <form action="options.php" method="post" id="taggrs-options-form">
                            <?php
                            settings_fields('tggr'); // This registers nonces etc. for the page

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


function tggr_get_defaults() {
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
        'tggr_url' => 'https://googletagmanager.com/'
    ];
}

add_filter('default_option_tggr_options', 'tggr_get_defaults');

function tggr_admin_scripts($hook) {
    if ('settings_page_wc-gtm-settings' != $hook) {
        return;
    }
    wp_enqueue_script('wc-gtm-admin', plugins_url('/admin.js', __FILE__), array('jquery'), '1.0.0', true);
}
add_action('admin_enqueue_scripts', 'tggr_admin_scripts');

function tggr_admin_notices() {
    if ($error = get_transient('tggr_settings_error')) {
        echo esc_html('<div class="error"><p>' . $error . '</p></div>');
        delete_transient('tggr_settings_error');  // Remove the error now that we've displayed it.
    }
}

add_action('admin_notices', 'tggr_admin_notices');

function tggr_code_sanitize($input) {
    if (!empty($input) && strpos($input, 'GTM-') !== 0) { // If the input doesn't start with 'GTM-'
        set_transient('tggr_settings_error', 'The GTM Code must start with "GTM-".', 45);
        return get_option('tggr_code'); // Return the old value
    }
    return sanitize_text_field($input);
}

function tggr_events_sanitize($input) {
    return $input;
}

function tggr_options_sanitize($input) {
    $input['tggr_url'] = 'https://googletagmanager.com/';

    $input['enhanced_tracking_v2'] = 0;
    $input['enhanced_tracking_v2_container_id'] = '';

    if (!empty($input['enhanced_tracking_v2']) && empty($input['enhanced_tracking_v2_container_id'])) {
        $input['enhanced_tracking_v2'] = 0;
    }

    return $input;
}


function tggr_success_message($old_value, $value, $option) {
    if ($old_value !== $value) { // Only show if the value has changed.
        add_settings_error('tggr_messages', 'tggr_message', 'Settings saved', 'updated');
    }
}
add_action('update_option_tggr_code', 'tggr_success_message', 10, 3);
add_action('update_option_tggr_url', 'tggr_success_message', 10, 3);
add_action('update_option_tggr_options', 'tggr_success_message', 10, 3);

function tggr_section_gtm_cb($args) {
    echo esc_html('Enter your Google Tag Manager settings below:');
}

function tggr_code_cb($args) {
    $gtm_code = get_option('tggr_code');
    echo '<input name="tggr_code" id="tggr_code" type="text" value="' . esc_attr($gtm_code) . '" class="regular-text">';
    echo '<p class="description">You can fill in your Google Tag Manager web container ID</p>';
}

function tggr_url_cb($args) {
    $gtm_url = get_option('tggr_url');
    echo ( '<input name="tggr_url" id="tggr_url" type="text" value="' . esc_attr($gtm_url) . '" class="regular-text">' );
}

function tggr_section_callback($args) {
    echo ( '<p class="description"><i>You have the option to select the events of your choice, and all events include the transmission of enhanced conversions.</i></p>' );
    echo ( '<p class="description">Enable or disable the following events:</p>' );
}

function tggr_field_callback($args) {
    $options = get_option('tggr_options');
    $checked = isset($options[$args['event_name']]) ? checked($options[$args['event_name']], 1, false) : '';
    echo ("<input name='tggr_options[" . esc_attr($args['event_name']). "]' type='checkbox' id='" . esc_attr($args['label_for']) . "' value='1'  " . esc_attr($checked) . ">");
}

function tggr_url_toggle_cb() {
    $options = get_option('tggr_options', array());
    $value = isset($options['tggr_url_toggle']) ? $options['tggr_url_toggle'] : '';
    echo ( '<input type="text" id="tggr_url_toggle" name="tggr_options[tggr_url_toggle]" style="width:350px; " value="' . esc_attr($value) . '" />' );
    echo ( '<p class="description">Read <a href="https://taggrs.io/en/enhanced-tracking-script/">this article</a> to find out how to use the Enhanced Tracking Script</p>' );
    echo ( '<p class="description"><i>If you do not want to use the Enhanced Tracking Script, leave this field empty</i></p>' );
}

function tggr_enhanced_tracking_v2_cb($args) {
    $options = get_option('tggr_options');
    $disabled = !isset($options['tggr_url_toggle']) || $options['tggr_url_toggle'] == '';
    $v2_active = isset($options['enhanced_tracking_v2']) ? checked($options['enhanced_tracking_v2'], 1, false) : '';
    $container_id = isset($options['enhanced_tracking_v2_container_id']) ? $options['enhanced_tracking_v2_container_id'] : '';

    echo '<div id="enhanced_tracking_v2_section" style="' . ($disabled ? 'opacity: 0.7;' : '') . '">';
    
    // Toggle
    echo "<div style='display:flex; gap: 6px;'>";
    echo ("<input style='margin-top: 7px;' name='tggr_options[enhanced_tracking_v2]' " . ($disabled ? 'disabled' : '') . " type='checkbox' id='tggr_enhanced_tracking_v2' value='1' " . esc_attr($v2_active) . ">");
    echo '<p class="description"><b>Enable</b></p>';
    echo "</div>";

    // TAGGRS Container Identifier
    echo '<p class="description" style="margin-top: 10px;"><b>TAGGRS Container Identifier</b></p>';
    echo '<input type="text" id="enhanced_tracking_v2_container_id" name="tggr_options[enhanced_tracking_v2_container_id]" ' . ($disabled ? 'disabled' : "") . ' style="width:350px;" value="' . esc_attr($container_id) . '" />';
    
    echo '</div>';
    echo '<p class="description"><i>The Enhanced Tracking Script v2 can only be used when you have entered a subdomain for the Enhanced Tracking Script.</i></p>';
}


function tggr_settings_init() {
    // Register the GTM code setting.
    register_setting('tggr', 'tggr_code', array('sanitize_callback' => 'tggr_code_sanitize'));

    // Add section for GTM
    add_settings_section(
        'tggr_section_gtm',
        'Google Tag Manager Settings',
        'tggr_section_gtm_cb',
        'wc-gtm-settings'
    );

    // Add field to input GTM code
    add_settings_field(
        'tggr_code',
        'Google Tag Manager Code',
        'tggr_code_cb',
        'wc-gtm-settings',
        'tggr_section_gtm'
    );

    register_setting('tggr', 'tggr_url', array('sanitize_callback' => 'tggr_options_sanitize'));

    // Add field to input GTM url
    add_settings_field(
        'tggr_url',
        'Subdomain for Enhanced Tracking Script',
        'tggr_url_toggle_cb',
        'wc-gtm-settings',
        'tggr_section_gtm'
    );

    // Add field for Enhanced Tracking Script v2
    add_settings_field(
        'tggr_enhanced_tracking_v2',
        'Enhanced Tracking Script v2',
        'tggr_enhanced_tracking_v2_cb',
        'wc-gtm-settings',
        'tggr_section_gtm'
    );

    // Register a new setting for our options page for the events.
    register_setting('tggr', 'tggr_options', array('sanitize_callback' => 'tggr_options_sanitize'));


    // Add a new section to our options page for the events.
    add_settings_section(
        'tggr_section_events',       // Changed this to make it more descriptive
        'Events',
        'tggr_section_callback',
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
            'tggr_field_' . $event,
            ucfirst(str_replace('_', ' ', $event)),
            'tggr_field_callback',
            'wc-gtm-settings-events',   // This matches the slug above for the events section
            'tggr_section_events',    // Use the updated section ID here
            [
                'label_for' => 'tggr_field_' . $event,
                'event_name' => $event
            ]
        );
    }
}


add_action('admin_init', 'tggr_settings_init');


?>