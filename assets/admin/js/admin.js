(function($) {
    'use strict';

    $(document).ready(function() {
        
        /**
         * Bulk Update Sentiment
         */
        $('#bulk-update-sentiment').on('click', function(e) {
            e.preventDefault();
            
            const button = $(this);
            const progressContainer = $('#bulk-update-progress');
            const progressBar = progressContainer.find('.sa-progress-fill');
            const progressText = progressContainer.find('.sa-progress-text');
            const statusDiv = $('#bulk-update-status');
            
            // Confirm action
            if (!confirm(SENTIMENT_ANALYZER.strings.confirm)) {
                return;
            }
            
            // Disable button and show progress
            button.prop('disabled', true);
            progressContainer.show();
            statusDiv.html('');
            progressBar.css('width', '0%');
            progressText.text(SENTIMENT_ANALYZER.strings.bulkUpdating);
            
            // Make API call
            $.ajax({
                url: SENTIMENT_ANALYZER.apiUrl + '/analyze/bulk',
                method: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', SENTIMENT_ANALYZER.nonce);
                },
                success: function(response) {
                    progressBar.css('width', '100%');
                    
                    if (response.success) {
                        const message = SENTIMENT_ANALYZER.strings.bulkSuccess.replace('{count}', response.analyzed);
                        progressText.text(message);
                        
                        statusDiv.html(
                            '<div class="notice notice-success inline">' +
                            '<p><strong>' + message + '</strong></p>' +
                            '<p>' + response.analyzed + ' of ' + response.total + ' posts analyzed.</p>' +
                            '</div>'
                        );
                        
                        // Hide progress after 3 seconds
                        setTimeout(function() {
                            progressContainer.fadeOut();
                        }, 3000);
                    } else {
                        showError(statusDiv, SENTIMENT_ANALYZER.strings.bulkError);
                        progressContainer.hide();
                    }
                },
                error: function(xhr) {
                    let errorMessage = SENTIMENT_ANALYZER.strings.bulkError;
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    showError(statusDiv, errorMessage);
                    progressContainer.hide();
                },
                complete: function() {
                    button.prop('disabled', false);
                }
            });
        });
        
        /**
         * Clear Cache
         */
        $('#clear-cache').on('click', function(e) {
            e.preventDefault();
            
            const button = $(this);
            const statusDiv = $('#clear-cache-status');
            
            // Disable button
            button.prop('disabled', true);
            button.text(SENTIMENT_ANALYZER.strings.cacheClearing);
            statusDiv.html('');
            
            // Make API call
            $.ajax({
                url: SENTIMENT_ANALYZER.apiUrl + '/cache/clear',
                method: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', SENTIMENT_ANALYZER.nonce);
                },
                success: function(response) {
                    if (response.success) {
                        statusDiv.html(
                            '<div class="notice notice-success inline">' +
                            '<p>' + SENTIMENT_ANALYZER.strings.cacheSuccess + '</p>' +
                            '</div>'
                        );
                        
                        // Hide success message after 3 seconds
                        setTimeout(function() {
                            statusDiv.fadeOut(function() {
                                $(this).html('').show();
                            });
                        }, 3000);
                    } else {
                        showError(statusDiv, SENTIMENT_ANALYZER.strings.cacheError);
                    }
                },
                error: function(xhr) {
                    let errorMessage = SENTIMENT_ANALYZER.strings.cacheError;
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    showError(statusDiv, errorMessage);
                },
                complete: function() {
                    button.prop('disabled', false);
                    button.text(button.data('original-text') || SENTIMENT_ANALYZER.strings.clearCache || 'Clear Cache');
                }
            });
        });
        
        /**
         * Helper function to show error messages
         */
        function showError(container, message) {
            container.html(
                '<div class="notice notice-error inline">' +
                '<p><strong>Error:</strong> ' + message + '</p>' +
                '</div>'
            );
        }
        
        /**
         * Store original button text
         */
        $('#clear-cache').data('original-text', $('#clear-cache').text());
    });
    
})(jQuery);