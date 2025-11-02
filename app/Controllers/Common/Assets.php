<?php
namespace Sentiment\Analyzer\Controllers\Common;

defined( 'ABSPATH' ) || exit;

class Assets {

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'add_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'add_assets' ) );
	}

	public function add_assets() {
		$load_common_assets = false;

		/**
		 * Localize PHP variables to be used in the JS files
		 *
		 * @since 1.0
		 */
		$localized = array(
			'rest_base'       => esc_url_raw( get_rest_url() ),
			'nonce'           => wp_create_nonce( 'wp_rest' ),
			'logout_url'      => wp_logout_url(),
			'ajax_url'        => admin_url( 'admin-ajax.php' ),
			'assets'          => Sentiment_Analyzer_URL . 'assets/',
			'plugin_url'      => Sentiment_Analyzer_URL,
		);

		/**
		 * Assets that are required in the specific screens under `wp-admin` only
		 *
		 * @since 1.0
		 */
		if ( is_admin() ) {
			$load_common_assets = true;
			
			wp_enqueue_script(
				'sentiment-analyzer-admin',
				Sentiment_Analyzer_ASSETS . '/js/admin.js',
				array( 'jquery' ),
				Sentiment_Analyzer_VERSION,
				true
			);

			wp_enqueue_style(
				'sentiment-analyzer-admin',
				Sentiment_Analyzer_ASSETS . '/css/admin.css',
				array(),
				Sentiment_Analyzer_VERSION
			);
		}

		/**
		 * Assets that are required in the public-facing screens only
		 *
		 * @since 1.0
		 */
		elseif ( ! is_admin() ) {
			$load_common_assets = true;
			
			wp_enqueue_style(
				'sentiment-analyzer-public',
				Sentiment_Analyzer_ASSETS . '/css/public.css',
				array(),
				Sentiment_Analyzer_VERSION
			);

			wp_enqueue_script(
				'sentiment-analyzer-public',
				Sentiment_Analyzer_ASSETS . '/js/public.js',
				array( 'jquery' ),
				Sentiment_Analyzer_VERSION,
				true
			);
		}

		/**
		 * Assets that are required in both the `wp-admin` and public-facing screens
		 *
		 * @since 1.0
		 */
		if ( $load_common_assets ) {
			wp_enqueue_script(
				'sentiment-analyzer-common',
				Sentiment_Analyzer_ASSETS . '/js/common.js',
				array( 'jquery' ),
				Sentiment_Analyzer_VERSION,
				true
			);

			wp_enqueue_style(
				'sentiment-analyzer-common',
				Sentiment_Analyzer_ASSETS . '/css/common.css',
				array(),
				Sentiment_Analyzer_VERSION
			);

			wp_localize_script(
				'sentiment-analyzer-common',
				'SENTIMENT_ANALYZER',
				apply_filters( 'sentiment-analyzer-localized_vars', $localized )
			);
		}
	}
}