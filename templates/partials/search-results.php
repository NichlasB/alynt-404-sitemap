<?php
/**
 * Partial template for AJAX search form
 *
 * @package Alynt_404_Sitemap
 */

if (!defined('WPINC')) {
    die;
}
?>

<div class="alynt-404-search" role="search">
    <label for="alynt-404-search-input" class="screen-reader-text">
        <?php esc_html_e('Search website', 'alynt-404-sitemap'); ?>
    </label>
    
    <input type="text" 
           id="alynt-404-search-input" 
           autocomplete="off"
           aria-expanded="false"
           aria-controls="alynt-404-search-results"
           aria-owns="alynt-404-search-results"
           placeholder="<?php esc_attr_e('Search...', 'alynt-404-sitemap'); ?>" />
    
    <div id="alynt-404-search-results" 
         class="alynt-404-search-results" 
         role="listbox" 
         aria-label="<?php esc_attr_e('Search results', 'alynt-404-sitemap'); ?>">
    </div>
</div>