<?php
namespace EasyCommerce\Controllers\Common;

defined( 'ABSPATH' ) || exit;

use Content_Mood\Traits\Hook;

class Block {

	use Hook;

	public $categories = array();

	public $pattern_categories = array();

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
	}
}
