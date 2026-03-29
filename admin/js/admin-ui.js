(function(window, $) {
    'use strict';

    const admin = window.Alynt404Admin || {};
    const elements = admin.elements || {};

    function initColorPickers() {
        $(elements.colorPickers).wpColorPicker({
            change: function() {
                updateColorPreview();
            },
            clear: function() {
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
                });

                frame.open();
            });

            removeButton.on('click', function(e) {
                e.preventDefault();
                input.val('');
                preview.empty();
                $(this).addClass('hidden');
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

            textarea.on('keydown', function(e) {
                if (e.keyCode !== 9) {
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
            <h3 style="color: ${colors.headings || 'inherit'} !important; font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen-Sans,Ubuntu,Cantarell,'Helvetica Neue',sans-serif !important; margin: 0 !important;">Heading Example</h3>
        </div>
        <div class="preview-item" style="color: ${colors.paragraph || 'inherit'}">
            <p>Paragraph text example</p>
        </div>
        <div class="preview-item">
            <a href="#" style="color: ${colors.links || 'inherit'}">Link example</a>
        </div>
        <div class="preview-item">
            <button style="background-color: ${colors.buttons || 'inherit'}; color: ${colors.button_text || 'inherit'}">
                Button example
            </button>
        </div>
        <div class="preview-item">
            <input type="text"
                   placeholder="Search example"
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
