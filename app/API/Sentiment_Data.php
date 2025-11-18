<?php
namespace Sentiment\API;

defined( 'ABSPATH' ) || exit;

use Sentiment\Traits\Rest;

class Sentiment_Data {

	use Rest;
 	/**
     * Analyze a single post
     */

	private $option_name = 'sentiment_analyzer_settings';

	
    /**
     * Update plugin settings
     */
    public function update_settings( $request ) {
        $current = get_option( $this->option_name, array() );

        $defaults = array(
            'positive_keywords' => '',
            'negative_keywords' => '',
            'neutral_keywords'  => '',
            'badge_position'    => 'top',
        );

        $current = wp_parse_args( $current, $defaults );

        $updated_fields = array();

        // Update only sent fields
        if ( $request->has_param('positive_keywords' ) ) {
            $current['positive_keywords'] = $request->get_param( 'positive_keywords' );
            $updated_fields[] = 'positive_keywords';
        }

        if ( $request->has_param( 'negative_keywords' ) ) {
            $current['negative_keywords'] = $request->get_param( 'negative_keywords' );
            $updated_fields[] = 'negative_keywords';
        }

        if ( $request->has_param( 'neutral_keywords' ) ) {
            $current['neutral_keywords'] = $request->get_param( 'neutral_keywords' );
            $updated_fields[] = 'neutral_keywords';
        }

        if ( $request->has_param( 'badge_position' ) ) {
            $current['badge_position'] = $request->get_param( 'badge_position' );
            $updated_fields[] = 'badge_position';
        }

        // Save as single array
        $saved = update_option( $this->option_name, $current );

        if ( $saved ) {
            if ( in_array( 'positive_keywords', $updated_fields ) ||
                in_array( 'negative_keywords', $updated_fields ) ||
                in_array( 'neutral_keywords', $updated_fields ) ) {
                sa_clear_sentiment_cache();
            }

            return rest_ensure_response( array(
                'success' => true,
                'updated' => $updated_fields,
                'settings' => $current,
                'message' => __( 'Settings saved successfully.', 'sentiment-analyzer' ),
            ) );
        }

        return new \WP_Error(
            'save_failed',
            __( 'Failed to save settings.', 'sentiment-analyzer' ),
            array( 'status' => 500 )
        );
    }

    public function analyze_post( $request ) {
        $post_id 	= $request->get_param( 'id' );
        $post 		= get_post( $post_id );

        if ( ! $post ) {
            return new \WP_Error(
                'post_not_found',
                __( 'Post not found.', 'sentiment-analyzer' ),
                array( 'status' => 404 )
            );
        }

        if ( $post->post_type !== 'post' ) {
            return new \WP_Error(
                'invalid_post_type',
                __( 'Only posts can be analyzed.', 'sentiment-analyzer' ),
                array( 'status' => 400 )
            );
        }

        // Analyze sentiment
        $sentiment = sa_perform_sentiment_analysis( $post );

        return rest_ensure_response( array(
            'success' => true,
            'post_id' => $post_id,
            'sentiment' => $sentiment,
            'message' => __( 'Post analyzed successfully.', 'sentiment-analyzer' ),
        ) );
    }

    /**
     * Bulk analyze all posts
     */
    public function bulk_analyze( $request ) {
        // Get all published posts
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        );

        $post_ids = get_posts( $args );
        $analyzed = 0;
        $results = array();

        foreach ( $post_ids as $post_id ) {
            $post = get_post( $post_id );
            if ( $post ) {
                $sentiment = sa_perform_sentiment_analysis( $post );
                $results[] = array(
                    'post_id' => $post_id,
                    'sentiment' => $sentiment,
                );
                $analyzed++;
            }
        }

        // Clear all caches
        sa_clear_sentiment_cache();

        return rest_ensure_response( array(
            'success' => true,
            'analyzed' => $analyzed,
            'total' => count( $post_ids ),
            'results' => $results,
            'message' => sprintf(
                __( 'Analyzed %d posts successfully.', 'sentiment-analyzer' ),
                $analyzed
            ),
        ));
    }

    /**
     * Clear all sentiment caches
     */
    public function clear_cache( $request ) {
        sa_clear_sentiment_cache();

        return rest_ensure_response( array(
            'success' => true,
            'message' => __( 'Cache cleared successfully.', 'sentiment-analyzer' ),
        ) );
    }

    /**
     * Get posts by sentiment
     */
    public function get_posts_by_sentiment( $request ) {
        $sentiment      = $request->get_param( 'sentiment' );
        $page           = $request->get_param( 'page' );
        $per_page       = $request->get_param( 'per_page' );
        $cache_key      = 'sa_posts_' . $sentiment . '_page_' . $page . '_per_' . $per_page;
        $cached_data    = get_transient( $cache_key );

        if ( $cached_data !== false ) {
            return rest_ensure_response( $cached_data );
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

        $query = new \WP_Query( $args );
        $posts = array();

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $posts[] = array(
                    'id'        => get_the_ID(),
                    'title'     => get_the_title(),
                    'excerpt'   => get_the_excerpt(),
                    'permalink' => get_permalink(),
                    'date'      => get_the_date(),
                    'sentiment' => $sentiment,
                );
            }
            wp_reset_postdata();
        }

        $response_data = array(
            'success'       => true,
            'posts'         => $posts,
            'total'         => $query->found_posts,
            'pages'         => $query->max_num_pages,
            'current_page'  => $page,
            'per_page'      => $per_page,
        );

        // Cache for 1 hour
        set_transient( $cache_key, $response_data, HOUR_IN_SECONDS );

        return rest_ensure_response( $response_data );
    }

    /**
     * Get plugin settings
     */
    public function get_settings( $request ) {
        $settings = get_option( $this->option_name, array() );

        $defaults = array(
            'positive_keywords' => '',
            'negative_keywords' => '',
            'neutral_keywords'  => '',
            'badge_position'    => 'top',
        );

        $settings = wp_parse_args( $settings, $defaults );

        return rest_ensure_response( array(
            'success' => true,
            'settings' => $settings,
        ) );
    }
}