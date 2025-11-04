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
		define( 'SENTIMENT_ANALYZER_VERSION', self::version );
		define( 'SENTIMENT_ANALYZER_FILE', __FILE__ );
		define( 'SENTIMENT_ANALYZER_PATH', plugin_dir_path(__FILE__) );
		define( 'SENTIMENT_ANALYZER_URL', plugin_dir_url(__FILE__) );
		define( 'SENTIMENT_ANALYZER_ASSETS', SENTIMENT_ANALYZER_URL . 'assets/' );
	}

	/**
	 * Initialize the plugin
	 *
	 * @return void
	 */
	public function init_plugin() {

		new Sentiment\Controllers\Common\Assets();
		new Sentiment\Controllers\Common\Activation();

		// if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		// 	new Thrail\Commerce\Ajax();
		// }

		if ( is_admin() ) {
			new Sentiment\Controllers\Admin\Menu();
		} else {
			new Sentiment\Controllers\Front\Shortcode();
		    new Sentiment\Controllers\Front\Front();
		}

	}
}
function sentiment_analyzer() {
	return Sentiment_Analyzer::init();
}

sentiment_analyzer();