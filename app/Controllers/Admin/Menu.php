<?php
namespace Sentiment\Controllers\Admin;

defined( 'ABSPATH' ) || exit;

class Menu {

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		add_action('admin_menu', array($this, 'add_admin_menu'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
	}
	  
    /**
     * Add admin menu as standalone menu (not under Settings)
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Sentiment Analyzer', 'sentiment-analyzer'),      // Page title
            __('Sentiment Analyzer', 'sentiment-analyzer'),      // Menu title
            'manage_options',                                     // Capability
            'sentiment-analyzer',                                 // Menu slug
            array($this, 'settings_page'),                       // Callback
            'dashicons-chart-line',                              // Icon
            30                                                    // Position
        );
    }

	/**
	 * Enqueue admin scripts and styles
	 */
	public function enqueue_admin_scripts($hook) {
		// Only load on our settings page
		if ('toplevel_page_sentiment-analyzer' !== $hook) {
			return;
		}

		wp_enqueue_script(
			'sentiment-analyzer-admin',
			plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/js/admin.js',
			array('jquery'),
			'2.0.0',
			true
		);

		wp_localize_script('sentiment-analyzer-admin', 'sentimentAnalyzer', array(
			'apiUrl' => rest_url('sentiment-analyzer/v1'),
			'nonce' => wp_create_nonce('wp_rest'),
			'strings' => array(
				'saving' => __('Saving settings...', 'sentiment-analyzer'),
				'saveSuccess' => __('Settings saved successfully!', 'sentiment-analyzer'),
				'saveError' => __('Error saving settings. Please try again.', 'sentiment-analyzer'),
				'bulkUpdating' => __('Analyzing posts...', 'sentiment-analyzer'),
				'bulkSuccess' => __('Successfully analyzed {count} posts!', 'sentiment-analyzer'),
				'bulkError' => __('Error analyzing posts. Please try again.', 'sentiment-analyzer'),
				'cacheClearing' => __('Clearing cache...', 'sentiment-analyzer'),
				'cacheSuccess' => __('Cache cleared successfully!', 'sentiment-analyzer'),
				'cacheError' => __('Error clearing cache. Please try again.', 'sentiment-analyzer'),
				'confirmBulk' => __('This will re-analyze all posts. Continue?', 'sentiment-analyzer'),
			)
		));

		wp_enqueue_style(
			'sentiment-analyzer-admin',
			plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/css/sentiment-analyzer-admin.css',
			array(),
			'2.0.0'
		);
	}

	/**
     * Settings page HTML
     */
    public function settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'sentiment-analyzer'));
        }
        ?>
        <div class="wrap sentiment-analyzer-admin">
            <h1 class="wp-heading-inline">
				<?php echo esc_html__('Sentiment Analyzer', 'sentiment-analyzer'); ?>
			</h1>
            
			<hr class="wp-header-end">

			<!-- Global Messages Container -->
			<div id="sa-messages"></div>

			<!-- Settings Form -->
			<div class="sa-card">
				<h2><?php esc_html_e('Keyword Settings', 'sentiment-analyzer'); ?></h2>
				<p class="description">
					<?php esc_html_e('Configure the keywords used to determine post sentiment. Changes will apply to newly analyzed posts.', 'sentiment-analyzer'); ?>
				</p>

				<form id="sentiment-settings-form">
					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="sa_positive_keywords">
									<?php esc_html_e('Positive Keywords', 'sentiment-analyzer'); ?>
								</label>
							</th>
							<td>
								<textarea 
									name="positive_keywords" 
									id="sa_positive_keywords" 
									rows="5" 
									cols="50" 
									class="large-text code"
								><?php echo esc_textarea(get_option('sa_positive_keywords')); ?></textarea>
								<p class="description">
									<?php esc_html_e('Comma-separated list of positive keywords (e.g., good, great, excellent)', 'sentiment-analyzer'); ?>
								</p>
							</td>
						</tr>
						
						<tr>
							<th scope="row">
								<label for="sa_negative_keywords">
									<?php esc_html_e('Negative Keywords', 'sentiment-analyzer'); ?>
								</label>
							</th>
							<td>
								<textarea 
									name="negative_keywords" 
									id="sa_negative_keywords" 
									rows="5" 
									cols="50" 
									class="large-text code"
								><?php echo esc_textarea(get_option('sa_negative_keywords')); ?></textarea>
								<p class="description">
									<?php esc_html_e('Comma-separated list of negative keywords (e.g., bad, terrible, awful)', 'sentiment-analyzer'); ?>
								</p>
							</td>
						</tr>
						
						<tr>
							<th scope="row">
								<label for="sa_neutral_keywords">
									<?php esc_html_e('Neutral Keywords', 'sentiment-analyzer'); ?>
								</label>
							</th>
							<td>
								<textarea 
									name="neutral_keywords" 
									id="sa_neutral_keywords" 
									rows="5" 
									cols="50" 
									class="large-text code"
								><?php echo esc_textarea(get_option('sa_neutral_keywords')); ?></textarea>
								<p class="description">
									<?php esc_html_e('Comma-separated list of neutral keywords (e.g., okay, average, decent)', 'sentiment-analyzer'); ?>
								</p>
							</td>
						</tr>
						
						<tr>
							<th scope="row">
								<label for="sa_badge_position">
									<?php esc_html_e('Badge Position', 'sentiment-analyzer'); ?>
								</label>
							</th>
							<td>
								<select name="badge_position" id="sa_badge_position" class="regular-text">
									<option value="top" <?php selected(get_option('sa_badge_position'), 'top'); ?>>
										<?php esc_html_e('Top of Content', 'sentiment-analyzer'); ?>
									</option>
									<option value="bottom" <?php selected(get_option('sa_badge_position'), 'bottom'); ?>>
										<?php esc_html_e('Bottom of Content', 'sentiment-analyzer'); ?>
									</option>
								</select>
								<p class="description">
									<?php esc_html_e('Choose where to display the sentiment badge on posts', 'sentiment-analyzer'); ?>
								</p>
							</td>
						</tr>
					</table>

					<p class="submit">
						<button type="submit" class="button button-primary button-large" id="save-settings">
							<?php esc_html_e('Save Settings', 'sentiment-analyzer'); ?>
						</button>
						<span class="spinner"></span>
					</p>
				</form>
			</div>

			<!-- Bulk Actions Section -->
			<div class="sa-card">
				<h2><?php esc_html_e('Bulk Actions', 'sentiment-analyzer'); ?></h2>
				<p class="description">
					<?php esc_html_e('Re-analyze sentiment for all existing posts using the current keyword settings. This may take a while if you have many posts.', 'sentiment-analyzer'); ?>
				</p>

				<div class="sa-bulk-actions">
					<button id="bulk-update-sentiment" class="button button-primary button-large" type="button">
						<span class="dashicons dashicons-update"></span>
						<?php esc_html_e('Bulk Update All Posts', 'sentiment-analyzer'); ?>
					</button>
					
					<div id="bulk-update-progress" style="display: none; margin-top: 15px;">
						<div class="sa-progress-bar">
							<div class="sa-progress-fill"></div>
						</div>
						<p class="sa-progress-text"></p>
					</div>
					
					<div id="bulk-update-status"></div>
				</div>
			</div>

			<!-- Clear Cache Section -->
			<div class="sa-card">
				<h2><?php esc_html_e('Clear Cache', 'sentiment-analyzer'); ?></h2>
				<p class="description">
					<?php esc_html_e('Clear all cached sentiment query results. Use this if you notice stale data or after making significant changes.', 'sentiment-analyzer'); ?>
				</p>

				<div class="sa-clear-cache">
					<button id="clear-cache" class="button button-secondary button-large" type="button">
						<span class="dashicons dashicons-trash"></span>
						<?php esc_html_e('Clear Cache', 'sentiment-analyzer'); ?>
					</button>
					
					<div id="clear-cache-status"></div>
				</div>
			</div>

			<!-- API Information Section -->
			<div class="sa-card">
				<h2><?php esc_html_e('API Information', 'sentiment-analyzer'); ?></h2>
				<p><?php esc_html_e('You can use the REST API to interact with the plugin programmatically.', 'sentiment-analyzer'); ?></p>
				
				<div class="sa-api-url">
					<strong><?php esc_html_e('Base URL:', 'sentiment-analyzer'); ?></strong>
					<code class="sa-code-block"><?php echo esc_url(rest_url('sentiment-analyzer/v1')); ?></code>
					<button class="button button-small copy-api-url" data-clipboard-text="<?php echo esc_attr(rest_url('sentiment-analyzer/v1')); ?>">
						<span class="dashicons dashicons-clipboard"></span>
						<?php esc_html_e('Copy', 'sentiment-analyzer'); ?>
					</button>
				</div>
				
				<h3><?php esc_html_e('Available Endpoints:', 'sentiment-analyzer'); ?></h3>
				<table class="widefat striped sa-endpoints-table">
					<thead>
						<tr>
							<th><?php esc_html_e('Method', 'sentiment-analyzer'); ?></th>
							<th><?php esc_html_e('Endpoint', 'sentiment-analyzer'); ?></th>
							<th><?php esc_html_e('Description', 'sentiment-analyzer'); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><span class="sa-method post">POST</span></td>
							<td><code>/analyze/{post_id}</code></td>
							<td><?php esc_html_e('Analyze a single post', 'sentiment-analyzer'); ?></td>
						</tr>
						<tr>
							<td><span class="sa-method post">POST</span></td>
							<td><code>/analyze/bulk</code></td>
							<td><?php esc_html_e('Bulk analyze all posts', 'sentiment-analyzer'); ?></td>
						</tr>
						<tr>
							<td><span class="sa-method get">GET</span></td>
							<td><code>/sentiment/{post_id}</code></td>
							<td><?php esc_html_e('Get post sentiment', 'sentiment-analyzer'); ?></td>
						</tr>
						<tr>
							<td><span class="sa-method post">POST</span></td>
							<td><code>/cache/clear</code></td>
							<td><?php esc_html_e('Clear cache', 'sentiment-analyzer'); ?></td>
						</tr>
						<tr>
							<td><span class="sa-method get">GET</span></td>
							<td><code>/posts/{sentiment}</code></td>
							<td><?php esc_html_e('Get posts by sentiment', 'sentiment-analyzer'); ?></td>
						</tr>
						<tr>
							<td><span class="sa-method get">GET</span></td>
							<td><code>/settings</code></td>
							<td><?php esc_html_e('Get plugin settings', 'sentiment-analyzer'); ?></td>
						</tr>
						<tr>
							<td><span class="sa-method post">POST</span></td>
							<td><code>/settings</code></td>
							<td><?php esc_html_e('Update plugin settings', 'sentiment-analyzer'); ?></td>
						</tr>
					</tbody>
				</table>
			</div>
        </div>
        <?php
    }
}