<?php
namespace Sentiment\Controllers\Admin;

defined( 'ABSPATH' ) || exit;

class Menu {

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		add_action('admin_menu', array($this, 'add_admin_menu'));
		add_action('admin_init', array($this, 'register_settings'));
	}
	  
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

        // Handle bulk update success
        if (isset($_GET['bulk_updated'])) {
            add_settings_error('sa_messages', 'sa_message', __('All posts analyzed successfully!', 'sentiment-analyzer'), 'updated');
        }

        // Handle cache cleared success
        if (isset($_GET['cache_cleared'])) {
            add_settings_error('sa_messages', 'sa_message', __('Cache cleared successfully!', 'sentiment-analyzer'), 'updated');
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
            <div class="sa-bulk-actions">
                <p><?php _e('Re-analyze sentiment for all existing posts using current keyword settings.', 'sentiment-analyzer'); ?></p>
                
                <button id="bulk-update-sentiment" class="button button-primary" type="button">
					<?php esc_html_e('Bulk Update All Posts', 'sentiment-analyzer'); ?>
				</button>
                
                <div id="bulk-update-progress" style="display: none; margin-top: 10px;">
                    <div class="sa-progress-bar">
                        <div class="sa-progress-fill"></div>
                    </div>
                    <p class="sa-progress-text"></p>
                </div>
                
                <div id="bulk-update-status"></div>
            </div>
            
            <hr>
            
            <h2><?php _e('Clear Cache', 'sentiment-analyzer'); ?></h2>
            <div class="sa-clear-cache">
                <p><?php _e('Clear all cached sentiment query results.', 'sentiment-analyzer'); ?></p>
                
                <button id="clear-cache" class="button button-secondary" type="button">
					<?php esc_html_e('Clear Cache', 'sentiment-analyzer'); ?>
				</button>
                
                <div id="clear-cache-status"></div>
            </div>

			<hr>

			<h2><?php _e('API Information', 'sentiment-analyzer'); ?></h2>
			<div class="sa-api-info">
				<p><?php _e('You can use the REST API to interact with the plugin programmatically.', 'sentiment-analyzer'); ?></p>
				<p><strong><?php _e('Base URL:', 'sentiment-analyzer'); ?></strong> <code><?php echo esc_url(rest_url('sentiment-analyzer/v1')); ?></code></p>
				
				<h3><?php _e('Available Endpoints:', 'sentiment-analyzer'); ?></h3>
				<ul style="list-style: disc; margin-left: 20px;">
					<li><code>POST /analyze/{post_id}</code> - <?php _e('Analyze a single post', 'sentiment-analyzer'); ?></li>
					<li><code>POST /analyze/bulk</code> - <?php _e('Bulk analyze all posts', 'sentiment-analyzer'); ?></li>
					<li><code>GET /sentiment/{post_id}</code> - <?php _e('Get post sentiment', 'sentiment-analyzer'); ?></li>
					<li><code>POST /cache/clear</code> - <?php _e('Clear cache', 'sentiment-analyzer'); ?></li>
					<li><code>GET /posts/{sentiment}</code> - <?php _e('Get posts by sentiment', 'sentiment-analyzer'); ?></li>
					<li><code>GET /settings</code> - <?php _e('Get plugin settings', 'sentiment-analyzer'); ?></li>
					<li><code>POST /settings</code> - <?php _e('Update plugin settings', 'sentiment-analyzer'); ?></li>
				</ul>
			</div>
        </div>
        <?php
    }
}