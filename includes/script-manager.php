<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * TAGGRS Script Manager
 * Handles all GA4 script enqueuing and management
 */
class TGGR_Script_Manager {
    
    private static $instance = null;
    private $event_scripts = array();
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_footer', array($this, 'print_scripts'));
    }
    
    /**
     * Define all available event scripts
     */
    private function get_event_scripts() {
        return array(
            'view_item' => 'ga4-view-item',
            'add_to_cart' => 'ga4-add-to-cart', 
            'view_cart' => 'ga4-view-cart',
            'purchase' => 'ga4-purchase',
            'view_item_list' => 'ga4-view-item-list',
            'begin_checkout' => 'ga4-begin-checkout',
            'remove_from_cart' => 'ga4-remove-from-cart',
            'add_to_wishlist' => 'ga4-add-to-wishlist',
            'add_payment_info' => 'ga4-add-payment-info',
            'add_shipping_info' => 'ga4-add-shipping-info',
            'select_item' => 'ga4-select-item',
            'view_promotion' => 'ga4-view-promotion',
            'select_promotion' => 'ga4-select-promotion',
            'refund' => 'ga4-refund'
        );
    }
    
    /**
     * Enqueue scripts based on active events
     */
    public function enqueue_scripts() {
        $options = get_option('tggr_options', array());
        $events = $this->get_event_scripts();
        
        foreach ($events as $event_key => $script_handle) {
            if (isset($options[$event_key]) && $options[$event_key]) {
                wp_register_script($script_handle, false, array('jquery'), TGGR_VERSION, true);
            }
        }
        
        // Frontend WooCommerce handler
        if (is_woocommerce() || is_cart() || is_checkout()) {
            wp_enqueue_script(
                'tggr-wc-gtm-handler',
                TGGR_PLUGIN_PATH . 'js/wc-gtm-handler.js',
                array('jquery'),
                TGGR_VERSION,
                true
            );
            
            wp_localize_script('tggr-wc-gtm-handler', 'tggr_vars', array(
                'removedProduct' => array()
            ));
        }
    }
    
    /**
     * Add event data to script
     */
    public function add_event_data($script_handle, $data_var, $event_data) {
        if (wp_script_is($script_handle, 'registered')) {
            wp_enqueue_script($script_handle);
            wp_add_inline_script($script_handle, 'window.' . $data_var . ' = ' . wp_json_encode($event_data) . ';', 'before');
            
            // Store for later output
            $this->event_scripts[$script_handle] = $data_var;
        }
    }
    
    /**
     * Print all GA4 scripts in footer using wp_add_inline_script
     */
    public function print_scripts() {
        if (empty($this->event_scripts)) {
            return;
        }
        
        // Build the inline script content
        $inline_script_parts = array();
        foreach ($this->event_scripts as $script_handle => $data_var) {
            if (wp_script_is($script_handle, 'enqueued')) {
                $inline_script_parts[] = "if (window." . esc_js($data_var) . ") {
                    window.dataLayer = window.dataLayer || [];
                    window.dataLayer.push(window." . esc_js($data_var) . ");
                }";
            }
        }
        
        if (!empty($inline_script_parts)) {
            // Register and enqueue a script handle for our inline script
            wp_register_script('tggr-datalayer-push', false, array(), TGGR_VERSION, true);
            wp_enqueue_script('tggr-datalayer-push');
            
            $inline_script = 'document.addEventListener("DOMContentLoaded", function() {
                ' . implode("\n                ", $inline_script_parts) . '
            });';
            
            wp_add_inline_script('tggr-datalayer-push', $inline_script);
        }
    }
}

// Helper function for backwards compatibility
function tggr_add_ga4_event_data($script_handle, $data_var, $event_data) {
    TGGR_Script_Manager::get_instance()->add_event_data($script_handle, $data_var, $event_data);
}

// Initialize script manager
TGGR_Script_Manager::get_instance();
?>
