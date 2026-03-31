<?php
/**
 * Partial template for AJAX search form
 *
 * @package Alynt_404_Sitemap
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

?>

<form class="alynt-404-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="alynt-404-search-input" class="screen-reader-text">
		<?php esc_html_e( 'Search website', 'alynt-404-sitemap' ); ?>
	</label>

	<input type="text"
			id="alynt-404-search-input"
			name="s"
			role="combobox"
			autocomplete="off"
			aria-expanded="false"
			aria-controls="alynt-404-search-results"
			aria-owns="alynt-404-search-results"
			aria-haspopup="listbox"
			placeholder="<?php esc_attr_e( 'Search...', 'alynt-404-sitemap' ); ?>" />

	<button type="submit" class="alynt-404-search-submit">
		<?php esc_html_e( 'Search', 'alynt-404-sitemap' ); ?>
	</button>

	<div id="alynt-404-search-results"
		class="alynt-404-search-results"
		role="listbox"
		aria-label="<?php esc_attr_e( 'Search results', 'alynt-404-sitemap' ); ?>">
	</div>

	<div id="alynt-404-search-status"
		class="screen-reader-text"
		aria-live="polite"
		aria-atomic="true"></div>
</form>
