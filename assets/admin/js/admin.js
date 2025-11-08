
// Additional file: admin.js (place in the plugin directory)
jQuery(document).ready(function($) {
    $('#bulk-update-sentiment').on('click', function(e) {
        e.preventDefault();
        var status = $('#bulk-update-status');
        status.text('Updating...');

        $.ajax({
            url: SENTIMENT_ANALYZER.ajax_url,
            type: 'POST',
            data: {
                action: 'bulk_update_sentiment',
                nonce: SENTIMENT_ANALYZER.nonce
            },
            success: function(response) {
                if (response.success) {
                    status.text(response.data);
                } else {
                    status.text('Error: ' + response.data);
                }
            },
            error: function() {
                status.text('AJAX error');
            }
        });
    });
});