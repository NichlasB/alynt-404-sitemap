/* global jQuery, wp */
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
    window.Alynt404Admin.messages =
        (window.alynt404Vars && window.alynt404Vars.messages) || {};
    window.Alynt404Admin.state = {
        isDirty: false,
        skipBeforeUnload: false
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

    window.Alynt404Admin.setDirtyState = function(isDirty) {
        window.Alynt404Admin.state.isDirty = Boolean(isDirty);
    };

    window.Alynt404Admin.clearFieldError = function($field) {
        const $error = $field.siblings('.alynt-404-field-error').first();
        $field.removeClass('error').removeAttr('aria-invalid').removeAttr('aria-describedby');
        if ($error.length) {
            $error.remove();
        }
    };

    window.Alynt404Admin.setFieldError = function($field, message) {
        const fieldId = $field.attr('id') || ('alynt-404-error-' + Math.random().toString(36).slice(2));
        const errorId = fieldId + '-error';
        let $error = $field.siblings('.alynt-404-field-error').first();

        if (!$field.attr('id')) {
            $field.attr('id', fieldId);
        }

        if (!$error.length) {
            $error = $('<div />', {
                class: 'alynt-404-field-error',
                id: errorId,
                role: 'alert'
            });
            $field.after($error);
        }

        $field.addClass('error').attr('aria-invalid', 'true').attr('aria-describedby', errorId);
        $error.text(message);
    };

    window.Alynt404Admin.clearAllFieldErrors = function($context) {
        $context.find('.error').removeClass('error').removeAttr('aria-invalid').removeAttr('aria-describedby');
        $context.find('.alynt-404-field-error').remove();
    };

    window.Alynt404Admin.setButtonLoading = function($button, isLoading) {
        if (!$button.length) {
            return;
        }

        const originalText = $button.data('original-text') || $button.text();
        if (!$button.data('original-text')) {
            $button.data('original-text', originalText);
        }

        if (isLoading) {
            $button.prop('disabled', true);
            $button.attr('aria-busy', 'true');
            $button.addClass('is-busy');
            $button.text($button.data('loading-text') || originalText);
            return;
        }

        $button.prop('disabled', false);
        $button.removeAttr('aria-busy');
        $button.removeClass('is-busy');
        $button.text(originalText);
    };
})(window, jQuery);

