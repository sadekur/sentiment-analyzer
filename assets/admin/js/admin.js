(function($) {
    'use strict';

    $(document).ready(function() {
        
        /**
         * Save Settings via API
         */
        $('#sentiment-settings-form').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const button = $('#save-settings');
            const spinner = form.find('.spinner');
            const messagesContainer = $('#sa-messages');
            
            // Get form data
            const formData = {
                positive_keywords: $('#sa_positive_keywords').val(),
                negative_keywords: $('#sa_negative_keywords').val(),
                neutral_keywords: $('#sa_neutral_keywords').val(),
                badge_position: $('#sa_badge_position').val()
            };
            
            // Disable button and show spinner
            button.prop('disabled', true);
            spinner.addClass('is-active');
            messagesContainer.html('');
            
            // Make API call
            $.ajax({
                url: SENTIMENT_ANALYZER.apiUrl + '/settings',
                method: 'POST',
                data: JSON.stringify(formData),
                contentType: 'application/json',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', SENTIMENT_ANALYZER.nonce);
                },
                success: function(response) {
                    if (response.success) {
                        showMessage('success', SENTIMENT_ANALYZER.strings.saveSuccess);
                        
                        // Scroll to message
                        $('html, body').animate({
                            scrollTop: messagesContainer.offset().top - 50
                        }, 500);
                    } else {
                        showMessage('error', SENTIMENT_ANALYZER.strings.saveError);
                    }
                },
                error: function(xhr) {
                    let errorMessage = SENTIMENT_ANALYZER.strings.saveError;
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    showMessage('error', errorMessage);
                },
                complete: function() {
                    button.prop('disabled', false);
                    spinner.removeClass('is-active');
                }
            });
        });
        
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
            if (!confirm(SENTIMENT_ANALYZER.strings.confirmBulk)) {
                return;
            }
            
            // Disable button and show progress
            button.prop('disabled', true);
            button.html('<span class="dashicons dashicons-update spin"></span> ' + SENTIMENT_ANALYZER.strings.bulkUpdating);
            progressContainer.show();
            statusDiv.html('');
            progressBar.css('width', '0%');
            progressText.text(SENTIMENT_ANALYZER.strings.bulkUpdating);
            
            // Animate progress bar
            let progress = 0;
            const progressInterval = setInterval(function() {
                progress += 5;
                if (progress <= 90) {
                    progressBar.css('width', progress + '%');
                }
            }, 300);
            
            // Make API call
            $.ajax({
                url: SENTIMENT_ANALYZER.apiUrl + '/analyze/bulk',
                method: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', SENTIMENT_ANALYZER.nonce);
                },
                success: function(response) {
                    clearInterval(progressInterval);
                    progressBar.css('width', '100%');
                    
                    if (response.success) {
                        const message = SENTIMENT_ANALYZER.strings.bulkSuccess.replace('{count}', response.analyzed);
                        progressText.html('<span class="dashicons dashicons-yes-alt"></span> ' + message);
                        
                        statusDiv.html(
                            '<div class="notice notice-success inline sa-notice">' +
                            '<p><strong>' + message + '</strong></p>' +
                            '<p>Analyzed <strong>' + response.analyzed + '</strong> of <strong>' + response.total + '</strong> posts.</p>' +
                            '</div>'
                        );
                        
                        // Hide progress after 3 seconds
                        setTimeout(function() {
                            progressContainer.fadeOut();
                        }, 3000);
                    } else {
                        showErrorInline(statusDiv, SENTIMENT_ANALYZER.strings.bulkError);
                        progressContainer.hide();
                    }
                },
                error: function(xhr) {
                    clearInterval(progressInterval);
                    let errorMessage = SENTIMENT_ANALYZER.strings.bulkError;
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    showErrorInline(statusDiv, errorMessage);
                    progressContainer.hide();
                },
                complete: function() {
                    button.prop('disabled', false);
                    button.html('<span class="dashicons dashicons-update"></span> ' + button.text().replace(/^.*?\s/, ''));
                }
            });
        });
        
        /**
         * Clear Cache
         */
        $('#clear-cache').on('click', function(e) {
            e.preventDefault();
            
            const button = $(this);
            const originalText = button.html();
            const statusDiv = $('#clear-cache-status');
            
            // Disable button
            button.prop('disabled', true);
            button.html('<span class="dashicons dashicons-update spin"></span> ' + SENTIMENT_ANALYZER.strings.cacheClearing);
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
                            '<div class="notice notice-success inline sa-notice">' +
                            '<p><span class="dashicons dashicons-yes-alt"></span> ' + SENTIMENT_ANALYZER.strings.cacheSuccess + '</p>' +
                            '</div>'
                        );
                        
                        // Hide success message after 3 seconds
                        setTimeout(function() {
                            statusDiv.fadeOut(function() {
                                $(this).html('').show();
                            });
                        }, 3000);
                    } else {
                        showErrorInline(statusDiv, SENTIMENT_ANALYZER.strings.cacheError);
                    }
                },
                error: function(xhr) {
                    let errorMessage = SENTIMENT_ANALYZER.strings.cacheError;
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    showErrorInline(statusDiv, errorMessage);
                },
                complete: function() {
                    button.prop('disabled', false);
                    button.html(originalText);
                }
            });
        });
        
        /**
         * Copy API URL to clipboard
         */
        $('.copy-api-url').on('click', function(e) {
            e.preventDefault();
            const button = $(this);
            const textToCopy = button.data('clipboard-text');
            
            // Create temporary input
            const tempInput = $('<input>');
            $('body').append(tempInput);
            tempInput.val(textToCopy).select();
            document.execCommand('copy');
            tempInput.remove();
            
            // Change button text temporarily
            const originalHtml = button.html();
            button.html('<span class="dashicons dashicons-yes"></span> Copied!');
            
            setTimeout(function() {
                button.html(originalHtml);
            }, 2000);
        });
        
        /**
         * Helper function to show global messages
         */
        function showMessage(type, message) {
            const messagesContainer = $('#sa-messages');
            const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
            
            const html = 
                '<div class="notice ' + noticeClass + ' is-dismissible sa-notice">' +
                '<p><strong>' + message + '</strong></p>' +
                '<button type="button" class="notice-dismiss">' +
                '<span class="screen-reader-text">Dismiss this notice.</span>' +
                '</button>' +
                '</div>';
            
            messagesContainer.html(html);
            
            // Make dismissible
            messagesContainer.find('.notice-dismiss').on('click', function() {
                $(this).parent().fadeOut();
            });
        }
        
        /**
         * Helper function to show inline error messages
         */
        function showErrorInline(container, message) {
            container.html(
                '<div class="notice notice-error inline sa-notice">' +
                '<p><strong>Error:</strong> ' + message + '</p>' +
                '</div>'
            );
        }
        
        /**
         * Add spinning animation for dashicons
         */
        const style = $('<style>').text(`
            .dashicons.spin {
                animation: spin 1s linear infinite;
            }
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        `);
        $('head').append(style);
    });
    
})(jQuery);