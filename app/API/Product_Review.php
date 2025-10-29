<?php
namespace EasyCommerce\API;

defined( 'ABSPATH' ) || exit;

use WP_REST_Request;
use WP_REST_Response;
use EasyCommerce\Traits\Rest;
use EasyCommerce\Models\Product_Review as Review_Model;
use EasyCommerce\Models\Product;

class Product_Review {

	use Rest;

	/**
	 * Get a list of product reviews.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response
	 */
	public function get_all( $request ) {
		$post_id 	= $request->get_param( 'product_id' );
		$user_id 	= $request->get_param( 'customer_id' );
		$status 	= $request->get_param( 'status' ) ?? null;
		$per_page 	= $request->get_param( 'per_page' ) ?: 10;
		$page 		= $request->get_param( 'page' ) ?: 1;
		$search 	= $request->get_param( 'search' );
		$order 		= $request->get_param( 'order' ) ?: 'DESC';

		$reviews = Review_Model::list( array(
			'post_id' 	=> $post_id,
			'user_id' 	=> $user_id,
			'status' 	=> $status,
			'search' 	=> $search,
			'order' 	=> $order,
			'per_page' 	=> $per_page,
			'page' 		=> $page
		) );

		return $this->response_success( $reviews );
	}

	/**
	 * Delete a product review.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response
	 */
	public function delete( $request ) {
		$id = $request->get_param( 'id' );

		if ( empty( $id ) ) {
			return $this->response_error( __( 'Review ID is required.', 'easycommerce' ), 400 );
		}

		$review = new Review_Model( $id );

		if ( ! $review->exists() ) {
			return $this->response_error( __( 'Review not found.', 'easycommerce' ), 404 );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return $this->response_error( __( 'You do not have permission to delete this review.', 'easycommerce' ), 403 );
		}

		$result = $review->delete();

		if ( ! $result ) {
			return $this->response_error( __( 'Failed to delete review.', 'easycommerce' ), 500 );
		}

		return $this->response_success(
			array(
				'message' => __( 'Review deleted successfully.', 'easycommerce' ),
			)
		);
	}
}