(function(window, $) {
    'use strict';

    const admin = window.Alynt404Admin || {};
    const elements = admin.elements || {};
    const messages = admin.messages || {};

    function initColorPickers() {
        $(elements.colorPickers).wpColorPicker({
            change: function() {
                admin.setDirtyState(true);
                admin.clearFieldError($(this));
                updateColorPreview();
            },
            clear: function() {
                admin.setDirtyState(true);
                admin.clearFieldError($(this));
                updateColorPreview();
            }
        });

        $(elements.clearColor).on('click', function(e) {
            e.preventDefault();
            const targetId = $(this).data('target');
            const $input = $('#' + targetId);

            $input.val('');
            $input.wpColorPicker('color', '');
            $input.closest('.wp-picker-container').find('.wp-color-result').css('background-color', '');
            admin.setDirtyState(true);
            admin.clearFieldError($input);
            updateColorPreview();
        });
    }

    function initMediaUploaders() {
        $(elements.mediaUpload).each(function() {
            const container = $(this);
            const uploadButton = container.find('.upload-image-button');
            const removeButton = container.find('.remove-image-button');
            const preview = container.find('.image-preview');
            const input = container.find('input[type="hidden"]');

            uploadButton.on('click', function(e) {
                e.preventDefault();

                const frame = wp.media({
                    title: $(this).data('uploader-title'),
                    button: {
                        text: $(this).data('uploader-button-text')
                    },
                    multiple: false
                });

                frame.on('select', function() {
                    const attachment = frame.state().get('selection').first().toJSON();
                    input.val(attachment.id);
                    preview.html(`<img src="${attachment.url}" alt="" />`);
                    removeButton.removeClass('hidden');
                    admin.setDirtyState(true);
                });

                frame.open();
            });

            removeButton.on('click', function(e) {
                e.preventDefault();
                input.val('');
                preview.empty();
                $(this).addClass('hidden');
                admin.setDirtyState(true);
            });
        });
    }

    function initMetaDescriptionCounter() {
        $(elements.metaDesc).each(function() {
            const textarea = $(this);
            const counter = textarea.siblings('.meta-description-counter').find('.counter');

            function updateCounter() {
                const count = textarea.val().length;
                counter.text(count);

                if (count < 50 || count > 160) {
                    counter.css('color', '#dc3232');
                } else {
                    counter.css('color', '#46b450');
                }
            }

            textarea.on('input', updateCounter);
            updateCounter();
        });
    }

    function initCustomCSSEditor() {
        $(elements.customCSS).each(function() {
            const textarea = $(this);
            let trapTab = true;

            textarea.on('keydown', function(e) {
                if (e.keyCode === 27) {
                    trapTab = false;
                    return;
                }

                if (e.keyCode !== 9) {
                    trapTab = true;
                    return;
                }

                if (!trapTab) {
                    return;
                }

                e.preventDefault();
                const start = this.selectionStart;
                const end = this.selectionEnd;
                this.value = this.value.substring(0, start) + '    ' + this.value.substring(end);
                this.selectionStart = this.selectionEnd = start + 4;
            });
        });
    }

    function updateColorPreview() {
        const colors = {};
        $(elements.colorPickers).each(function() {
            const $picker = $(this);
            const key = $picker.attr('id').replace('_color', '');
            const color = $picker.val();
            if (color) {
                colors[key] = color;
            }
        });

        const preview = $('.alynt-404-color-preview');
        preview.html(`
        <div class="preview-item">
            <h3 style="color: ${colors.headings || 'inherit'} !important; font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen-Sans,Ubuntu,Cantarell,'Helvetica Neue',sans-serif !important; margin: 0 !important;">${messages.previewHeadingExample || ''}</h3>
        </div>
        <div class="preview-item" style="color: ${colors.paragraph || 'inherit'}">
            <p>${messages.previewParagraphExample || ''}</p>
        </div>
        <div class="preview-item">
            <a href="#" style="color: ${colors.links || 'inherit'}">${messages.previewLinkExample || ''}</a>
        </div>
        <div class="preview-item">
            <button style="background-color: ${colors.buttons || 'inherit'}; color: ${colors.button_text || 'inherit'}">
                ${messages.previewButtonExample || ''}
            </button>
        </div>
        <div class="preview-item">
            <input type="text"
                   placeholder="${messages.previewSearchPlaceholder || ''}"
                   style="color: ${colors.search_text || 'inherit'};
                          background-color: ${colors.search_background || 'inherit'};
                          border-color: ${colors.search_border || 'inherit'}">
        </div>
        `);
    }

    admin.initColorPickers = initColorPickers;
    admin.initMediaUploaders = initMediaUploaders;
    admin.initMetaDescriptionCounter = initMetaDescriptionCounter;
    admin.initCustomCSSEditor = initCustomCSSEditor;
    admin.updateColorPreview = updateColorPreview;
})(window, jQuery);

