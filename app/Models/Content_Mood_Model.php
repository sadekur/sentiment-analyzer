<?php
namespace Content_Mood\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Class Content_Mood_Model
 * Handles content mood data model operations.
 *
 * @package Content_Mood\Models
 */
class Content_Mood_Model {

    /**
     * Get posts with filters
     *
     * @param array $args Filters
     * @param int $per_page Posts per page
     * @param int $offset Offset for pagination
     * @param string $sort Sort order (asc or desc)
     * @return array
     */
    public static function list( $args = array(), $per_page = 2, $offset = 0, $sort = 'desc' ) {
        $sentiment  = isset( $args['sentiment'] ) ? $args['sentiment'] : null;
        $from_date  = isset( $args['from_date'] ) ? trim( $args['from_date'] ) : null;
        $to_date    = isset( $args['to_date'] ) ? trim( $args['to_date'] ) : null;

        $query_args = array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'offset'         => $offset,
            'order'          => strtoupper( $sort ),
            'orderby'        => 'date',
        );

        // Add sentiment filter
        if ( ! empty( $sentiment ) ) {
            $query_args['meta_query'] = array(
                array(
                    'key'     => '_post_sentiment',
                    'value'   => $sentiment,
                    'compare' => '=',
                ),
            );
        } else {
            // If no sentiment specified, get all posts with any sentiment
            $query_args['meta_query'] = array(
                array(
                    'key'     => '_post_sentiment',
                    'compare' => 'EXISTS',
                ),
            );
        }

        // Add date filters
        if ( ! empty( $from_date ) || ! empty( $to_date ) ) {
            $date_query = array();

            if ( ! empty( $from_date ) ) {
                $from_date = date( 'Y-m-d 00:00:00', strtotime( $from_date ) );
                $date_query['after'] = $from_date;
            }

            if ( ! empty( $to_date ) ) {
                $to_date = date( 'Y-m-d 23:59:59', strtotime( $to_date ) );
                $date_query['before'] = $to_date;
            }

            $date_query['inclusive'] = true;
            $query_args['date_query'] = array( $date_query );
        }

        // Get total count (without pagination)
        $count_args = $query_args;
        $count_args['posts_per_page'] = -1;
        $count_args['fields'] = 'ids';
        $count_query = new \WP_Query( $count_args );
        $total = $count_query->found_posts;

        // Get posts with pagination
        $query = new \WP_Query( $query_args );
        $posts = $query->posts;

        wp_reset_postdata();

        return array(
            'posts' => $posts,
            'total' => $total,
        );
    }

    /**
     * Get sentiment counts for all types
     *
     * @return array
     */
    public static function get_sentiment_counts() {
        global $wpdb;

        $sentiments = array( 'positive', 'neutral', 'negative' );
        $counts = array(
            'all' => 0,
        );

        foreach ( $sentiments as $sentiment ) {
            $count = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(DISTINCT pm.post_id)
                FROM {$wpdb->postmeta} pm
                INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                WHERE pm.meta_key = '_post_sentiment'
                AND pm.meta_value = %s
                AND p.post_status = 'publish'
                AND p.post_type = 'post'",
                $sentiment
            ) );
            
            $counts[ $sentiment ] = $count;
            $counts['all'] += $count;
        }

        return $counts;
    }

    /**
     * Get a single post by ID
     *
     * @param int $post_id
     * @return \WP_Post|null
     */
    public static function get( $post_id ) {
        $post = get_post( $post_id );
        
        if ( ! $post || $post->post_status !== 'publish' || $post->post_type !== 'post' ) {
            return null;
        }

        return $post;
    }

    /**
     * Clear sentiment cache
     *
     * @return void
     */
    // public static function clear_cache() {
    //     global $wpdb;
        
    //     // Delete all sentiment-related transients
    //     $wpdb->query(
    //         "DELETE FROM {$wpdb->options} 
    //         WHERE option_name LIKE '_transient_cma_posts_%' 
    //         OR option_name LIKE '_transient_timeout_cma_posts_%'"
    //     );
    // }
}