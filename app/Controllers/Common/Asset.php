<?php

namespace EasyCommerce\Controllers\Common;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Traits\Hook;
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Cart;
use EasyCommerce\Models\Customer;
use EasyCommerce\Models\Tax;
use EasyCommerce\Traits\Asset as Asset_Trait;
use EasyCommerce\Models\Product;

class Asset {

	use Hook;
	use Asset_Trait;

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		$this->action( 'enqueue_block_assets', array( $this, 'enqueue_block_assets' ) );
		$this->action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		$this->action( 'wp_enqueue_scripts', array( $this, 'add_assets' ) );
		$this->action( 'admin_enqueue_scripts', array( $this, 'add_assets' ) );
	}

	/**
	 * Enqueue block editor assets.
	 */
	public function enqueue_block_assets() {

		$this->enqueue_script(
			'easycommerce_blocks',
			EASYCOMMERCE_BUILD_URL . 'blocks.bundle.js',
			array( 'wp-blocks', 'wp-element', 'wp-editor', 'jquery' )
		);
	}

	/**
	 * Enqueue block editor only assets.
	 */
	public function enqueue_block_editor_assets() {
		if ( 'no' === Utility::get_option( 'ai', 'template-generator', 'enable', 'no' ) ) {
			return;
		}

		$this->enqueue_script(
			'easycommerce_editor',
			EASYCOMMERCE_BUILD_URL . 'editor.bundle.js',
			array( 'wp-element', 'wp-data', 'wp-i18n', 'wp-api-fetch', 'react', 'react-dom' )
		);
	}

	public function add_assets() {

		$customer           = new Customer( get_current_user_id() );
		$user               = get_userdata( get_current_user_id() );
		$load_common_assets = false;

		/**
		 * Localize PHP variables to be used in the JS files
		 *
		 * @since 0.1
		 */
		$localized = array(
			'rest_base'       => easycommerce_rest_base(),
			'nonce'           => wp_create_nonce( 'wp_rest' ),
			'logout_url'      => wp_logout_url(),
			'ajax_url'        => admin_url( 'admin-ajax.php' ),
			'assets'          => EASYCOMMERCE_ASSETS_URL,
			'build'           => EASYCOMMERCE_BUILD_URL,
			'spa'             => EASYCOMMERCE_SPA_URL,
		);

		/**
		 * Assets that are required in the specific screens under `wp-admin` only
		 *
		 * @since 0.1
		 */
		if ( is_admin() ) {
            $load_common_assets = true;
			$this->enqueue_script(
				'easycommerce_admin',
				EASYCOMMERCE_ASSETS_URL . 'admin/js/init.js',
				array( 'easycommerce' )
			);

			$this->enqueue_style(
				'easycommerce_admin',
				EASYCOMMERCE_ASSETS_URL . 'admin/css/init.css',
			);
			
			$this->enqueue_style(
				'easycommerce-tailwind',
				EASYCOMMERCE_ASSETS_URL . 'common/css/tailwind.css'
			);
		}

		/**
		 * Assets that are required in the public-facing screens only
		 *
		 * @since 0.1
		 */
		elseif ( ! is_admin() ) {
			$load_common_assets = true;
			$this->enqueue_style(
				'style',
				EASYCOMMERCE_ASSETS_URL . 'public/css/style.css'
			);

			$this->enqueue_script(
				'easycommerce_public',
				EASYCOMMERCE_ASSETS_URL . 'public/js/init.js',
				array( 'easycommerce' )
			);
		}

		/**
		 * Assets that are required in both the `wp-admin` and public-facing screens
		 *
		 * @since 0.1
		 */
		if ( $load_common_assets ) {

			$this->enqueue_script(
				'easycommerce',
				EASYCOMMERCE_ASSETS_URL . 'common/js/init.js',
				array( 'jquery' )
			);

			$this->enqueue_style(
				'easycommerce',
				EASYCOMMERCE_ASSETS_URL . 'common/css/init.css'
			);

			$this->localize_script(
				'easycommerce',
				'EASYCOMMERCE',
				apply_filters( 'easycommerce-localized_vars', $localized )
			);
		}
	}
}