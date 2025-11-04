<?php
 /**
     * Clear sentiment cache
     */
    function sa_clear_sentiment_cache() {
        global $wpdb;
        
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_sa_posts_%' OR option_name LIKE '_transient_timeout_sa_posts_%'");
    }