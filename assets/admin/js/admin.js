// assets/admin/js/admin.js
jQuery(document).ready(function($) {

    // Save Settings
    $('#sentiment-settings-form').on('submit', function(e) {
        e.preventDefault();
        var status = $('#save-status');
        status.text('Saving...').css('color', 'blue');

        $.ajax({
            url: SENTIMENT_ANALYZER.ajax_url,
            type: 'POST',
            data: {
                action: 'save_sentiment_settings',
                sentiment_nonce: $('#sentiment_nonce').val(),
                api_key: $('input[name="api_key"]').val(),
                model: $('select[name="model"]').val(),
                auto_analyze: $('input[name="auto_analyze"]').is(':checked') ? 1 : 0,
                cache_hours: $('input[name="cache_hours"]').val()
            },
            success: function(response) {
                if (response.success) {
                    status.text(response.data).css('color', 'green');
                } else {
                    status.text('Error: ' + response.data).css('color', 'red');
                }
            },
            error: function() {
                status.text('AJAX Error').css('color', 'red');
            }
        });
    });

    // Bulk Update
    $('#bulk-update-sentiment').on('click', function(e) {
        e.preventDefault();
        var status = $('#bulk-update-status');
        status.text('Processing...').css('color', 'blue');

        $.ajax({
            url: SENTIMENT_ANALYZER.ajax_url,
            type: 'POST',
            data: {
                action: 'bulk_update_sentiment',
                nonce: SENTIMENT_ANALYZER.nonce
            },
            success: function(response) {
                if (response.success) {
                    status.text(response.data).css('color', 'green');
                } else {
                    status.text('Error: ' + response.data).css('color', 'red');
                }
            },
            error: function() {
                status.text('AJAX Error').css('color', 'red');
            }
        });
    });

    // Clear Cache
    $('#clear-cache').on('click', function(e) {
        e.preventDefault();
        var status = $('#clear-cache-status');
        status.text('Clearing...').css('color', 'blue');

        $.ajax({
            url: SENTIMENT_ANALYZER.ajax_url,
            type: 'POST',
            data: {
                action: 'clear_sentiment_cache',
                nonce: SENTIMENT_ANALYZER.nonce
            },
            success: function(response) {
                if (response.success) {
                    status.text(response.data).css('color', 'green');
                } else {
                    status.text('Error: ' + response.data).css('color', 'red');
                }
            }
        });
    });

});