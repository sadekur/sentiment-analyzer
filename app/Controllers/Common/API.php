<?php
namespace Sentiment\Controllers\Common;
use Sentiment\API\Sentiment_Data;
use Sentiment\Traits\Rest;

defined( 'ABSPATH' ) || exit;

class API {


	use Rest;

    /**
     * Namespace for the API
     */
    // private $namespace = 'sentiment-analyzer/v1';

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
        // Update settings
		$this->register_route(
            '/settings',
            array(
                'methods' => 'POST',
                'callback' => array( new Sentiment_Data(), 'update_settings' ),
                'permission_callback' => array( $this, 'check_permission' ),
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
                        'validate_callback' => function( $param ) {
                            return in_array( $param, array( 'top', 'bottom', 'none' ) );
                        }
                    ),
                ),
            )
        );

        // Get settings
		register_rest_route( $this->namespace, '/settings', array(
            'methods' => 'GET',
            'callback' => array( new Sentiment_Data(), 'get_settings' ),
            'permission_callback' => array( $this, 'check_permission' ),
        ));

        // Analyze single post
        register_rest_route( $this->namespace, '/analyze/(?P<id>\d+)', array(
            'methods' => 'POST',
            'callback'   => array( new Sentiment_Data(), 'analyze_post' ),
            'permission_callback' => array( $this, 'check_permission' ),
            'args' => array(
                'id' => array(
                    'validate_callback' => function( $param ) {
                        return is_numeric( $param );
                    }
                ),
            ),
        ));

        // Bulk analyze all posts
        register_rest_route($this->namespace, '/analyze/bulk', array(
            'methods' => 'POST',
			'callback'   => array( new Sentiment_Data(), 'bulk_analyze' ),
            'permission_callback' => array($this, 'check_permission'),
        ));

        // Get post sentiment
        register_rest_route( $this->namespace, '/sentiment/(?P<id>\d+)', array(
            'methods' => 'GET',
			'callback' => array( new Sentiment_Data(), 'get_sentiment' ),
            'permission_callback' => '__return_true',
            'args' => array(
                'id' => array(
                    'validate_callback' => function( $param ) {
                        return is_numeric( $param );
                    }
                ),
            ),
        ) );

        // Clear cache
        register_rest_route(
            $this->namespace,
            '/cache/clear', array(
            'methods' => 'POST',
			'callback'   => array( new Sentiment_Data(), 'clear_cache' ),
            'permission_callback' => array( $this, 'check_permission' ),
        ));

        // Get posts by sentiment
        $this->register_route(
            '/posts/(?P<sentiment>positive|negative|neutral )', array(
            'methods' => 'GET',
			'callback' => array( new Sentiment_Data(), 'get_posts_by_sentiment' ),
            'permission_callback' => '__return_true',
            'args' => array(
                'sentiment' => array(
                    'validate_callback' => function( $param ) {
                        return in_array( $param, array( 'positive', 'negative', 'neutral' ) );
                    }
                ),
                'page' => array(
                    'default' => 1,
                    'validate_callback' => function( $param ) {
                        return is_numeric( $param ) && $param > 0;
                    }
                ),
                'per_page' => array(
                    'default' => 10,
                    'validate_callback' => function( $param ) {
                        return is_numeric( $param ) && $param > 0 && $param <= 100;
                    }
                ),
            ),
        ) );
    }

    /**
     * Check if user has permission
     */
    public function check_permission() {
        return current_user_can( 'manage_options' );
    }
}