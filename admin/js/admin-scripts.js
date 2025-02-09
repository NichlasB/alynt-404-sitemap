(function($) {
    'use strict';

    // Store references to commonly used elements
    const elements = {
        colorPickers: '.alynt-404-color-picker',
        buttonLinks: '.alynt-404-button-links',
        metaDesc: 'textarea[id$="_meta_description"]',
        mediaUpload: '.alynt-404-media-upload',
        customCSS: 'textarea[id$="_custom_css"]',
        clearColor: '.alynt-404-clear-color'
    };

    /**
     * Initialize all admin functionality
     */
    function init() {
        initColorPickers();
        initMediaUploaders();
        initButtonLinks();
        initMetaDescriptionCounter();
        initCustomCSSEditor();
        initFormValidation();
    }

    /**
     * Initialize color pickers
     */
    function initColorPickers() {
        $(elements.colorPickers).wpColorPicker({
            change: function(event, ui) {
                updateColorPreview();
            },
            clear: function() {
                updateColorPreview();
            }
        });

        // Handle clear color buttons
        $(elements.clearColor).on('click', function(e) {
            e.preventDefault();
            const targetId = $(this).data('target');
            const $input = $('#' + targetId);
            
            // Clear the input value
            $input.val('');
            
            // Clear the color picker
            $input.wpColorPicker('color', '');
            
            // Update the color picker UI
            $input.closest('.wp-picker-container').find('.wp-color-result').css('background-color', '');
            
            // Trigger change to update preview
            updateColorPreview();
        });
    }

    /**
     * Initialize media uploaders
     */
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

    /**
     * Initialize button links functionality
     */
    function initButtonLinks() {
        const container = $(elements.buttonLinks);
        if (!container.length) return;

        const template = $('#button-link-template').html();
        let nextIndex = container.find('.button-link-item').length;

        // Move button up
        container.on('click', '.button-link-move-up', function(e) {
            e.preventDefault();
            const currentItem = $(this).closest('.button-link-item');
            const prevItem = currentItem.prev('.button-link-item');

            if (prevItem.length) {
                currentItem.insertBefore(prevItem);
                updateButtonIndices();
            }
        });

        // Move button down
        container.on('click', '.button-link-move-down', function(e) {
            e.preventDefault();
            const currentItem = $(this).closest('.button-link-item');
            const nextItem = currentItem.next('.button-link-item');

            if (nextItem.length) {
                currentItem.insertAfter(nextItem);
                updateButtonIndices();
            }
        });

        // Add new button link
        container.on('click', '.add-button-link', function() {
            const maxButtons = container.data('max-buttons');
            const currentButtons = container.find('.button-link-item').length;

            if (currentButtons >= maxButtons) {
                alert(`Maximum ${maxButtons} buttons allowed per row.`);
                return;
            }

            const newButton = template.replace(/\{\{index\}\}/g, nextIndex++);
            container.find('.button-links-container').append(newButton);
            updateButtonIndices();
        });

        // Remove button link
        container.on('click', '.button-link-remove', function() {
            $(this).closest('.button-link-item').remove();
            updateButtonIndices();
        });

        // Update button title on text change
        container.on('input', '.button-link-text', function() {
            const text = $(this).val() || 'New Link';
            $(this).closest('.button-link-item').find('.button-link-title').text(text);
        });
    }

    /**
     * Update button indices after sorting or removal
     */
    function updateButtonIndices() {
        $('.button-link-item').each(function(index) {
            const item = $(this);
            item.attr('data-index', index);
            item.find('input').each(function() {
                const input = $(this);
                const name = input.attr('name');
                input.attr('name', name.replace(/\[\d+\]/, `[${index}]`));
            });
        });
    }

    /**
     * Initialize meta description character counter
     */
    function initMetaDescriptionCounter() {
        $(elements.metaDesc).each(function() {
            const textarea = $(this);
            const counter = textarea.siblings('.meta-description-counter').find('.counter');
            
            function updateCounter() {
                const count = textarea.val().length;
                counter.text(count);
                
                // Visual feedback
                if (count < 50) {
                    counter.css('color', '#dc3232'); // Red
                } else if (count > 160) {
                    counter.css('color', '#dc3232'); // Red
                } else {
                    counter.css('color', '#46b450'); // Green
                }
            }

            textarea.on('input', updateCounter);
            updateCounter(); // Initial count
        });
    }

    /**
     * Initialize custom CSS editor
     */
    function initCustomCSSEditor() {
        $(elements.customCSS).each(function() {
            const textarea = $(this);
            
            // Add tab support
            textarea.on('keydown', function(e) {
                if (e.keyCode === 9) { // Tab key
                    e.preventDefault();
                    
                    const start = this.selectionStart;
                    const end = this.selectionEnd;
                    
                    this.value = this.value.substring(0, start) + 
                    "    " + 
                    this.value.substring(end);
                    
                    this.selectionStart = this.selectionEnd = start + 4;
                }
            });
        });
    }

    /**
    * Initialize form validation
    */
    function initFormValidation() {
        $('form.alynt-404-form').on('submit', function(e) {
            const urlSlug = $('#sitemap_url_slug');
            if (urlSlug.length) {
                const slugValue = urlSlug.val();
                if (!/^[a-zA-Z0-9-]+$/.test(slugValue)) {
                    e.preventDefault();
                    alert('URL slug can only contain letters, numbers, and hyphens.');
                    urlSlug.focus();
                    return false;
                }
            }

            // Validate button links
            let isValid = true;
            $('.button-link-item').each(function() {
                const text = $(this).find('.button-link-text').val();
                const url = $(this).find('.button-link-url').val();

                if ((text && !url) || (!text && url)) {
                    e.preventDefault();
                    alert('Both text and URL are required for button links.');
                    isValid = false;
                    return false;
                }

                // Only validate if URL is not empty
                if (url) {
                    // Allow absolute URLs or relative paths
                    if (!url.match(/^(https?:\/\/|\/|[^\/])/) || /\s/.test(url)) {
                        e.preventDefault();
                        alert('Please enter a valid URL or relative path.');
                        isValid = false;
                        return false;
                    }
                }
            });

            return isValid;
        });
    }

    /**
     * Update color preview
     */
    function updateColorPreview() {
        const colors = {};
        $(elements.colorPickers).each(function() {
            const $picker = $(this);
            const key = $picker.attr('id').replace('_color', '');
            const color = $picker.val();
            // Only add the color if it has a value
            if (color) {
                colors[key] = color;
            }
        });

        // Update preview HTML with new colors
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

    // Initialize everything when document is ready
    $(document).ready(init);

})(jQuery);