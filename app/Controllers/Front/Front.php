<?php
namespace Sentiment\Controllers\Front;

defined( 'ABSPATH' ) || exit;

class Front {

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		add_action('save_post', array($this, 'analyze_post_sentiment'), 10, 3);
        add_filter('the_content', array($this, 'add_sentiment_badge'));
        add_shortcode('sentiment_filter', array($this, 'sentiment_filter_shortcode'));
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