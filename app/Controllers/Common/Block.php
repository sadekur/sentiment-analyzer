<?php
namespace EasyCommerce\Controllers\Common;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Traits\Hook;
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Traits\Asset;
use ParagonIE\Sodium\Core\Util;

class Block {

	use Hook;
	use Asset;

	public $categories = array();

	public $pattern_categories = array();

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {

		$this->categories = array(
			'shop'      => __( 'EasyCommerce - Shop', 'easycommerce' ),
			'product'   => __( 'EasyCommerce - Product', 'easycommerce' ),
			'checkout'  => __( 'EasyCommerce - Checkout', 'easycommerce' ),
			'dashboard' => __( 'EasyCommerce - Dashboard', 'easycommerce' ),
		);

		$this->filter( 'init', array( $this, 'register' ) );
		$this->filter( 'block_categories_all', array( $this, 'register_category' ) );
		$this->action( 'init', array( $this, 'register_patterns' ) );
		$this->action( 'init', array( $this, 'register_pattern_categories' ) );
	}

	public function register() {
		$blocks_dir = EASYCOMMERCE_PLUGIN_DIR . 'blocks/';
		$categories = glob( $blocks_dir . '*', GLOB_ONLYDIR );

		foreach ( $categories as $category ) {
			$category_name = basename( $category );
			$blocks        = glob( $category . '/*', GLOB_ONLYDIR );

			foreach ( $blocks as $block ) {
				$block_name = basename( $block );
				$block_type = "{$category_name}/{$block_name}";

				register_block_type( $block );
			}
		}
	}

	/**
	 * Register custom block categories.
	 *
	 * @param array $categories Existing block categories.
	 * @return array Updated block categories.
	 */
	public function register_category( $categories ) {
		$new_categories = array();

		foreach ( $this->categories as $id => $label ) {
			$new_categories[] = array(
				'slug'  => "easycommerce-{$id}",
				'title' => $label,
			);
		}

		return array_merge( $new_categories, $categories );
	}

	/**
	 * Registers patterns
	 */
	public function register_patterns() {

		register_block_pattern(
			'easycommerce/single-product-1',
			array(
				'title'       => __( 'Single Product 1', 'easycommerce' ),
				'description' => _x( 'A pattern that includes all checkout blocks', 'Block pattern description', 'easycommerce' ),
				'categories'  => array( 'easycommerce' ),
				'content'     => Utility::get_template( 'patterns/single-product/template-1.php' ),
			)
		);

		register_block_pattern(
			'easycommerce/single-product-2',
			array(
				'title'       => __( 'Single Product 2', 'easycommerce' ),
				'description' => _x( 'A pattern that includes all checkout blocks', 'Block pattern description', 'easycommerce' ),
				'categories'  => array( 'easycommerce' ),
				'content'     => Utility::get_template( 'patterns/single-product/template-2.php' ),
			)
		);
	}

	/**
	 * Registers pattern categories
	 */
	public function register_pattern_categories() {
		register_block_pattern_category(
			'easycommerce',
			array( 'label' => __( 'EasyCommerce', 'easycommerce' ) )
		);
	}
}
