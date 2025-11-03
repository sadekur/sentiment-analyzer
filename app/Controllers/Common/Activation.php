<?php
namespace Sentiment\Analyzer\Controllers\Common;

defined( 'ABSPATH' ) || exit;

class Activation {

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
	}
}