(function(window, $) {
    'use strict';

    const admin = window.Alynt404Admin || {};
    const messages = admin.messages || {};
    const elements = admin.elements || {};

    function updateButtonIndices() {
        $('.button-link-item').each(function(index) {
            const item = $(this);
            const position = index + 1;
            item.attr('data-index', index);
            item.find('input').each(function() {
                const input = $(this);
                const name = input.attr('name');
                input.attr('name', name.replace(/\[\d+\]/, `[${index}]`));
            });
            item.find('.button-link-text').attr(
                'aria-label',
                (messages.buttonLinkTextLabel || 'Quick link %d: button text').replace('%d', position)
            );
            item.find('.button-link-url').attr(
                'aria-label',
                (messages.buttonLinkUrlLabel || 'Quick link %d: button URL').replace('%d', position)
            );
        });
    }

    function initButtonLinks() {
        const container = $(elements.buttonLinks);
        if (!container.length) {
            return;
        }

        const template = $('#button-link-template').html();
        let nextIndex = container.find('.button-link-item').length;
        bindMoveHandlers(container);
        bindTextHandlers(container);
        bindAddAndRemoveHandlers(container, template, function() {
            return nextIndex++;
        });
    }

    function bindMoveHandlers(container) {
        container.on('click', '.button-link-move-up', function(e) {
            e.preventDefault();
            const currentItem = $(this).closest('.button-link-item');
            const prevItem = currentItem.prev('.button-link-item');
            if (!prevItem.length) {
                return;
            }
            currentItem.insertBefore(prevItem);
            updateButtonIndices();
        });

        container.on('click', '.button-link-move-down', function(e) {
            e.preventDefault();
            const currentItem = $(this).closest('.button-link-item');
            const nextItem = currentItem.next('.button-link-item');
            if (!nextItem.length) {
                return;
            }
            currentItem.insertAfter(nextItem);
            updateButtonIndices();
        });
    }

    function bindAddAndRemoveHandlers(container, template, getNextIndex) {
        container.on('click', '.add-button-link', function() {
            const maxButtons = container.data('max-buttons');
            const currentButtons = container.find('.button-link-item').length;
            if (currentButtons >= maxButtons) {
                const template = messages.maxButtonsPerRow || '';
                admin.showValidationMessage(container, template.replace('%d', maxButtons));
                return;
            }

            admin.clearValidationMessage(container);
            const newButton = template.replace(/\{\{index\}\}/g, getNextIndex());
            container.find('.button-links-container').append(newButton);
            admin.setDirtyState(true);
            updateButtonIndices();
        });

        container.on('click', '.button-link-remove', function() {
            if (!window.confirm(messages.removeLinkConfirm || '')) {
                return;
            }

            admin.clearValidationMessage(container);
            $(this).closest('.button-link-item').remove();
            admin.setDirtyState(true);
            updateButtonIndices();
        });
    }

    function bindTextHandlers(container) {
        container.on('input', '.button-link-text', function() {
            admin.setDirtyState(true);
            admin.clearValidationMessage(container);
            admin.clearFieldError($(this));
            const text = $(this).val() || messages.newLinkTitle || '';
            $(this).closest('.button-link-item').find('.button-link-title').text(text);
        });

        container.on('input', '.button-link-url', function() {
            admin.setDirtyState(true);
            admin.clearValidationMessage(container);
            admin.clearFieldError($(this));
        });
    }

    admin.initButtonLinks = initButtonLinks;
    admin.updateButtonIndices = updateButtonIndices;
})(window, jQuery);

