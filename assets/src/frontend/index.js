/* global jQuery, alynt404Search */
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
        const $search = $('.alynt-404-search');
        const $input = $(window.Alynt404Search.elements.searchInput);

        $search.toggleClass('is-loading', loading).attr('aria-busy', loading ? 'true' : 'false');
        $input.attr('aria-busy', loading ? 'true' : 'false');
    };

    window.Alynt404Search.showResults = function() {
        $(window.Alynt404Search.elements.searchResults).addClass('active');
        $(window.Alynt404Search.elements.searchInput).attr('aria-expanded', 'true');
    };

    window.Alynt404Search.clearResults = function() {
        const $results = $(window.Alynt404Search.elements.searchResults);
        const $input = $(window.Alynt404Search.elements.searchInput);
        $results.removeClass('active').empty();
        $input.attr('aria-expanded', 'false').removeAttr('aria-activedescendant');
        window.Alynt404Search.state.lastSearchTerm = '';
        $('#alynt-404-search-status').text('');
    };
})(window, jQuery);

(function(window, $) {
    'use strict';

    const search = window.Alynt404Search || {};

    function showError(message) {
        const $results = $(search.elements.searchResults);
        $results.empty().append(
            $('<div />', {
                class: 'alynt-404-search-item error',
                role: 'status',
                text: message
            })
        );
        $('#alynt-404-search-status').text(message);
        search.showResults();
    }

    function displayResults(results) {
        const $results = $(search.elements.searchResults);

        if (!results.length) {
            $results.empty().append(
                $('<div />', {
                    class: 'alynt-404-search-item no-results',
                    role: 'status',
                    text: alynt404Search.messages.noResults
                })
            );
            $('#alynt-404-search-status').text(alynt404Search.messages.noResults);
            search.showResults();
            return;
        }

        $results.empty();

        results.forEach(function(result, index) {
            const $item = $('<div />', {
                class: 'alynt-404-search-item',
                id: `alynt-404-result-${index}`,
                role: 'option',
                tabindex: 0,
                'aria-selected': 'false'
            });

            $item.attr('data-url', result.url || '');
            $item.append(
                $('<div />', {
                    class: 'alynt-404-search-item-title',
                    text: result.title || ''
                })
            );
            $item.append(
                $('<div />', {
                    class: 'alynt-404-search-item-type',
                    text: result.type || ''
                })
            );

            $results.append($item);
        });

        search.showResults();
    }

    function selectResult($item) {
        const $current = $(search.elements.searchResults)
            .find('.alynt-404-search-item[aria-selected="true"]');

        if ($current.length) {
            $current.attr('aria-selected', 'false');
        }

        $item.attr('aria-selected', 'true');
        if ($item[0]) {
            $item[0].scrollIntoView({ block: 'nearest' });
            $(search.elements.searchInput).attr('aria-activedescendant', $item[0].id);
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

(function(window, $) {
    'use strict';

    const search = window.Alynt404Search || {};

    function resolveErrorMessage(jqXHR, textStatus) {
        if (!navigator.onLine) {
            return alynt404Search.messages.offline;
        }

        if (textStatus === 'timeout') {
            return alynt404Search.messages.timeout;
        }

        if (jqXHR && jqXHR.status >= 500) {
            return alynt404Search.messages.server;
        }

        return alynt404Search.messages.error;
    }

    function performSearch(searchTerm) {
        if (search.state.currentRequest) {
            search.state.currentRequest.abort();
        }

        search.setLoadingState(true);

        search.state.currentRequest = $.ajax({
            url: alynt404Search.ajaxurl,
            type: 'POST',
            dataType: 'json',
            timeout: Number(alynt404Search.timeout) || 8000,
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
            error: function(jqXHR, textStatus) {
                if (textStatus === 'abort') {
                    return;
                }

                search.showError(resolveErrorMessage(jqXHR, textStatus));
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
