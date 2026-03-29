(function(window, $) {
    'use strict';

    const search = window.Alynt404Search || {};

    function showError(message) {
        const $results = $(search.elements.searchResults);
        $results.html(`
            <div class="alynt-404-search-item error">
                ${message}
            </div>
        `);
        search.showResults();
    }

    function displayResults(results) {
        const $results = $(search.elements.searchResults);

        if (!results.length) {
            $results.html(`
                <div class="alynt-404-search-item no-results">
                    ${alynt404Search.messages.noResults}
                </div>
            `);
            search.showResults();
            return;
        }

        const html = results.map((result) => `
            <div class="alynt-404-search-item"
                 role="option"
                 tabindex="0"
                 data-url="${result.url}"
                 aria-selected="false">
                <div class="alynt-404-search-item-title">${result.title}</div>
                <div class="alynt-404-search-item-type">${result.type}</div>
            </div>
        `).join('');

        $results.html(html);
        search.showResults();
    }

    function selectResult($item) {
        $('.alynt-404-search-item').attr('aria-selected', 'false');
        $item.attr('aria-selected', 'true');
        if ($item[0]) {
            $item[0].scrollIntoView({ block: 'nearest' });
        }
    }

    function handleKeyboardNavigation(e) {
        const $results = $(search.elements.searchResults);
        const $items = $results.find('.alynt-404-search-item');
        const $current = $items.filter('[aria-selected="true"]');
        let $next;

        switch (e.keyCode) {
            case 40:
                e.preventDefault();
                $next = !$current.length ? $items.first() : $current.next('.alynt-404-search-item');
                if (!$next.length) {
                    $next = $items.first();
                }
                selectResult($next);
                break;
            case 38:
                e.preventDefault();
                $next = !$current.length ? $items.last() : $current.prev('.alynt-404-search-item');
                if (!$next.length) {
                    $next = $items.last();
                }
                selectResult($next);
                break;
            case 13:
                if ($current.length) {
                    e.preventDefault();
                    window.location.href = $current.data('url');
                }
                break;
            case 27:
                e.preventDefault();
                search.clearResults();
                break;
        }
    }

    function handleResultClick() {
        const url = $(this).data('url');
        if (url) {
            window.location.href = url;
        }
    }

    function handleClickOutside(e) {
        const $target = $(e.target);
        if (!$target.closest('.alynt-404-search').length) {
            search.clearResults();
        }
    }

    search.displayResults = displayResults;
    search.showError = showError;
    search.handleKeyboardNavigation = handleKeyboardNavigation;
    search.handleResultClick = handleResultClick;
    search.handleClickOutside = handleClickOutside;
})(window, jQuery);
