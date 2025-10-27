<?php
/**
 * Plugin Name:       Sentiment Analyzer
 * Plugin URI:        https://sadekurrahman.net
 * Description:       A plugin Sentiment Analyzer for WordPress.
 * Version:           1.0.0
 * Requires at least: 6.1
 * Requires PHP:      7.4
 * Author:            Sadekur Rahman
 * Author URI:        https://sadekurrahman.net
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sentiment-analyzer
 * Domain Path:       /languages
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';
// require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
// register_activation_hook(__FILE__, 'thrail_crm_activate');

/**
 * The main plugin class
 */
final class Sentiment_Analyzer{

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	const version = '1.0';

	/**
	 * Class construcotr
	 */
	private function __construct() {
		$this->define_constants();

		add_action( 'init', [ $this, 'init_plugin' ] );
	}

	public static function init() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Define the required plugin constants
	 *
	 * @return void
	 */
	public function define_constants() {
		define( 'Sentiment_Analyzer_VERSION', self::version );
		define( 'Sentiment_Analyzer_FILE', __FILE__ );
		define( 'Sentiment_Analyzer_PATH', plugin_dir_path(__FILE__) );
		define( 'Sentiment_Analyzer_URL', plugin_dir_url(__FILE__) );
		define( 'Sentiment_Analyzer_ASSETS', Sentiment_Analyzer_URL . 'assets' );
	}

	/**
	 * Initialize the plugin
	 *
	 * @return void
	 */
	public function init_plugin() {

		new Thrail\Commerce\Assets();
		new Thrail\Commerce\Email();
		new Thrail\Commerce\API();
		new Thrail\Commerce\Common\Init();
		new Thrail\Commerce\Blocks();
		new Thrail\Commerce\Features();
		new Thrail\Commerce\Helper();

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			new Thrail\Commerce\Ajax();
		}

		if ( is_admin() ) {
			new Thrail\Commerce\Admin();
		} else {
			new Thrail\Commerce\Frontend();
		}

	}
}
function sentiment_analyzer() {
	return Sentiment_Analyzer::init();
}

sentiment_analyzer();