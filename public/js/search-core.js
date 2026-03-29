(function(window, $) {
    'use strict';

    if (typeof window.alynt404Search === 'undefined') {
        console.error('alynt404Search object is not defined. Script localization failed.');
    }

    window.Alynt404Search = window.Alynt404Search || {};

    window.Alynt404Search.elements = {
        searchInput: '#alynt-404-search-input',
        searchResults: '#alynt-404-search-results'
    };

    window.Alynt404Search.state = {
        searchTimeout: null,
        currentRequest: null,
        lastSearchTerm: ''
    };

    window.Alynt404Search.setLoadingState = function(loading) {
        $('.alynt-404-search').toggleClass('is-loading', loading);
    };

    window.Alynt404Search.showResults = function() {
        $(window.Alynt404Search.elements.searchResults).addClass('active');
    };

    window.Alynt404Search.clearResults = function() {
        const $results = $(window.Alynt404Search.elements.searchResults);
        $results.removeClass('active').empty();
        window.Alynt404Search.state.lastSearchTerm = '';
    };
})(window, jQuery);
