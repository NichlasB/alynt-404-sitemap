(function(window, $) {
    'use strict';

    const search = window.Alynt404Search || {};

    function performSearch(searchTerm) {
        if (search.state.currentRequest) {
            search.state.currentRequest.abort();
        }

        search.setLoadingState(true);

        search.state.currentRequest = $.ajax({
            url: alynt404Search.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'alynt_404_search',
                nonce: alynt404Search.nonce,
                search: searchTerm
            },
            success: function(response) {
                if (response.success) {
                    search.displayResults(response.data.results);
                    return;
                }
                search.showError(response.data.message || alynt404Search.messages.error);
            },
            error: function() {
                search.showError(alynt404Search.messages.error);
            },
            complete: function() {
                search.setLoadingState(false);
                search.state.currentRequest = null;
                search.state.lastSearchTerm = searchTerm;
            }
        });
    }

    function handleSearchInput(e) {
        const searchTerm = e.target.value.trim();

        if (search.state.searchTimeout) {
            clearTimeout(search.state.searchTimeout);
        }

        if (!searchTerm) {
            search.clearResults();
            return;
        }

        if (searchTerm === search.state.lastSearchTerm) {
            return;
        }

        search.state.searchTimeout = setTimeout(function() {
            performSearch(searchTerm);
        }, 300);
    }

    search.performSearch = performSearch;
    search.handleSearchInput = handleSearchInput;
})(window, jQuery);
