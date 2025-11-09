<?php
namespace Sentiment\Controllers\Front;

defined( 'ABSPATH' ) || exit;

class Front {

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		// Auto-analyze on save (optional - can be disabled if using API only)
        add_action('save_post', array($this, 'analyze_post_sentiment'), 10, 3);
        
        // Display badge on frontend
        add_filter('the_content', array($this, 'add_sentiment_badge'));
        
        // Enqueue frontend scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
	}

	/**
	 * Enqueue frontend scripts for AJAX functionality
	 */
	public function enqueue_frontend_scripts() {
		// Only enqueue on single posts
		if (!is_singular('post')) {
			return;
		}

		wp_enqueue_script(
			'sentiment-analyzer-front',
			plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/js/frontend.js',
			array('jquery'),
			'1.0.0',
			true
		);

		wp_localize_script('sentiment-analyzer-front', 'sentimentAnalyzerFront', array(
			'apiUrl' => rest_url('sentiment-analyzer/v1'),
			'nonce' => wp_create_nonce('wp_rest'),
			'postId' => get_the_ID(),
		));
	}

    /**
     * Analyze post sentiment (hooks into save_post)
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
        
        // Perform the analysis
        $this->perform_analysis($post);
    }

    /**
     * Perform sentiment analysis
     */
    private function perform_analysis($post) {
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
        update_post_meta($post->ID, '_post_sentiment', sanitize_text_field($sentiment));
        
        // Store analysis metadata
        update_post_meta($post->ID, '_post_sentiment_counts', array(
            'positive' => $positive_count,
            'negative' => $negative_count,
            'neutral' => $neutral_count,
        ));
        
        update_post_meta($post->ID, '_post_sentiment_analyzed_at', current_time('mysql'));
        
        // Clear relevant caches
        delete_transient('sa_posts_' . $sentiment);
        
        return $sentiment;
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
            // Analyze if not yet analyzed
            $sentiment = $this->perform_analysis($post);
        }
        
        $badge = sa_get_sentiment_badge_html($sentiment);
        
        $position = get_option('sa_badge_position', 'top');
        
        if ($position === 'top') {
            return $badge . $content;
        } else {
            return $content . $badge;
        }
    }

    /**
     * Get sentiment via AJAX (can be called from frontend)
     */
    public function ajax_get_sentiment() {
        check_ajax_referer('sentiment_analyzer_nonce', 'nonce');
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error(array('message' => 'Invalid post ID'));
        }
        
        $sentiment = get_post_meta($post_id, '_post_sentiment', true);
        
        if (empty($sentiment)) {
            $post = get_post($post_id);
            if ($post) {
                $sentiment = $this->perform_analysis($post);
            } else {
                wp_send_json_error(array('message' => 'Post not found'));
            }
        }
        
        wp_send_json_success(array(
            'sentiment' => $sentiment,
            'badge_html' => sa_get_sentiment_badge_html($sentiment),
        ));
    }
}