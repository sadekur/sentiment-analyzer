<?php
namespace Sentiment\Controllers\Common;

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
			'assets'          => SENTIMENT_ANALYZER_URL . 'assets/',
			'plugin_url'      => SENTIMENT_ANALYZER_URL,
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
				SENTIMENT_ANALYZER_ASSETS . '/js/admin.js',
				array( 'jquery' ),
				SENTIMENT_ANALYZER_VERSION,
				true
			);

			wp_enqueue_style(
				'sentiment-analyzer-admin',
				SENTIMENT_ANALYZER_ASSETS . '/css/admin.css',
				array(),
				SENTIMENT_ANALYZER_VERSION
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
				SENTIMENT_ANALYZER_ASSETS . '/css/public.css',
				array(),
				SENTIMENT_ANALYZER_VERSION
			);

			wp_enqueue_script(
				'sentiment-analyzer-public',
				SENTIMENT_ANALYZER_ASSETS . '/js/public.js',
				array( 'jquery' ),
				SENTIMENT_ANALYZER_VERSION,
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
				SENTIMENT_ANALYZER_ASSETS . '/js/common.js',
				array( 'jquery' ),
				SENTIMENT_ANALYZER_VERSION,
				true
			);

			wp_enqueue_style(
				'sentiment-analyzer-common',
				SENTIMENT_ANALYZER_ASSETS . '/css/common.css',
				array(),
				SENTIMENT_ANALYZER_VERSION
			);

			wp_localize_script(
				'sentiment-analyzer-common',
				'SENTIMENT_ANALYZER',
				apply_filters( 'sentiment-analyzer-localized_vars', $localized )
			);
		}
	}
}