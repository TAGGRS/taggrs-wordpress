<?php
// Function to output the content of the widget
function wc_gtm_dashboard_widget_content() {
    // Fetch the container data
    $id = 'd6c41dc2-69f5-49d4-a510-cbe5cadad499';
    $bearer_token = '1|hUgtpWxPz17M0WC023NlLZhmM5EMGnaTKFsw70nr';

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

    // Output the content for the dashboard widget
    ?>
    <div class="postbox">
        <div class="inside">
            <p><strong>Containername:</strong> <?php echo esc_js($container_name); ?></p>
            <p><strong>Tagging URL:</strong> <?php echo esc_html($tagging_url); ?></p>
            <p><strong>Plan:</strong> <span class="wc-gtm-plan-badge-<?php echo esc_js($plan_number); ?>"><?php echo esc_js($plan); ?></span></p>
            <p><strong>Request Limit:</strong></p>
            <div style="background-color: #f5f5f5; border: 1px solid #ccc; height: 20px; width: 100%; position: relative;">
                <div style="width: <?php echo esc_js($percentage); ?>%; background-color: #0073aa; height: 100%;"></div>
                <span style="position: absolute; top: 0; left: 50%; transform: translateX(-50%); color: #555;"><?php echo esc_js($requests); ?> / <?php echo esc_js($request_limit); ?></span>
            </div>
        </div>
    </div>
    <?php
}

// Function to add the widget to the dashboard
function wc_gtm_add_dashboard_widgets() {
    wp_add_dashboard_widget(
        'wc_gtm_dashboard_widget',               // Widget slug
        'TAGGRS Container Details',              // Title
        'wc_gtm_dashboard_widget_content'        // Display function
    );
}
add_action('wp_dashboard_setup', 'wc_gtm_add_dashboard_widgets');
?>