(function(window, $) {
    'use strict';

    window.Alynt404Admin = window.Alynt404Admin || {};

    window.Alynt404Admin.elements = {
        colorPickers: '.alynt-404-color-picker',
        buttonLinks: '.alynt-404-button-links',
        metaDesc: 'textarea[id$="_meta_description"]',
        mediaUpload: '.alynt-404-media-upload',
        customCSS: 'textarea[id$="_custom_css"]',
        clearColor: '.alynt-404-clear-color'
    };

    window.Alynt404Admin.showValidationMessage = function($context, message) {
        const $notice = $context.find('.alynt-404-validation-notice').first();
        if (!$notice.length) {
            return;
        }
        $notice.text(message).removeAttr('hidden');
    };

    window.Alynt404Admin.clearValidationMessage = function($context) {
        const $notice = $context.find('.alynt-404-validation-notice').first();
        if (!$notice.length) {
            return;
        }
        $notice.attr('hidden', true).empty();
    };
})(window, jQuery);
