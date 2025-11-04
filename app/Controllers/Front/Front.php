  add_action('save_post', array($this, 'analyze_post_sentiment'), 10, 3);
        add_filter('the_content', array($this, 'add_sentiment_badge'));
        add_shortcode('sentiment_filter', array($this, 'sentiment_filter_shortcode'));