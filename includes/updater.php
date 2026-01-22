<?php
/**
 * TAGGRS Plugin Updater
 * 
 * Handles automatic updates from GitHub repository
 * 
 * @package TAGGRS
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class TAGGRS_Plugin_Updater {
    
    private $plugin_slug;
    private $plugin_basename;
    private $github_repo;
    private $plugin_data;
    private $github_response;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->plugin_slug = 'taggrs-datalayer';
        $this->plugin_basename = plugin_basename( dirname( dirname( __FILE__ ) ) . '/taggrs-datalayer.php' );
        $this->github_repo = 'TAGGRS/taggrs-wordpress';
        
        // Get plugin data
        if ( ! function_exists( 'get_plugin_data' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $this->plugin_data = get_plugin_data( dirname( dirname( __FILE__ ) ) . '/taggrs-datalayer.php' );
        
        // Hook into WordPress
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
        add_filter( 'plugins_api', array( $this, 'plugin_info' ), 10, 3 );
        add_filter( 'upgrader_post_install', array( $this, 'post_install' ), 10, 3 );
        add_filter( 'upgrader_source_selection', array( $this, 'fix_source_selection' ), 10, 4 );
        
        // Auto-updates support (WordPress 5.5+)
        add_filter( 'auto_update_plugin', array( $this, 'auto_update_filter' ), 10, 2 );
        
        // Add settings link to plugin page
        add_filter( 'plugin_action_links_' . $this->plugin_basename, array( $this, 'add_action_links' ) );
        
        // Add admin notice for available updates
        add_action( 'admin_notices', array( $this, 'update_notice' ) );
        
        // AJAX handler for manual update check
        add_action( 'wp_ajax_taggrs_check_updates', array( $this, 'ajax_check_updates' ) );
        
        // Add plugin meta row
        add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
    }
    
    /**
     * AJAX handler for checking updates
     */
    public function ajax_check_updates() {
        // Check nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'taggrs-check-updates' ) ) {
            wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
        }
        
        // Check permissions
        if ( ! current_user_can( 'update_plugins' ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied' ) );
        }
        
        // Clear cache
        delete_transient( 'taggrs_github_release' );
        
        // Send success response
        wp_send_json_success( array( 'message' => 'Update check completed' ) );
    }
    
    /**
     * Add custom meta to plugin row
     */
    public function plugin_row_meta( $links, $file ) {
        if ( $file !== $this->plugin_basename ) {
            return $links;
        }
        
        $auto_update_enabled = get_option( 'taggrs_auto_update', false );
        
        $new_links = array(
            'github' => '<a href="https://github.com/' . $this->github_repo . '" target="_blank">' . __( 'GitHub', 'taggrs-datalayer' ) . '</a>',
        );
        
        if ( $auto_update_enabled ) {
            $new_links['auto_update'] = '<span style="color: #00a32a;">⚙️ ' . __( 'Auto-update enabled', 'taggrs-datalayer' ) . '</span>';
        }
        
        return array_merge( $links, $new_links );
    }
    
    /**
     * Show admin notice if update is available
     */
    public function update_notice() {
        $screen = get_current_screen();
        
        // Only show on plugins page
        if ( $screen->id !== 'plugins' ) {
            return;
        }
        
        $release = $this->get_github_release_info();
        
        if ( ! $release ) {
            return;
        }
        
        $latest_version = ltrim( $release->tag_name, 'v' );
        $current_version = $this->plugin_data['Version'];
        
        if ( version_compare( $current_version, $latest_version, '<' ) ) {
            $message = sprintf(
                /* translators: 1: plugin name, 2: current version, 3: new version */
                __( '<strong>TAGGRS Plugin Update Available!</strong> You are running version %1$s. Version %2$s is now available. <a href="%3$s">View update settings</a>', 'taggrs-datalayer' ),
                $current_version,
                $latest_version,
                admin_url( 'admin.php?page=wc-gtm-settings' )
            );
            
            echo '<div class="notice notice-warning is-dismissible"><p>' . wp_kses_post( $message ) . '</p></div>';
        }
    }
    
    /**
     * Add action links to plugin page
     */
    public function add_action_links( $links ) {
        $settings_link = '<a href="' . admin_url( 'admin.php?page=wc-gtm-settings' ) . '">' . __( 'Settings', 'taggrs-datalayer' ) . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }
    
    /**
     * Get GitHub release information
     */
    private function get_github_release_info() {
        // Check cache first
        $cache_key = 'taggrs_github_release';
        $cached_response = get_transient( $cache_key );
        
        if ( false !== $cached_response ) {
            return $cached_response;
        }
        
        // Fetch from GitHub API
        $api_url = 'https://api.github.com/repos/' . $this->github_repo . '/releases/latest';
        
        $response = wp_remote_get( $api_url, array(
            'timeout' => 10,
            'headers' => array(
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'TAGGRS-WordPress-Plugin',
            ),
        ) );
        
        if ( is_wp_error( $response ) ) {
            // Log error for debugging
            error_log( 'TAGGRS Updater Error: ' . $response->get_error_message() );
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code( $response );
        if ( $response_code !== 200 ) {
            error_log( 'TAGGRS Updater: GitHub API returned status code ' . $response_code );
            return false;
        }
        
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body );
        
        if ( empty( $data ) || isset( $data->message ) ) {
            // GitHub API error (e.g., rate limit)
            if ( isset( $data->message ) ) {
                error_log( 'TAGGRS Updater: GitHub API error - ' . $data->message );
            }
            return false;
        }
        
        // Cache for 12 hours
        set_transient( $cache_key, $data, 12 * HOUR_IN_SECONDS );
        
        return $data;
    }
    
    /**
     * Get readme.txt version from GitHub (fallback method)
     */
    private function get_readme_version() {
        $readme_url = 'https://raw.githubusercontent.com/' . $this->github_repo . '/main/readme.txt';
        
        $response = wp_remote_get( $readme_url, array(
            'timeout' => 10,
        ) );
        
        if ( is_wp_error( $response ) ) {
            return false;
        }
        
        $body = wp_remote_retrieve_body( $response );
        
        // Parse readme.txt for Stable tag
        if ( preg_match( '/Stable tag:\s*([0-9.]+)/i', $body, $matches ) ) {
            return trim( $matches[1] );
        }
        
        return false;
    }
    
    /**
     * Check for updates
     */
    public function check_update( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }
        
        // Get latest release from GitHub
        $release = $this->get_github_release_info();
        
        if ( ! $release ) {
            return $transient;
        }
        
        // Get version from tag name (remove 'v' prefix if present)
        $latest_version = ltrim( $release->tag_name, 'v' );
        $current_version = $this->plugin_data['Version'];
        
        // Compare versions
        if ( version_compare( $current_version, $latest_version, '<' ) ) {
            // Find the .zip asset
            $download_url = '';
            if ( ! empty( $release->assets ) ) {
                foreach ( $release->assets as $asset ) {
                    if ( strpos( $asset->name, '.zip' ) !== false ) {
                        $download_url = $asset->browser_download_url;
                        break;
                    }
                }
            }
            
            // Fallback to zipball_url if no zip asset found
            if ( empty( $download_url ) ) {
                $download_url = $release->zipball_url;
            }
            
            $plugin_data = array(
                'slug' => $this->plugin_slug,
                'plugin' => $this->plugin_basename,
                'new_version' => $latest_version,
                'url' => 'https://github.com/' . $this->github_repo,
                'package' => $download_url,
                'tested' => '6.8',
            );
            
            $transient->response[ $this->plugin_basename ] = (object) $plugin_data;
        }
        
        return $transient;
    }
    
    /**
     * Provide plugin information for the "View Details" screen
     */
    public function plugin_info( $result, $action, $args ) {
        if ( $action !== 'plugin_information' ) {
            return $result;
        }
        
        if ( ! isset( $args->slug ) || $args->slug !== $this->plugin_slug ) {
            return $result;
        }
        
        $release = $this->get_github_release_info();
        
        if ( ! $release ) {
            return $result;
        }
        
        $latest_version = ltrim( $release->tag_name, 'v' );
        
        // Find the .zip asset
        $download_url = '';
        if ( ! empty( $release->assets ) ) {
            foreach ( $release->assets as $asset ) {
                if ( strpos( $asset->name, '.zip' ) !== false ) {
                    $download_url = $asset->browser_download_url;
                    break;
                }
            }
        }
        
        if ( empty( $download_url ) ) {
            $download_url = $release->zipball_url;
        }
        
        $plugin_info = new stdClass();
        $plugin_info->name = $this->plugin_data['Name'];
        $plugin_info->slug = $this->plugin_slug;
        $plugin_info->version = $latest_version;
        $plugin_info->author = '<a href="https://taggrs.com">TAGGRS BV</a>';
        $plugin_info->homepage = 'https://github.com/' . $this->github_repo;
        $plugin_info->requires = '4.5';
        $plugin_info->tested = '6.8';
        $plugin_info->downloaded = 0;
        $plugin_info->last_updated = $release->published_at;
        $plugin_info->sections = array(
            'description' => $this->plugin_data['Description'],
            'changelog' => $this->parse_changelog( $release->body ),
        );
        $plugin_info->download_link = $download_url;
        
        return $plugin_info;
    }
    
    /**
     * Parse changelog from GitHub release notes
     */
    private function parse_changelog( $body ) {
        if ( empty( $body ) ) {
            return 'No changelog available.';
        }
        
        // Convert markdown to basic HTML
        $changelog = wpautop( $body );
        
        return $changelog;
    }
    
    /**
     * Fix source selection during installation
     */
    public function fix_source_selection( $source, $remote_source, $upgrader, $hook_extra = null ) {
        global $wp_filesystem;
        
        // Check if we're updating this plugin
        if ( ! isset( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->plugin_basename ) {
            return $source;
        }
        
        // Get the list of files in the source directory
        $source_files = $wp_filesystem->dirlist( $remote_source );
        
        if ( ! $source_files ) {
            return $source;
        }
        
        // GitHub usually creates a directory like: TAGGRS-taggrs-wordpress-abc1234
        // We need to find the correct subdirectory
        $first_dir = null;
        foreach ( $source_files as $file_name => $file_details ) {
            if ( $file_details['type'] === 'd' ) {
                $first_dir = trailingslashit( $remote_source ) . $file_name;
                break;
            }
        }
        
        if ( $first_dir ) {
            $source = trailingslashit( $first_dir );
        }
        
        return $source;
    }
    
    /**
     * Post install hook
     */
    public function post_install( $response, $hook_extra, $result ) {
        global $wp_filesystem;
        
        if ( ! isset( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->plugin_basename ) {
            return $response;
        }
        
        // Clear update cache
        delete_transient( 'taggrs_github_release' );
        
        return $response;
    }
    
    /**
     * Filter for auto-updates
     */
    public function auto_update_filter( $update, $item ) {
        if ( ! isset( $item->plugin ) || $item->plugin !== $this->plugin_basename ) {
            return $update;
        }
        
        // Check if auto-update is enabled in settings
        $auto_update_enabled = get_option( 'taggrs_auto_update', false );
        
        if ( $auto_update_enabled ) {
            return true;
        }
        
        return $update;
    }
}

// Initialize the updater
new TAGGRS_Plugin_Updater();
