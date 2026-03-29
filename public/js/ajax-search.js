(function(window, $) {
    'use strict';

    function init() {
        const search = window.Alynt404Search || {};
        const $searchInput = $(search.elements.searchInput);
        const $searchResults = $(search.elements.searchResults);

        if (!$searchInput.length) {
            return;
        }

        $searchInput
            .on('input', search.handleSearchInput)
            .on('focus', search.showResults)
            .on('keydown', search.handleKeyboardNavigation);

        $(document).on('click', search.handleClickOutside);
        $searchResults.on('click', '.alynt-404-search-item', search.handleResultClick);
    }

    $(document).ready(init);
})(window, jQuery);
