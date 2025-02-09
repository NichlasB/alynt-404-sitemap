(function($) {
    'use strict';

    // Debug: Check if our AJAX variables are properly localized
    if (typeof alynt404Search === 'undefined') {
        console.error('alynt404Search object is not defined. Script localization failed.');
    } else {
        console.log('AJAX Configuration:', {
            ajaxurl: alynt404Search?.ajaxurl,
            nonceAvailable: !!alynt404Search.nonce,
            messages: alynt404Search.messages
        });
    }

    // Store DOM elements and variables
    const elements = {
        searchInput: '#alynt-404-search-input',
        searchResults: '#alynt-404-search-results'
    };

    let searchTimeout = null;
    let currentRequest = null;
    let lastSearchTerm = '';
    let isLoading = false;

    /**
     * Initialize search functionality
     */
    function init() {
        const $searchInput = $(elements.searchInput);
        const $searchResults = $(elements.searchResults);

        if (!$searchInput.length) return;

        // Bind events
        $searchInput
        .on('input', handleSearchInput)
        .on('focus', showResults)
        .on('keydown', handleKeyboardNavigation);

        // Close results when clicking outside
        $(document).on('click', handleClickOutside);

        // Handle result item clicks
        $searchResults.on('click', '.alynt-404-search-item', handleResultClick);
    }

    /**
     * Handle search input
     * @param {Event} e Input event
     */
    function handleSearchInput(e) {
        const searchTerm = e.target.value.trim();

        // Clear previous timeout
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        // Clear results if search is empty
        if (!searchTerm) {
            clearResults();
            return;
        }

        // Don't search if term hasn't changed
        if (searchTerm === lastSearchTerm) {
            return;
        }

        // Set timeout for search
        searchTimeout = setTimeout(() => {
            performSearch(searchTerm);
        }, 300);
    }

    /**
     * Perform AJAX search
     * @param {string} searchTerm Search term
     */
    function performSearch(searchTerm) {
        // Abort previous request
        if (currentRequest) {
            currentRequest.abort();
        }

        // Set loading state
        setLoadingState(true);

        currentRequest = $.ajax({
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
                    displayResults(response.data.results);
                } else {
                    showError(response.data.message || alynt404Search.messages.error);
                }
            },
            error: function() {
                showError(alynt404Search.messages.error);
            },
            complete: function() {
                setLoadingState(false);
                currentRequest = null;
                lastSearchTerm = searchTerm;
            }
        });
    }

    /**
     * Display search results
     * @param {Array} results Search results
     */
    function displayResults(results) {
        const $results = $(elements.searchResults);
        
        if (!results.length) {
            $results.html(`
                <div class="alynt-404-search-item no-results">
                    ${alynt404Search.messages.noResults}
                </div>
            `);
        } else {
            const html = results.map((result, index) => `
                <div class="alynt-404-search-item" 
                     role="option" 
                     tabindex="0" 
                     data-url="${result.url}"
                     aria-selected="false">
                    <div class="alynt-404-search-item-title">
                        ${result.title}
                    </div>
                    <div class="alynt-404-search-item-type">
                        ${result.type}
                    </div>
                </div>
            `).join('');

            $results.html(html);
        }

        showResults();
    }

    /**
     * Handle keyboard navigation
     * @param {Event} e Keyboard event
     */
    function handleKeyboardNavigation(e) {
        const $results = $(elements.searchResults);
        const $items = $results.find('.alynt-404-search-item');
        const $current = $items.filter('[aria-selected="true"]');
        let $next;

        switch (e.keyCode) {
            case 40: // Down
                e.preventDefault();
                if (!$current.length) {
                    $next = $items.first();
                } else {
                    $next = $current.next('.alynt-404-search-item');
                    if (!$next.length) {
                        $next = $items.first();
                    }
                }
                selectResult($next);
                break;

            case 38: // Up
                e.preventDefault();
                if (!$current.length) {
                    $next = $items.last();
                } else {
                    $next = $current.prev('.alynt-404-search-item');
                    if (!$next.length) {
                        $next = $items.last();
                    }
                }
                selectResult($next);
                break;

            case 13: // Enter
                if ($current.length) {
                    e.preventDefault();
                    window.location.href = $current.data('url');
                }
                break;

            case 27: // Escape
                e.preventDefault();
                clearResults();
                break;
            }
        }

    /**
     * Select a result item
     * @param {jQuery} $item Result item to select
     */
        function selectResult($item) {
            $('.alynt-404-search-item').attr('aria-selected', 'false');
            $item.attr('aria-selected', 'true');
            $item[0].scrollIntoView({ block: 'nearest' });
        }

    /**
     * Handle result item click
     * @param {Event} e Click event
     */
        function handleResultClick(e) {
            const url = $(this).data('url');
            if (url) {
                window.location.href = url;
            }
        }

    /**
     * Handle click outside search
     * @param {Event} e Click event
     */
        function handleClickOutside(e) {
            const $target = $(e.target);
            if (!$target.closest('.alynt-404-search').length) {
                clearResults();
            }
        }

    /**
     * Show search results
     */
        function showResults() {
            $(elements.searchResults).addClass('active');
        }

    /**
     * Clear search results
     */
        function clearResults() {
            const $results = $(elements.searchResults);
            $results.removeClass('active').empty();
            lastSearchTerm = '';
        }

    /**
     * Show error message
     * @param {string} message Error message
     */
        function showError(message) {
            const $results = $(elements.searchResults);
            $results.html(`
            <div class="alynt-404-search-item error">
                ${message}
            </div>
            `);
            showResults();
        }

    /**
     * Set loading state
     * @param {boolean} loading Loading state
     */
        function setLoadingState(loading) {
            const $search = $('.alynt-404-search');
            isLoading = loading;
            $search.toggleClass('is-loading', loading);
        }

    // Initialize when document is ready
        $(document).ready(init);

    })(jQuery);