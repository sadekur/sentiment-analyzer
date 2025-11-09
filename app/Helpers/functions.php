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