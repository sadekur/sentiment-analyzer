<?php
 /**
 * Clear sentiment cache
 */
function sa_clear_sentiment_cache() {
    global $wpdb;
    
    $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_sa_posts_%' OR option_name LIKE '_transient_timeout_sa_posts_%'" );
}

/**
 * Convert keyword string to array
 */
function sa_get_keywords_array( $keywords_string ) {
    if ( empty( $keywords_string ) ) {
        return array();
    }
    
    $keywords = array_map( 'trim', explode( ',', $keywords_string ) );
    return array_filter( $keywords );
}

/**
 * Count keyword matches in content
 */
function sa_count_keyword_matches( $content, $keywords ) {
    $count = 0;
    
    foreach ( $keywords as $keyword ) {
        $keyword = strtolower( trim( $keyword ) );
        if ( ! empty( $keyword ) ) {
            $count += substr_count( $content, $keyword );
        }
    }
    
    return $count;
}

/**
 * Get sentiment badge HTML
 */
function sa_get_sentiment_badge_html( $sentiment ) {
    $labels = array(
        'positive' => __( 'Positive', 'sentiment-analyzer' ),
        'negative' => __( 'Negative', 'sentiment-analyzer' ),
        'neutral' => __( 'Neutral', 'sentiment-analyzer' )
    );
    
    $label = isset( $labels[$sentiment] ) ? $labels[$sentiment] : $labels['neutral'];
    
    return sprintf(
        '<div class="sentiment-badge sentiment-badge-%s"><span class="sentiment-icon"></span><span class="sentiment-label">%s</span></div>',
        esc_attr( $sentiment ),
        esc_html( $label )
    );
}

function sa_get_setting( $key, $default = '' ) {
        $settings = get_option( 'sentiment_analyzer_settings', array() );
        return isset( $settings[$key] ) ? $settings[$key] : $default;
}

/**
 * Perform sentiment analysis on a post
 */
function sa_perform_sentiment_analysis( $post ) {
    $content = strtolower( $post->post_content . ' ' . $post->post_title );

    $positive_keywords = sa_get_keywords_array( sa_get_setting( 'positive_keywords', '' ) );
    $negative_keywords = sa_get_keywords_array( sa_get_setting( 'negative_keywords', '' ) );
    $neutral_keywords  = sa_get_keywords_array( sa_get_setting( 'neutral_keywords', '' ) );

    $positive_count = sa_count_keyword_matches( $content, $positive_keywords );
    $negative_count = sa_count_keyword_matches( $content, $negative_keywords );
    $neutral_count  = sa_count_keyword_matches( $content, $neutral_keywords );

    $sentiment = 'neutral';

    if ( $positive_count > 0 || $negative_count > 0 || $neutral_count > 0 ) {
        $max = max( $positive_count, $negative_count, $neutral_count );
        if ( $positive_count === $max ) $sentiment = 'positive';
        elseif ( $negative_count === $max ) $sentiment = 'negative';
    }

    update_post_meta( $post->ID, '_post_sentiment', sanitize_text_field( $sentiment ) );
    delete_transient( 'sa_posts_' . $sentiment );

    return $sentiment;
}