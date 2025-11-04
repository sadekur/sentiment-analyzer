<?php
namespace Sentiment\Controllers\Common;

defined( 'ABSPATH' ) || exit;

class Activation {

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
	}

	/**
     * Plugin activation
     */
    public function activate() {
        // Set default keyword lists if not exists
        if (!get_option('sa_positive_keywords')) {
            update_option('sa_positive_keywords', "good, great, excellent, amazing, wonderful, fantastic, love, happy, perfect, best, awesome, brilliant, outstanding, superb, terrific");
        }
        
        if (!get_option('sa_negative_keywords')) {
            update_option('sa_negative_keywords', "bad, terrible, awful, horrible, poor, worst, hate, disappointing, disappointing, fail, failed, useless, pathetic, disaster, garbage");
        }
        
        if (!get_option('sa_neutral_keywords')) {
            update_option('sa_neutral_keywords', "okay, ok, average, decent, fair, moderate, acceptable, reasonable, standard, normal");
        }
        
        // Set default badge position
        if (!get_option('sa_badge_position')) {
            update_option('sa_badge_position', 'top');
        }
        
        flush_rewrite_rules();
    }

	/**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear all sentiment transients
        sa_clear_sentiment_cache();
        flush_rewrite_rules();
    }
}