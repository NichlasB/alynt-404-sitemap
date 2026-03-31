<?php
/**
 * Handle all AJAX operations.
 *
 * @package Alynt_404_Sitemap
 * @since   1.0.0
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

/**
 * Handles AJAX requests for the plugin.
 *
 * @since 1.0.0
 */
class Alynt_404_Ajax_Handler {

	/**
	 * Duration for rate limiting in seconds.
	 *
	 * @var int
	 */
	private $rate_limit_duration;

	/**
	 * Maximum number of requests allowed per duration.
	 *
	 * @var int
	 */
	private $rate_limit_requests;

	/**
	 * Initialize the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Allow five requests every ten seconds.
		$this->rate_limit_duration = 10;
		$this->rate_limit_requests = 5;

		// Register AJAX actions.
		add_action( 'wp_ajax_alynt_404_search', array( $this, 'handle_search' ) );
		add_action( 'wp_ajax_nopriv_alynt_404_search', array( $this, 'handle_search' ) );
	}

	/**
	 * Handle AJAX search requests.
	 *
	 * @since 1.0.0
	 */
	public function handle_search() {
		// Verify nonce.
		if ( ! check_ajax_referer( ALYNT_404_PREFIX . 'search_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Your search session expired. Refresh the page and try again.', 'alynt-404-sitemap' ) ) );
			return;
		}

		if ( ! $this->check_rate_limit() ) {
			wp_send_json_error( array( 'message' => __( 'Too many searches were sent in a short time. Please wait a moment and try again.', 'alynt-404-sitemap' ) ) );
			return;
		}

		// Sanitize and validate search input.
		$search_term = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
		if ( empty( $search_term ) ) {
			wp_send_json_error( array( 'message' => __( 'Enter a search term to see matching content.', 'alynt-404-sitemap' ) ) );
			return;
		}

		// Get enabled post types for search.
		$settings   = get_option( ALYNT_404_PREFIX . '404_settings', array() );
		$post_types = ! empty( $settings['search_post_types'] ) ? $settings['search_post_types'] : array( 'post', 'page' );

		// Perform search.
		$results = $this->perform_search( $search_term, $post_types );

		wp_send_json_success(
			array(
				'results' => $results,
				'count'   => count( $results ),
			)
		);
	}

	/**
	 * Handle settings save requests.
	 *
	 * @since 1.0.0
	 */
	public function save_settings() {
		// Verify nonce and capabilities.
		if ( ! check_ajax_referer( ALYNT_404_PREFIX . 'settings_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => 'Invalid security token.' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions.' ) );
		}

		// Validate and sanitize settings.
		$settings_type = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : '';
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nested settings are recursively sanitized in sanitize_settings().
		$settings = isset( $_POST['settings'] ) ? $this->sanitize_settings( wp_unslash( $_POST['settings'] ) ) : array();

		if ( empty( $settings_type ) || empty( $settings ) ) {
			wp_send_json_error( array( 'message' => 'Invalid settings data.' ) );
		}

		// Update settings based on type.
		switch ( $settings_type ) {
			case 'colors':
				$this->save_color_settings( $settings );
				break;
			case '404':
				$this->save_404_settings( $settings );
				break;
			case 'sitemap':
				$this->save_sitemap_settings( $settings );
				break;
			default:
				wp_send_json_error( array( 'message' => 'Invalid settings type.' ) );
		}

		wp_send_json_success( array( 'message' => 'Settings saved successfully.' ) );
	}

	/**
	 * Perform search query.
	 *
	 * @since 1.0.0
	 * @param string $search_term The search term.
	 * @param array  $post_types Array of post types to search.
	 * @return array Search results.
	 */
	private function perform_search( $search_term, $post_types ) {
		$result_limit = $this->get_search_result_limit();
		$args = array(
			'post_type'              => $post_types,
			'post_status'            => 'publish',
			's'                      => $search_term,
			'orderby'                => 'relevance',
			'posts_per_page'         => $result_limit,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		);

		$query            = new WP_Query( $args );
		$results          = array();
		$post_type_labels = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_type = get_post_type();

				if ( ! isset( $post_type_labels[ $post_type ] ) ) {
					$post_type_object             = get_post_type_object( $post_type );
					$post_type_labels[ $post_type ] = $post_type_object ? $post_type_object->labels->singular_name : ucfirst( $post_type );
				}

				$results[] = array(
					'title' => get_the_title(),
					'url'   => get_permalink(),
					'type'  => $post_type_labels[ $post_type ],
				);
			}
		}

		wp_reset_postdata();
		return $results;
	}

	/**
	 * Check rate limiting.
	 *
	 * @since 1.0.0
	 * @return boolean True if request is allowed, false if rate limited.
	 */
	private function check_rate_limit() {
		$ip            = $this->get_client_ip();
		$transient_key = ALYNT_404_PREFIX . 'rate_limit_' . md5( $ip );
		$requests      = get_transient( $transient_key );

		if ( false === $requests ) {
			set_transient( $transient_key, 1, $this->rate_limit_duration );
			return true;
		}

		if ( $requests >= $this->rate_limit_requests ) {
			return false;
		}

		set_transient( $transient_key, $requests + 1, $this->rate_limit_duration );
		return true;
	}

	/**
	 * Get client IP address.
	 *
	 * @since 1.0.0
	 * @return string IP address.
	 */
	private function get_client_ip() {
		$ip = '';

		// Prefer forwarded client IP headers before falling back to REMOTE_ADDR.
		if ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = trim( current( explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) ) ) );
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '';
	}

	/**
	 * Sanitize settings array.
	 *
	 * @since 1.0.0
	 * @param array $settings Raw settings array.
	 * @return array Sanitized settings array.
	 */
	private function sanitize_settings( $settings ) {
		if ( ! is_array( $settings ) ) {
			return array();
		}

		$sanitized = array();
		foreach ( $settings as $key => $value ) {
			$key = sanitize_key( $key );

			if ( is_array( $value ) ) {
				$sanitized[ $key ] = $this->sanitize_settings( $value );
			} else {
				switch ( $key ) {
					case 'custom_css':
						$sanitized[ $key ] = wp_strip_all_tags( $value );
						break;
					case 'meta_description':
						$sanitized[ $key ] = sanitize_text_field( $value );
						break;
					case 'excluded_ids':
						$sanitized[ $key ] = sanitize_text_field( $value );
						break;
					default:
						$sanitized[ $key ] = sanitize_text_field( $value );
				}
			}
		}

		return $sanitized;
	}

	/**
	 * Save color settings.
	 *
	 * @since 1.0.0
	 * @param array $settings Color settings array.
	 */
	private function save_color_settings( $settings ) {
		// Validate hex colors.
		foreach ( $settings as $key => $color ) {
			if ( ! preg_match( '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/', $color ) ) {
				wp_send_json_error( array( 'message' => 'Invalid color format for ' . $key ) );
			}
		}

		update_option( ALYNT_404_PREFIX . 'colors', $settings );
	}

	/**
	 * Save 404 page settings.
	 *
	 * @since 1.0.0
	 * @param array $settings 404 page settings array.
	 */
	private function save_404_settings( $settings ) {
		// Validate post types.
		if ( ! empty( $settings['search_post_types'] ) ) {
			$settings['search_post_types'] = array_intersect(
				$settings['search_post_types'],
				array_keys( get_post_types( array( 'public' => true ) ) )
			);
		}

		update_option( ALYNT_404_PREFIX . '404_settings', $settings );
	}

	/**
	 * Save sitemap settings.
	 *
	 * @since 1.0.0
	 * @param array $settings Sitemap settings array.
	 */
	private function save_sitemap_settings( $settings ) {
		// Validate post types.
		if ( ! empty( $settings['post_types'] ) ) {
			$settings['post_types'] = array_intersect(
				$settings['post_types'],
				array_keys( get_post_types( array( 'public' => true ) ) )
			);
		}

		// Validate excluded IDs.
		if ( ! empty( $settings['excluded_ids'] ) ) {
			$ids                    = array_map( 'trim', explode( ',', $settings['excluded_ids'] ) );
			$settings['excluded_ids'] = implode( ',', $this->get_valid_post_ids( $ids ) );
		}

		update_option( ALYNT_404_PREFIX . 'sitemap_settings', $settings );
	}

	/**
	 * Get the maximum number of search results to return.
	 *
	 * @since 1.0.3
	 * @return int
	 */
	private function get_search_result_limit() {
		return max( 1, (int) apply_filters( ALYNT_404_PREFIX . 'search_result_limit', 10 ) );
	}

	/**
	 * Validate post IDs with a single batched query.
	 *
	 * @since 1.0.3
	 * @param array $ids Raw post IDs.
	 * @return array
	 */
	private function get_valid_post_ids( $ids ) {
		$post_ids = array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );

		if ( empty( $post_ids ) ) {
			return array();
		}

		return get_posts(
			array(
				'post_type'              => 'any',
				'post_status'            => 'any',
				'post__in'               => $post_ids,
				'posts_per_page'         => count( $post_ids ),
				'orderby'                => 'post__in',
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'ignore_sticky_posts'    => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);
	}
}
