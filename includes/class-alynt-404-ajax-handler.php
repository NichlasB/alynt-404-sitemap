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
	 * Perform search query.
	 *
	 * @since 1.0.0
	 * @param string $search_term The search term.
	 * @param array  $post_types Array of post types to search.
	 * @return array Search results.
	 */
	private function perform_search( $search_term, $post_types ) {
		$result_limit = $this->get_search_result_limit();
		$args         = array(
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
					$post_type_object               = get_post_type_object( $post_type );
					$post_type_labels[ $post_type ] = $post_type_object ? $post_type_object->labels->singular_name : ucfirst( $post_type );
				}

				$results[] = array(
					'title' => wp_strip_all_tags( get_the_title() ),
					'url'   => get_permalink(),
					'type'  => sanitize_text_field( $post_type_labels[ $post_type ] ),
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
		$ip = $this->get_client_ip();
		if ( '' === $ip ) {
			return true;
		}

		$user_agent    = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		$transient_key = ALYNT_404_PREFIX . 'rate_limit_' . md5( $ip . '|' . $user_agent );
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
		$remote_addr = $this->get_server_ip_header( 'REMOTE_ADDR' );
		if ( '' === $remote_addr ) {
			return '';
		}

		// Only trust forwarded headers when the request came through a configured proxy.
		if ( ! $this->request_came_through_trusted_proxy( $remote_addr ) ) {
			return $remote_addr;
		}

		foreach ( $this->get_trusted_proxy_headers() as $header_name ) {
			$forwarded_ip = $this->get_server_ip_header( $header_name, true );
			if ( '' !== $forwarded_ip ) {
				return $forwarded_ip;
			}
		}

		return $remote_addr;
	}

	/**
	 * Get a validated IP address from a server header.
	 *
	 * @since 1.0.4
	 * @param string $header_name     Server header name.
	 * @param bool   $allow_ip_lists  Whether to parse comma-delimited proxy header values.
	 * @return string
	 */
	private function get_server_ip_header( $header_name, $allow_ip_lists = false ) {
		if ( ! isset( $_SERVER[ $header_name ] ) ) {
			return '';
		}

		$raw_value = sanitize_text_field( wp_unslash( $_SERVER[ $header_name ] ) );
		if ( '' === $raw_value ) {
			return '';
		}

		$candidates = $allow_ip_lists ? explode( ',', $raw_value ) : array( $raw_value );
		foreach ( $candidates as $candidate ) {
			$ip = trim( $candidate );
			if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return $ip;
			}
		}

		return '';
	}

	/**
	 * Determine whether the request originated from a trusted proxy.
	 *
	 * Site owners can allow specific proxy IPs or CIDR ranges via the
	 * `alynt_404_trusted_proxies` filter before forwarded client IP headers are used.
	 *
	 * @since 1.0.4
	 * @param string $remote_addr Direct client address reported by the web server.
	 * @return bool
	 */
	private function request_came_through_trusted_proxy( $remote_addr ) {
		$trusted_proxies = apply_filters( ALYNT_404_PREFIX . 'trusted_proxies', array() );
		if ( ! is_array( $trusted_proxies ) || empty( $trusted_proxies ) ) {
			return false;
		}

		foreach ( $trusted_proxies as $trusted_proxy ) {
			if ( $this->ip_matches_trusted_proxy( $remote_addr, $trusted_proxy ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the forwarded headers that may be used for trusted proxy requests.
	 *
	 * @since 1.0.4
	 * @return array
	 */
	private function get_trusted_proxy_headers() {
		$allowed_headers = array(
			'HTTP_CF_CONNECTING_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_REAL_IP',
		);

		$headers = apply_filters(
			ALYNT_404_PREFIX . 'trusted_proxy_headers',
			array(
				'HTTP_CF_CONNECTING_IP',
				'HTTP_X_FORWARDED_FOR',
			)
		);

		if ( ! is_array( $headers ) ) {
			return array();
		}

		return array_values( array_intersect( array_map( 'strval', $headers ), $allowed_headers ) );
	}

	/**
	 * Check whether an IP matches a trusted proxy IP or CIDR range.
	 *
	 * @since 1.0.4
	 * @param string $ip            Direct client address reported by the web server.
	 * @param mixed  $trusted_proxy Exact IP or CIDR range.
	 * @return bool
	 */
	private function ip_matches_trusted_proxy( $ip, $trusted_proxy ) {
		$ip            = filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '';
		$trusted_proxy = is_string( $trusted_proxy ) ? trim( $trusted_proxy ) : '';

		if ( '' === $ip || '' === $trusted_proxy ) {
			return false;
		}

		if ( false === strpos( $trusted_proxy, '/' ) ) {
			return $ip === $trusted_proxy;
		}

		return $this->ip_is_in_cidr_range( $ip, $trusted_proxy );
	}

	/**
	 * Check whether an IP falls within a CIDR range.
	 *
	 * @since 1.0.4
	 * @param string $ip   IP address.
	 * @param string $cidr CIDR range.
	 * @return bool
	 */
	private function ip_is_in_cidr_range( $ip, $cidr ) {
		list( $subnet, $mask_length ) = array_pad( explode( '/', $cidr, 2 ), 2, null );
		$subnet                       = filter_var( $subnet, FILTER_VALIDATE_IP ) ? $subnet : '';

		if ( '' === $subnet || null === $mask_length || ! is_numeric( $mask_length ) ) {
			return false;
		}

		$ip_binary     = inet_pton( $ip );
		$subnet_binary = inet_pton( $subnet );
		if ( false === $ip_binary || false === $subnet_binary || strlen( $ip_binary ) !== strlen( $subnet_binary ) ) {
			return false;
		}

		$mask_length = (int) $mask_length;
		$bit_length  = strlen( $ip_binary ) * 8;
		if ( $mask_length < 0 || $mask_length > $bit_length ) {
			return false;
		}

		$full_bytes     = intdiv( $mask_length, 8 );
		$remaining_bits = $mask_length % 8;

		if ( $full_bytes > 0 && substr( $ip_binary, 0, $full_bytes ) !== substr( $subnet_binary, 0, $full_bytes ) ) {
			return false;
		}

		if ( 0 === $remaining_bits ) {
			return true;
		}

		$mask = ( 0xFF << ( 8 - $remaining_bits ) ) & 0xFF;
		return ( ord( $ip_binary[ $full_bytes ] ) & $mask ) === ( ord( $subnet_binary[ $full_bytes ] ) & $mask );
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
}
