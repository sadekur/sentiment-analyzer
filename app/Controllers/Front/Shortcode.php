<?php
namespace Sentiment\Controllers\Front;

defined( 'ABSPATH' ) || exit;

class Shortcode {

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		add_shortcode('sentiment_filter', array($this, 'sentiment_filter_shortcode'));
	}
}
