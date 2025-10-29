<?php
namespace EasyCommerce\Abstracts;

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
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'easycommerce_order_status', [$this, 'process_payment'], 10, 4 );
		add_action( "easycommerce-{$this->get_id()}_payment_complete", [$this, 'insert_transaction'], 10, 5 );
		add_action( "easycommerce_after_refund_order_{$this->get_id()}", array( $this, 'refund' ), 10, 3 );
		add_filter( 'easycommerce-localized_vars', [$this, 'localized'] );
		add_filter( "easycommerce_unset_payment_method_{$this->get_id()}", [ $this, 'check_for_physical_product' ], 10, 2 );
		add_filter( "easycommerce_supports_recurring", [ $this, 'supports_recurring' ], 10, 3 );
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
		add_filter( 'easycommerce_payment_methods', array( $this, 'add_payment_method' ) );
	}

	/**
	 * Add payment method to the available methods
	 *
	 * @param array $methods Existing payment methods.
	 * @return array Updated list of payment methods.
	 */
	public function add_payment_method( $methods ) {
		$methods[ $this->id ] = array(
			'id'          => $this->get_id(),
			'title'       => $this->get_title(),
			'description' => $this->get_description(),
			'status'      => $this->get_status(),
			'settings'    => $this->settings(),
			'form'        => $this->payment_form(),
			'icon'        => $this->get_icon(),
			'class'		  => get_class( $this ),
		);

		return $methods;
	}

	/**
	 * Enqueue payment method scripts
	 */
	public function enqueue_scripts() {}

	/**
	 * Localize payment method variables
	 *
	 * @param array $vars Existing localized variables.
	 * @return array Updated list of localized variables.
	 */
	public function localized( $vars ) {
		return $vars;
	}

	/**
	 * Display and save payment method settings
	 *
	 * @return array Payment method settings.
	 */
	public function settings() {
		return array();
	}

	/**
	 * Process payment
	 *
	 * @param string $status Payment status.
	 * @param int $order_id Order ID.
	 * @param array $params Payment parameters.
	 * @param int $customer_id Customer ID.
	 */

	public function process_payment( $status, $order_id, $params, $customer_id ) {
		return $status;
	}

	/**
	 * Process transaction
	 *
	 * @param string $transaction_id Transaction ID.
	 * @param int $order_id Order ID.
	 * @param float $total_amount Total amount.
	 * @param int $customer_id Customer ID.
	 * @param array $params Payment parameters.
	 */
	public function insert_transaction( $transaction_id, $order_id, $total_amount, $customer_id, $params ) {}

	/**
	 * Display payment form
	 *
	 * @return string Payment form HTML.
	 */
	public function payment_form() {
		return '';
	}

	/**
	 * Get payment method status
	 *
	 * @return bool Payment method status.
	 */
	public function get_status() {
		$status = get_option( 'easycommerce_payment_method_' . $this->id . '_status', false ) == 1;

		/**
		 * Filter the status of a specific payment method.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $status Payment method enabled status.
		 */
		return apply_filters( 'easycommerce_payment_method_' . $this->id . '_status', $status );
	}

	/**
	 * Get payment method ID
	 *
	 * @return string Payment method ID.
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get payment method title
	 *
	 * @return string Payment method title.
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Get payment method description
	 *
	 * @return string Payment method description.
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Get payment method icon
	 *
	 * @return string Payment method icon HTML or URL.
	 */
	public function get_icon() {
		/**
		 * Filter the payment method icon.
		 *
		 * @since 1.0.0
		 *
		 * @param string $icon Payment method icon HTML or URL.
		 */
		return apply_filters( 'easycommerce_payment_method_' . $this->id . '_icon', '' );
	}

	public function refund( $order_id, $reason , $amount ) {}
	
	/**
	 * Check for physical product
	 *
	 * @param bool $unset Unset flag.
	 * @param bool $has_physical Flag for physical product.
	 * @return bool Updated unset flag.
	 */

	public function check_for_physical_product( $unset, $has_physical ) {
		return $unset;
	}
	/**
	 * Supports recurring payments
	 *
	 * @param bool $supports Supports recurring payments.
	 * @return bool Updated supports flag.
	 * 
	 */
	public function supports_recurring( $supports, $payment_id, $cart ) {
		return $supports;
	}
}
