<?php
namespace Content_Mood_Analyzer\Abstracts;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract Payment Method Class
 */
abstract class Payment_Method {

	/**
	 * Payment method ID
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Payment method title
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * Payment method description
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * Constructor
	 *
	 * @param string $id
	 * @param string $title
	 * @param string $description
	 */
	public function __construct( $id, $title, $description = '' ) {
		$this->id          = $id;
		$this->title       = $title;
		$this->description = $description;

		// Hook to register the payment method during WordPress initialization
		add_action( 'init', array( $this, 'register_payment_method' ) );
	}

	/**
	 * Register payment method in the checkout form
	 */
	public function register_payment_method() {
		/**
		 * Filter to modify payment methods list
		 *
		 * @since 1.0.0
		 *
		 * @param array $methods List of registered payment methods.
		 */
		add_filter( 'content_mood_analyzer_payment_methods', array( $this, 'add_payment_method' ) );
	}
}
