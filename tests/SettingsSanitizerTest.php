<?php
/**
 * Tests for sitemap settings sanitization edge cases.
 *
 * @package Alynt_404_Sitemap
 */

use Brain\Monkey;
use Brain\Monkey\Functions;

class SettingsSanitizerTest extends \PHPUnit\Framework\TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		if ( ! defined( 'OBJECT' ) ) {
			define( 'OBJECT', 'OBJECT' );
		}

		$this->mock_common_wordpress_functions();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_sanitize_sitemap_settings_falls_back_to_default_when_slug_is_empty() {
		Functions\when( 'get_option' )->alias(
			function ( $name, $default = null ) {
				if ( ALYNT_404_PREFIX . 'sitemap_settings' === $name ) {
					return array(
						'url_slug' => 'current-slug',
					);
				}

				if ( 'rewrite_rules' === $name ) {
					return array();
				}

				return $default;
			}
		);

		$sanitizer = new Alynt_404_Settings_Sanitizer();
		$result    = $sanitizer->sanitize_sitemap_settings(
			array(
				'url_slug'     => '   ',
				'post_types'   => array( 'post' ),
				'excluded_ids' => '',
			)
		);

		$this->assertSame( 'sitemap', $result['url_slug'] );
	}

	public function test_sanitize_sitemap_settings_generates_unique_slug_for_taken_routes() {
		Functions\when( 'get_option' )->alias(
			function ( $name, $default = null ) {
				if ( ALYNT_404_PREFIX . 'sitemap_settings' === $name ) {
					return array(
						'url_slug' => 'current-slug',
					);
				}

				if ( 'rewrite_rules' === $name ) {
					return array();
				}

				return $default;
			}
		);

		Functions\when( 'get_page_by_path' )->alias(
			function ( $slug ) {
				if ( 'taken' === $slug ) {
					return (object) array( 'ID' => 99 );
				}

				return null;
			}
		);

		$sanitizer = new Alynt_404_Settings_Sanitizer();
		$result    = $sanitizer->sanitize_sitemap_settings(
			array(
				'url_slug'     => 'taken',
				'post_types'   => array( 'post' ),
				'excluded_ids' => '',
			)
		);

		$this->assertSame( 'taken-2', $result['url_slug'] );
	}

	private function mock_common_wordpress_functions() {
		Functions\when( '__' )->alias(
			function ( $text ) {
				return $text;
			}
		);

		Functions\when( 'sanitize_text_field' )->alias(
			function ( $value ) {
				return trim( wp_strip_all_tags( (string) $value ) );
			}
		);

		Functions\when( 'sanitize_title' )->alias(
			function ( $value ) {
				$value = strtolower( trim( (string) $value ) );
				$value = preg_replace( '/[^a-z0-9]+/', '-', $value );

				return trim( (string) $value, '-' );
			}
		);

		Functions\when( 'wp_parse_args' )->alias(
			function ( $args, $defaults = array() ) {
				$args     = is_array( $args ) ? $args : array();
				$defaults = is_array( $defaults ) ? $defaults : array();

				return array_merge( $defaults, $args );
			}
		);

		Functions\when( 'wp_strip_all_tags' )->alias(
			function ( $value ) {
				return strip_tags( (string) $value );
			}
		);

		Functions\when( 'absint' )->alias(
			function ( $value ) {
				return abs( (int) $value );
			}
		);

		Functions\when( 'wp_get_attachment_image' )->justReturn( false );
		Functions\when( 'add_settings_error' )->justReturn( null );
		Functions\when( 'get_transient' )->justReturn( false );
		Functions\when( 'set_transient' )->justReturn( true );
		Functions\when( 'apply_filters' )->alias(
			function ( $hook, $value ) {
				return $value;
			}
		);
		Functions\when( 'esc_url_raw' )->alias(
			function ( $url ) {
				return filter_var( (string) $url, FILTER_SANITIZE_URL );
			}
		);
		Functions\when( 'get_posts' )->justReturn( array() );
		Functions\when( 'get_taxonomies' )->justReturn( array() );
		Functions\when( 'get_post_types' )->alias(
			function ( $args = array(), $output = 'names' ) {
				if ( 'objects' === $output ) {
					return array();
				}

				return array(
					'post' => 'post',
					'page' => 'page',
				);
			}
		);
		Functions\when( 'get_post_type_object' )->alias(
			function ( $post_type ) {
				return (object) array(
					'labels'      => (object) array(
						'name'          => ucfirst( $post_type ) . 's',
						'singular_name' => ucfirst( $post_type ),
					),
					'has_archive' => false,
					'rewrite'     => array(
						'slug' => $post_type,
					),
				);
			}
		);
		Functions\when( 'get_page_by_path' )->justReturn( null );
	}
}
