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
        
        // Hook into WordPress update system
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
        add_filter( 'plugins_api', array( $this, 'plugin_info' ), 10, 3 );
        add_filter( 'upgrader_post_install', array( $this, 'post_install' ), 10, 3 );
        add_filter( 'upgrader_source_selection', array( $this, 'fix_source_selection' ), 10, 4 );
        
        // Add settings link to plugin page
        add_filter( 'plugin_action_links_' . $this->plugin_basename, array( $this, 'add_action_links' ) );
        
        // Add GitHub link to plugin meta
        add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
    }
    
    /**
     * Add GitHub link to plugin meta
     */
    public function plugin_row_meta( $links, $file ) {
        if ( $file !== $this->plugin_basename ) {
            return $links;
        }
        
        $new_links = array(
            'github' => '<a href="https://github.com/' . $this->github_repo . '" target="_blank">' . __( 'GitHub', 'taggrs-datalayer' ) . '</a>',
        );
        
        return array_merge( $links, $new_links );
    }
    
    /**
     * Add settings link to plugin page
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
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code( $response );
        if ( $response_code !== 200 ) {
            return false;
        }
        
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body );
        
        if ( empty( $data ) || isset( $data->message ) ) {
            return false;
        }
        
        // Cache for 12 hours
        set_transient( $cache_key, $data, 12 * HOUR_IN_SECONDS );
        
        return $data;
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
            
            // WordPress expects specific object structure
            $update = new \stdClass();
            $update->id = 'taggrs-wordpress/' . $this->plugin_basename;
            $update->slug = $this->plugin_slug;
            $update->plugin = $this->plugin_basename;
            $update->new_version = $latest_version;
            $update->url = 'https://github.com/' . $this->github_repo;
            $update->package = $download_url;
            $update->icons = array();
            $update->banners = array();
            $update->banners_rtl = array();
            $update->tested = '6.8';
            $update->requires_php = '7.2';
            $update->compatibility = new \stdClass();
            
            $transient->response[ $this->plugin_basename ] = $update;
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
        
        // GitHub zipballs create a directory like: TAGGRS-taggrs-wordpress-abc1234
        // We need to rename it to just: taggrs-wordpress
        $corrected_source = trailingslashit( $remote_source ) . 'taggrs-wordpress/';
        
        // If the corrected source already exists, use it
        if ( $wp_filesystem->is_dir( $corrected_source ) ) {
            return $corrected_source;
        }
        
        // Otherwise, find the first directory and rename it
        $source_files = $wp_filesystem->dirlist( $remote_source );
        
        if ( ! $source_files ) {
            return $source;
        }
        
        foreach ( $source_files as $file_name => $file_details ) {
            if ( $file_details['type'] === 'd' ) {
                $old_source = trailingslashit( $remote_source ) . $file_name;
                
                // Rename to the expected directory name
                if ( $wp_filesystem->move( $old_source, $corrected_source ) ) {
                    return $corrected_source;
                }
                
                break;
            }
        }
        
        return $source;
    }
    
    /**
     * Post install hook
     */
    public function post_install( $response, $hook_extra, $result ) {
        if ( ! isset( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->plugin_basename ) {
            return $response;
        }
        
        // Clear update cache
        delete_transient( 'taggrs_github_release' );
        
        return $response;
    }
}

// Initialize the updater
new TAGGRS_Plugin_Updater();
