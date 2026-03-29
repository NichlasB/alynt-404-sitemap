(function(window, $) {
    'use strict';

    const admin = window.Alynt404Admin || {};
    const elements = admin.elements || {};

    function validateSlug($form, $urlSlug) {
        if (!$urlSlug.length) {
            return true;
        }

        const slugValue = $urlSlug.val();
        if (/^[a-zA-Z0-9-]+$/.test(slugValue)) {
            return true;
        }

        $urlSlug.addClass('error');
        admin.showValidationMessage($form, 'URL slug can only contain letters, numbers, and hyphens.');
        $urlSlug.focus();
        return false;
    }

    function validateButtonLinks($container) {
        let valid = true;
        $('.button-link-item').each(function() {
            const $item = $(this);
            const text = $item.find('.button-link-text').val();
            const url = $item.find('.button-link-url').val();

            if ((text && !url) || (!text && url)) {
                $item.addClass('error');
                admin.showValidationMessage($container, 'Both text and URL are required for button links.');
                $item.find('.button-link-text, .button-link-url').filter(function() {
                    return !$(this).val();
                }).first().focus();
                valid = false;
                return false;
            }

            if (url && (!url.match(/^(https?:\/\/|\/|[^\/])/) || /\s/.test(url))) {
                $item.addClass('error');
                admin.showValidationMessage($container, 'Please enter a valid URL or relative path.');
                $item.find('.button-link-url').focus();
                valid = false;
                return false;
            }

            return true;
        });

        return valid;
    }

    function initFormValidation() {
        $('form.alynt-404-form').on('submit', function(e) {
            const $form = $(this);
            const $urlSlug = $('#sitemap_url_slug');
            const $buttonLinksContainer = $(elements.buttonLinks);

            admin.clearValidationMessage($form);
            admin.clearValidationMessage($buttonLinksContainer);
            $urlSlug.removeClass('error');
            $('.button-link-item').removeClass('error');

            if (!validateSlug($form, $urlSlug)) {
                e.preventDefault();
                return false;
            }

            if (!validateButtonLinks($buttonLinksContainer)) {
                e.preventDefault();
                return false;
            }

            return true;
        });
    }

    admin.initFormValidation = initFormValidation;
})(window, jQuery);
