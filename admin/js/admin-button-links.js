(function(window, $) {
    'use strict';

    const admin = window.Alynt404Admin || {};
    const elements = admin.elements || {};

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
                admin.showValidationMessage(container, `Maximum ${maxButtons} buttons allowed per row.`);
                return;
            }

            admin.clearValidationMessage(container);
            const newButton = template.replace(/\{\{index\}\}/g, getNextIndex());
            container.find('.button-links-container').append(newButton);
            updateButtonIndices();
        });

        container.on('click', '.button-link-remove', function() {
            admin.clearValidationMessage(container);
            $(this).closest('.button-link-item').remove();
            updateButtonIndices();
        });
    }

    function bindTextHandlers(container) {
        container.on('input', '.button-link-text', function() {
            admin.clearValidationMessage(container);
            const text = $(this).val() || 'New Link';
            $(this).closest('.button-link-item').find('.button-link-title').text(text);
        });

        container.on('input', '.button-link-url', function() {
            admin.clearValidationMessage(container);
        });
    }

    admin.initButtonLinks = initButtonLinks;
    admin.updateButtonIndices = updateButtonIndices;
})(window, jQuery);