(function(window, $) {
    'use strict';

    const admin = window.Alynt404Admin || {};
    const messages = admin.messages || {};
    const elements = admin.elements || {};

    function validateSlug($form, $urlSlug) {
        if (!$urlSlug.length) {
            return [];
        }

        const slugValue = $urlSlug.val();
        if (/^[a-zA-Z0-9-]+$/.test(slugValue)) {
            admin.clearFieldError($urlSlug);
            return [];
        }

        admin.setFieldError($urlSlug, messages.invalidSlug || '');
        admin.showValidationMessage($form, messages.invalidSlug || '');

        return [{
            field: $urlSlug,
            message: messages.invalidSlug || ''
        }];
    }

    function validateButtonLinks($container) {
        const errors = [];
        $('.button-link-item').each(function() {
            const $item = $(this);
            const $textField = $item.find('.button-link-text');
            const $urlField = $item.find('.button-link-url');
            const text = $textField.val();
            const url = $urlField.val();

            if ((text && !url) || (!text && url)) {
                const $emptyField = $item.find('.button-link-text, .button-link-url').filter(function() {
                    return !$(this).val();
                }).first();
                $item.addClass('error');
                admin.setFieldError($emptyField, messages.buttonTextAndUrlRequired || '');
                errors.push({
                    field: $emptyField,
                    message: messages.buttonTextAndUrlRequired || ''
                });
                return;
            }

            if (url && (!url.match(/^(https?:\/\/|\/|[^\/])/) || /\s/.test(url))) {
                $item.addClass('error');
                admin.setFieldError($urlField, messages.invalidUrlOrPath || '');
                errors.push({
                    field: $urlField,
                    message: messages.invalidUrlOrPath || ''
                });
                return;
            }

            admin.clearFieldError($textField);
            admin.clearFieldError($urlField);
        });

        if (errors.length) {
            admin.showValidationMessage($container, errors[0].message);
        }

        return errors;
    }

    function validateColorFields($form) {
        const errors = [];

        $form.find(elements.colorPickers).each(function() {
            const $field = $(this);
            const value = $field.val();

            if (!value || /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/.test(value)) {
                admin.clearFieldError($field);
                return;
            }

            admin.setFieldError($field, messages.invalidColor || '');
            errors.push({
                field: $field,
                message: messages.invalidColor || ''
            });
        });

        return errors;
    }

    function markFormDirty() {
        admin.setDirtyState(true);
    }

    function initDirtyTracking() {
        $(document).on('input change', 'form.alynt-404-form :input', markFormDirty);

        $(window).on('beforeunload', function() {
            if (admin.state.skipBeforeUnload || !admin.state.isDirty) {
                return undefined;
            }

            return messages.unsavedChanges || '';
        });
    }

    function initFormValidation() {
        $('form.alynt-404-form').on('submit', function(e) {
            const $form = $(this);
            const $urlSlug = $('#sitemap_url_slug');
            const $buttonLinksContainer = $(elements.buttonLinks);
            const $submitButton = $form.find('.alynt-404-submit');
            const errors = [];

            admin.clearValidationMessage($form);
            admin.clearValidationMessage($buttonLinksContainer);
            admin.clearAllFieldErrors($form);
            $('.button-link-item').removeClass('error');

            errors.push.apply(errors, validateColorFields($form));
            errors.push.apply(errors, validateSlug($form, $urlSlug));
            errors.push.apply(errors, validateButtonLinks($buttonLinksContainer));

            if (errors.length) {
                if (errors[0].field && errors[0].field.length) {
                    errors[0].field.trigger('focus');
                }
                e.preventDefault();
                return false;
            }

            admin.state.skipBeforeUnload = true;
            admin.setDirtyState(false);
            admin.setButtonLoading($submitButton, true);

            return true;
        });

        $('form.reset-form').on('submit', function() {
            admin.state.skipBeforeUnload = true;
            admin.setDirtyState(false);
            admin.setButtonLoading($(this).find('.alynt-404-reset-button'), true);
        });
    }

    admin.initFormValidation = initFormValidation;
    admin.initDirtyTracking = initDirtyTracking;
})(window, jQuery);

(function(window, $) {
    'use strict';

    function initAdmin() {
        const admin = window.Alynt404Admin || {};

        if (typeof admin.initColorPickers === 'function') {
            admin.initColorPickers();
        }
        if (typeof admin.initMediaUploaders === 'function') {
            admin.initMediaUploaders();
        }
        if (typeof admin.initButtonLinks === 'function') {
            admin.initButtonLinks();
        }
        if (typeof admin.initMetaDescriptionCounter === 'function') {
            admin.initMetaDescriptionCounter();
        }
        if (typeof admin.initCustomCSSEditor === 'function') {
            admin.initCustomCSSEditor();
        }
        if (typeof admin.initFormValidation === 'function') {
            admin.initFormValidation();
        }
        if (typeof admin.initDirtyTracking === 'function') {
            admin.initDirtyTracking();
        }
    }

    $(document).ready(initAdmin);
})(window, jQuery);
