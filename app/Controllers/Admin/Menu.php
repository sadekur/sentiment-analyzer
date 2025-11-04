<?php
namespace EasyCommerce\Controllers\Admin;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Traits\Hook;
use EasyCommerce\Traits\Asset;
use EasyCommerce\Traits\Menu as Menu_Trait;
use EasyCommerce\Helpers\Utility;

class Menu {

	use Hook;
	use Asset;
	use Menu_Trait;

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		add_action('admin_menu', array($this, 'add_admin_menu'));
		add_action('admin_init', array($this, 'register_settings'));
		add_action('admin_post_bulk_update_sentiment', array($this, 'handle_bulk_update'));
	}

	// public function register() {
	// 	$menus = easycommerce_menus();

	// 	$register_submenus = function( $parent_slug, $submenus, $level = 0 ) use ( &$register_submenus ) {
	// 		foreach ( $submenus as $submenu ) {
	// 			$prefix = str_repeat( '↳', $level ) . ' ';

	// 			$this->add_submenu(
	// 				$parent_slug,
	// 				$submenu['page_title'],
	// 				$prefix . $submenu['menu_title'],
	// 				$submenu['capability'] ?? 'manage_options',
	// 				$submenu['slug'],
	// 				$submenu['callback'] ?? '__return_null'
	// 			);

	// 			if ( ! empty( $submenu['submenus'] ) ) {
	// 				$register_submenus( $parent_slug, $submenu['submenus'], $level + 1 );
	// 			}
	// 		}
	// 	};

	// 	foreach ( $menus as $menu ) {
	// 		$this->add_menu(
	// 			$menu['title'],
	// 			$menu['menu_title'],
	// 			$menu['capability'],
	// 			$menu['slug'],
	// 			$menu['callback'],
	// 			$menu['icon'],
	// 			$menu['position']
	// 		);

	// 		if ( ! empty( $menu['submenus'] ) ) {
	// 			$register_submenus( $menu['slug'], $menu['submenus'] );
	// 		}
	// 	}
	// }

	  
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            __('Sentiment Analyzer Settings', 'sentiment-analyzer'),
            __('Sentiment Analyzer', 'sentiment-analyzer'),
            'manage_options',
            'sentiment-analyzer',
            array($this, 'settings_page')
        );
    }

	/**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('sa_settings_group', 'sa_positive_keywords', array(
            'sanitize_callback' => array($this, 'sanitize_keywords')
        ));
        
        register_setting('sa_settings_group', 'sa_negative_keywords', array(
            'sanitize_callback' => array($this, 'sanitize_keywords')
        ));
        
        register_setting('sa_settings_group', 'sa_neutral_keywords', array(
            'sanitize_callback' => array($this, 'sanitize_keywords')
        ));
        
        register_setting('sa_settings_group', 'sa_badge_position', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
    }

	   /**
     * Sanitize keyword input
     */
    public function sanitize_keywords($input) {
        return sanitize_textarea_field($input);
    }

	/**
     * Settings page HTML
     */
    public function settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Handle settings saved message
        if (isset($_GET['settings-updated'])) {
            add_settings_error('sa_messages', 'sa_message', __('Settings Saved', 'sentiment-analyzer'), 'updated');
        }
        
        settings_errors('sa_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('sa_settings_group');
                do_settings_sections('sa_settings_group');
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="sa_positive_keywords"><?php _e('Positive Keywords', 'sentiment-analyzer'); ?></label>
                        </th>
                        <td>
                            <textarea name="sa_positive_keywords" id="sa_positive_keywords" rows="5" cols="50" class="large-text"><?php echo esc_textarea(get_option('sa_positive_keywords')); ?></textarea>
                            <p class="description"><?php _e('Comma-separated list of positive keywords', 'sentiment-analyzer'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="sa_negative_keywords"><?php _e('Negative Keywords', 'sentiment-analyzer'); ?></label>
                        </th>
                        <td>
                            <textarea name="sa_negative_keywords" id="sa_negative_keywords" rows="5" cols="50" class="large-text"><?php echo esc_textarea(get_option('sa_negative_keywords')); ?></textarea>
                            <p class="description"><?php _e('Comma-separated list of negative keywords', 'sentiment-analyzer'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="sa_neutral_keywords"><?php _e('Neutral Keywords', 'sentiment-analyzer'); ?></label>
                        </th>
                        <td>
                            <textarea name="sa_neutral_keywords" id="sa_neutral_keywords" rows="5" cols="50" class="large-text"><?php echo esc_textarea(get_option('sa_neutral_keywords')); ?></textarea>
                            <p class="description"><?php _e('Comma-separated list of neutral keywords', 'sentiment-analyzer'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="sa_badge_position"><?php _e('Badge Position', 'sentiment-analyzer'); ?></label>
                        </th>
                        <td>
                            <select name="sa_badge_position" id="sa_badge_position">
                                <option value="top" <?php selected(get_option('sa_badge_position'), 'top'); ?>><?php _e('Top of Content', 'sentiment-analyzer'); ?></option>
                                <option value="bottom" <?php selected(get_option('sa_badge_position'), 'bottom'); ?>><?php _e('Bottom of Content', 'sentiment-analyzer'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Save Settings', 'sentiment-analyzer')); ?>
            </form>
            
            <hr>
            
            <h2><?php _e('Bulk Actions', 'sentiment-analyzer'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="bulk_update_sentiment">
                <?php wp_nonce_field('bulk_update_sentiment_action', 'bulk_update_sentiment_nonce'); ?>
                
                <p><?php _e('Re-analyze sentiment for all existing posts using current keyword settings.', 'sentiment-analyzer'); ?></p>
                
                <?php submit_button(__('Bulk Update All Posts', 'sentiment-analyzer'), 'secondary', 'submit', false); ?>
            </form>
            
            <hr>
            
            <h2><?php _e('Clear Cache', 'sentiment-analyzer'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="clear_sentiment_cache">
                <?php wp_nonce_field('clear_cache_action', 'clear_cache_nonce'); ?>
                
                <p><?php _e('Clear all cached sentiment query results.', 'sentiment-analyzer'); ?></p>
                
                <?php submit_button(__('Clear Cache', 'sentiment-analyzer'), 'secondary', 'submit', false); ?>
            </form>
        </div>
        <?php
    }

	 /**
     * Handle bulk sentiment update
     */
    public function handle_bulk_update() {
        // Security checks
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'sentiment-analyzer'));
        }
        
        if (!isset($_POST['bulk_update_sentiment_nonce']) || 
            !wp_verify_nonce($_POST['bulk_update_sentiment_nonce'], 'bulk_update_sentiment_action')) {
            wp_die(__('Security check failed.', 'sentiment-analyzer'));
        }
        
        // Get all published posts
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        );
        
        $post_ids = get_posts($args);
        
        foreach ($post_ids as $post_id) {
            $post = get_post($post_id);
            if ($post) {
                sa_clear_sentiment_cache($post_id, $post, false);
            }
        }
        
        // Clear cache after bulk update
        sa_clear_sentiment_cache();
        
        // Redirect back with success message
        wp_redirect(add_query_arg(
            array('page' => 'sentiment-analyzer', 'bulk_updated' => 'true'),
            admin_url('options-general.php')
        ));
        exit;
    }
}
