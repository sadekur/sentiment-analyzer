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
     * Analyze post sentiment
     */
    public function analyze_post_sentiment($post_id, $post, $update) {
        // Skip auto-saves and revisions
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (wp_is_post_revision($post_id)) {
            return;
        }
        
        // Only analyze posts
        if ($post->post_type !== 'post') {
            return;
        }
        
        // Get post content
        $content = strtolower($post->post_content . ' ' . $post->post_title);
        
        // Get keyword lists
        $positive_keywords = sa_get_keywords_array(get_option('sa_positive_keywords', ''));
        $negative_keywords = sa_get_keywords_array(get_option('sa_negative_keywords', ''));
        $neutral_keywords = sa_get_keywords_array(get_option('sa_neutral_keywords', ''));
        
        // Count keyword matches
        $positive_count = sa_count_keyword_matches($content, $positive_keywords);
        $negative_count = sa_count_keyword_matches($content, $negative_keywords);
        $neutral_count = sa_count_keyword_matches($content, $neutral_keywords);
        
        // Determine sentiment
        $sentiment = 'neutral'; // Default
        
        if ($positive_count > 0 || $negative_count > 0 || $neutral_count > 0) {
            $max_count = max($positive_count, $negative_count, $neutral_count);
            
            if ($positive_count === $max_count) {
                $sentiment = 'positive';
            } elseif ($negative_count === $max_count) {
                $sentiment = 'negative';
            } else {
                $sentiment = 'neutral';
            }
        }
        
        // Store sentiment in post meta
        update_post_meta($post_id, '_post_sentiment', sanitize_text_field($sentiment));
        
        // Clear relevant caches
        delete_transient('sa_posts_' . $sentiment);
    }

    /**
     * Add sentiment badge to content
     */
    public function add_sentiment_badge($content) {
        if (!is_singular('post')) {
            return $content;
        }
        
        global $post;
        $sentiment = get_post_meta($post->ID, '_post_sentiment', true);
        
        if (empty($sentiment)) {
            $sentiment = 'neutral';
        }
        
        $badge = sa_get_sentiment_badge_html($sentiment);
        
        $position = get_option('sa_badge_position', 'top');
        
        if ($position === 'top') {
            return $badge . $content;
        } else {
            return $content . $badge;
        }
    }
}