<?php
namespace Sentiment\Controllers\Common;

defined( 'ABSPATH' ) || exit;

class API {

    /**
     * Namespace for the API
     */
    private $namespace = 'sentiment-analyzer/v1';

    /**
     * Constructor to register routes
     */
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    /**
     * Register all REST API routes
     */
    public function register_routes() {
        // Analyze single post
        register_rest_route($this->namespace, '/analyze/(?P<id>\d+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'analyze_post'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ),
            ),
        ));

        // Bulk analyze all posts
        register_rest_route($this->namespace, '/analyze/bulk', array(
            'methods' => 'POST',
            'callback' => array($this, 'bulk_analyze'),
            'permission_callback' => array($this, 'check_permission'),
        ));

        // Get post sentiment
        register_rest_route($this->namespace, '/sentiment/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_sentiment'),
            'permission_callback' => '__return_true',
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ),
            ),
        ));

        // Clear cache
        register_rest_route($this->namespace, '/cache/clear', array(
            'methods' => 'POST',
            'callback' => array($this, 'clear_cache'),
            'permission_callback' => array($this, 'check_permission'),
        ));

        // Get posts by sentiment
        register_rest_route($this->namespace, '/posts/(?P<sentiment>positive|negative|neutral)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_posts_by_sentiment'),
            'permission_callback' => '__return_true',
            'args' => array(
                'sentiment' => array(
                    'validate_callback' => function($param) {
                        return in_array($param, array('positive', 'negative', 'neutral'));
                    }
                ),
                'page' => array(
                    'default' => 1,
                    'validate_callback' => function($param) {
                        return is_numeric($param) && $param > 0;
                    }
                ),
                'per_page' => array(
                    'default' => 10,
                    'validate_callback' => function($param) {
                        return is_numeric($param) && $param > 0 && $param <= 100;
                    }
                ),
            ),
        ));

        // Update settings
        register_rest_route($this->namespace, '/settings', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_settings'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'positive_keywords' => array(
                    'sanitize_callback' => 'sanitize_textarea_field',
                ),
                'negative_keywords' => array(
                    'sanitize_callback' => 'sanitize_textarea_field',
                ),
                'neutral_keywords' => array(
                    'sanitize_callback' => 'sanitize_textarea_field',
                ),
                'badge_position' => array(
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => function($param) {
                        return in_array($param, array('top', 'bottom'));
                    }
                ),
            ),
        ));

        // Get settings
        register_rest_route($this->namespace, '/settings', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_settings'),
            'permission_callback' => array($this, 'check_permission'),
        ));
    }

    /**
     * Check if user has permission
     */
    public function check_permission() {
        return current_user_can('manage_options');
    }

    /**
     * Analyze a single post
     */
    public function analyze_post($request) {
        $post_id = $request->get_param('id');
        $post = get_post($post_id);

        if (!$post) {
            return new \WP_Error(
                'post_not_found',
                __('Post not found.', 'sentiment-analyzer'),
                array('status' => 404)
            );
        }

        if ($post->post_type !== 'post') {
            return new \WP_Error(
                'invalid_post_type',
                __('Only posts can be analyzed.', 'sentiment-analyzer'),
                array('status' => 400)
            );
        }

        // Analyze sentiment
        $sentiment = $this->perform_sentiment_analysis($post);

        return rest_ensure_response(array(
            'success' => true,
            'post_id' => $post_id,
            'sentiment' => $sentiment,
            'message' => __('Post analyzed successfully.', 'sentiment-analyzer'),
        ));
    }

    /**
     * Bulk analyze all posts
     */
    public function bulk_analyze($request) {
        // Get all published posts
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        );

        $post_ids = get_posts($args);
        $analyzed = 0;
        $results = array();

        foreach ($post_ids as $post_id) {
            $post = get_post($post_id);
            if ($post) {
                $sentiment = $this->perform_sentiment_analysis($post);
                $results[] = array(
                    'post_id' => $post_id,
                    'sentiment' => $sentiment,
                );
                $analyzed++;
            }
        }

        // Clear all caches
        sa_clear_sentiment_cache();

        return rest_ensure_response(array(
            'success' => true,
            'analyzed' => $analyzed,
            'total' => count($post_ids),
            'results' => $results,
            'message' => sprintf(
                __('Analyzed %d posts successfully.', 'sentiment-analyzer'),
                $analyzed
            ),
        ));
    }

    /**
     * Get sentiment for a post
     */
    public function get_sentiment($request) {
        $post_id = $request->get_param('id');
        $post = get_post($post_id);

        if (!$post) {
            return new \WP_Error(
                'post_not_found',
                __('Post not found.', 'sentiment-analyzer'),
                array('status' => 404)
            );
        }

        $sentiment = get_post_meta($post_id, '_post_sentiment', true);

        if (empty($sentiment)) {
            $sentiment = 'neutral';
        }

        return rest_ensure_response(array(
            'success' => true,
            'post_id' => $post_id,
            'sentiment' => $sentiment,
            'badge_html' => sa_get_sentiment_badge_html($sentiment),
        ));
    }

    /**
     * Clear all sentiment caches
     */
    public function clear_cache($request) {
        sa_clear_sentiment_cache();

        return rest_ensure_response(array(
            'success' => true,
            'message' => __('Cache cleared successfully.', 'sentiment-analyzer'),
        ));
    }

    /**
     * Get posts by sentiment
     */
    public function get_posts_by_sentiment($request) {
        $sentiment = $request->get_param('sentiment');
        $page = $request->get_param('page');
        $per_page = $request->get_param('per_page');

        // Check cache
        $cache_key = 'sa_posts_' . $sentiment . '_page_' . $page . '_per_' . $per_page;
        $cached_data = get_transient($cache_key);

        if ($cached_data !== false) {
            return rest_ensure_response($cached_data);
        }

        // Query posts
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'meta_query' => array(
                array(
                    'key' => '_post_sentiment',
                    'value' => $sentiment,
                    'compare' => '='
                )
            )
        );

        $query = new \WP_Query($args);
        $posts = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $posts[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'excerpt' => get_the_excerpt(),
                    'permalink' => get_permalink(),
                    'date' => get_the_date(),
                    'sentiment' => $sentiment,
                );
            }
            wp_reset_postdata();
        }

        $response_data = array(
            'success' => true,
            'posts' => $posts,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
            'current_page' => $page,
            'per_page' => $per_page,
        );

        // Cache for 1 hour
        set_transient($cache_key, $response_data, HOUR_IN_SECONDS);

        return rest_ensure_response($response_data);
    }

    /**
     * Update plugin settings
     */
    public function update_settings($request) {
        $updated = array();

        if ($request->has_param('positive_keywords')) {
            update_option('sa_positive_keywords', $request->get_param('positive_keywords'));
            $updated[] = 'positive_keywords';
        }

        if ($request->has_param('negative_keywords')) {
            update_option('sa_negative_keywords', $request->get_param('negative_keywords'));
            $updated[] = 'negative_keywords';
        }

        if ($request->has_param('neutral_keywords')) {
            update_option('sa_neutral_keywords', $request->get_param('neutral_keywords'));
            $updated[] = 'neutral_keywords';
        }

        if ($request->has_param('badge_position')) {
            update_option('sa_badge_position', $request->get_param('badge_position'));
            $updated[] = 'badge_position';
        }

        return rest_ensure_response(array(
            'success' => true,
            'updated' => $updated,
            'message' => __('Settings updated successfully.', 'sentiment-analyzer'),
        ));
    }

    /**
     * Get plugin settings
     */
    public function get_settings($request) {
        return rest_ensure_response(array(
            'success' => true,
            'settings' => array(
                'positive_keywords' => get_option('sa_positive_keywords', ''),
                'negative_keywords' => get_option('sa_negative_keywords', ''),
                'neutral_keywords' => get_option('sa_neutral_keywords', ''),
                'badge_position' => get_option('sa_badge_position', 'top'),
            ),
        ));
    }

    /**
     * Perform sentiment analysis on a post
     */
    private function perform_sentiment_analysis($post) {
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

        // Clear relevant caches
        delete_transient('sa_posts_' . $sentiment);

        return $sentiment;
    }
}