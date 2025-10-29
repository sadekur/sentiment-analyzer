<?php
namespace EasyCommerce\Controllers\Common;

defined( 'ABSPATH' ) || exit;

use WP_REST_Server;
use EasyCommerce\API\Product_Review;
use EasyCommerce\Traits\Hook;
use EasyCommerce\Traits\Rest;
use EasyCommerce\Traits\Auth;

class API {

	use Hook;
	use Auth;
	use Rest;

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		$this->action( 'rest_api_init', array( $this, 'register_endpoints' ) );
	}

	/**
	 * Register all API endpoints
	 */
	public function register_endpoints() {
		$this->register_product_review_endpoints();
	}

	/**
	 * Register Product Review-related API endpoints
	 */
	private function register_product_review_endpoints() {
		$review_controller = new Product_Review();

		$this->register_route(
			'/product-reviews',
			array(
				'methods'    => WP_REST_Server::READABLE,
				'callback'   => array( $review_controller, 'get_all' ),
				'args'     => array(
					'page' => array(
						'description' => __( 'The page number', 'easycommerce' ),
						'type'        => 'integer',
						'default'     => 1,
					),
					'per_page' => array(
						'description' => __( 'Reviews per page', 'easycommerce' ),
						'type'        => 'integer',
						'default'     => 10,
					),
					'product_id' => array(
						'type' => 'integer',
					),
					'customer_id' => array(
						'type' => 'integer',
					),
					'status' => array(
						'type'    => 'string',
						'default' => 'approve',
					),
					'search' => array(
						'description' => __( 'The search query', 'easycommerce' ),
						'required'    => false,
						'type'        => 'string',
					),
				),
				'permission' => array( $this, 'is_user' ),
			)
		);

		// Delete a product review
		$this->register_route(
			'/product-reviews/(?P<id>\\d+)',
			array(
				'methods'    => WP_REST_Server::DELETABLE,
				'callback'   => array( $review_controller, 'delete' ),
				'args'       => array(
					'id' => array(
						'description' => __( 'The review ID', 'easycommerce' ),
						'required'    => true,
						'type'        => 'integer',
					),
				),
				'permission' => array( $this, 'is_admin' ),
			)
		);
	}
}