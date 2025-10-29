<?php
namespace EasyCommerce\Controllers\Common;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Traits\Hook;
use EasyCommerce\Traits\Asset;
use EasyCommerce\Traits\Cache;
use EasyCommerce\Traits\Queue;
use EasyCommerce\Traits\Cleaner;
use EasyCommerce\Helpers\Utility;

class Process {

	use Hook;
	use Asset;
	use Cache;
	use Queue;
	use Cleaner;

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		$this->action( 'easycommerce_install_addon', array( $this, 'handle_addon_installation' ) );
	}

	public function handle_addon_installation( $slug ) {
		if ( ! function_exists( 'activate_plugin' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin_dir  = WP_PLUGIN_DIR . '/' . $slug;
		$plugin_file = "{$slug}/{$slug}.php";

		if ( file_exists( $plugin_dir ) ) {
			if ( ! is_plugin_active( $plugin_file ) ) {
				wp_clean_plugins_cache();
				activate_plugin( $plugin_file );
			}
			return;
		}
		$api     = get_option( 'easycommerce_api' );
		$headers = isset( $api->email ) ? array( 'email' => $api->email ) : array();

		$data_url = easycommerce_dev_store( "/wp-json/easysuite/v1/addons/{$slug}" );
		$response = json_decode( wp_remote_retrieve_body( wp_remote_get( $data_url, array( 'headers' => $headers ) ) ) );

		if ( ! empty( $response->success ) && $response->success && ! empty( $response->data->download_url ) ) {
			$temp_file = download_url( $response->data->download_url );

			if ( ! is_wp_error( $temp_file ) ) {
				$unzip_result = unzip_file( $temp_file, trailingslashit( WP_PLUGIN_DIR ) . $slug );
				@unlink( $temp_file );

				if ( is_wp_error( $unzip_result ) ) {
					return;
				}

				if ( ! is_plugin_active( $plugin_file ) ) {
					wp_clean_plugins_cache();
					activate_plugin( $plugin_file );
				}
			}
		}
	}
}