<?php
/**
 * Sitemap settings tab partial.
 *
 * @package Alynt_404_Sitemap
 * @since   1.0.0
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

// If this file is called directly, abort.

$settings   = get_option( ALYNT_404_PREFIX . 'sitemap_settings', array() );
$post_types = Alynt_404_Post_Types::get_instance()->get_public_post_types();
?>

<div class="alynt-404-settings-section">
	<h2><?php esc_html_e( 'Sitemap Content', 'alynt-404-sitemap' ); ?></h2>

	<!-- Featured Image -->
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row">
				<label for="sitemap_featured_image">
					<?php esc_html_e( 'Featured Image', 'alynt-404-sitemap' ); ?>
				</label>
			</th>
			<td>
				<div class="alynt-404-media-upload">
					<input type="hidden" 
							name="<?php echo esc_attr( ALYNT_404_PREFIX . 'sitemap_settings[featured_image]' ); ?>" 
							id="sitemap_featured_image" 
							value="<?php echo esc_attr( $settings['featured_image'] ?? '' ); ?>" />
					
					<div class="image-preview">
						<?php if ( ! empty( $settings['featured_image'] ) ) : ?>
							<?php echo wp_get_attachment_image( $settings['featured_image'], 'medium' ); ?>
						<?php endif; ?>
					</div>
					
					<button type="button" 
							class="button upload-image-button" 
							data-uploader-title="<?php esc_attr_e( 'Choose Featured Image', 'alynt-404-sitemap' ); ?>"
							data-uploader-button-text="<?php esc_attr_e( 'Select Image', 'alynt-404-sitemap' ); ?>">
						<?php esc_html_e( 'Upload Image', 'alynt-404-sitemap' ); ?>
					</button>
					
					<button type="button" class="button remove-image-button <?php echo empty( $settings['featured_image'] ) ? 'hidden' : ''; ?>">
						<?php esc_html_e( 'Remove Image', 'alynt-404-sitemap' ); ?>
					</button>
				</div>
			</td>
		</tr>

		<!-- Heading -->
		<tr>
			<th scope="row">
				<label for="sitemap_heading">
					<?php esc_html_e( 'Page Heading', 'alynt-404-sitemap' ); ?>
				</label>
			</th>
			<td>
				<input type="text" 
						id="sitemap_heading" 
						name="<?php echo esc_attr( ALYNT_404_PREFIX . 'sitemap_settings[heading]' ); ?>" 
						value="<?php echo esc_attr( $settings['heading'] ?? __( 'Sitemap', 'alynt-404-sitemap' ) ); ?>" 
						class="large-text"
						required />
				<p class="description">
					<?php esc_html_e( 'Main heading displayed on the sitemap page.', 'alynt-404-sitemap' ); ?>
				</p>
			</td>
		</tr>

		<!-- Introduction Message -->
		<tr>
			<th scope="row">
				<label for="sitemap_message">
					<?php esc_html_e( 'Introduction Message', 'alynt-404-sitemap' ); ?>
				</label>
			</th>
			<td>
				<textarea id="sitemap_message" 
							name="<?php echo esc_attr( ALYNT_404_PREFIX . 'sitemap_settings[message]' ); ?>" 
							class="large-text" 
							rows="3" 
							required><?php echo esc_textarea( $settings['message'] ?? __( "Here's our website at a glance. Use this sitemap to quickly find what you're looking for.", 'alynt-404-sitemap' ) ); ?></textarea>
				<p class="description">
					<?php esc_html_e( 'Introductory text displayed below the heading.', 'alynt-404-sitemap' ); ?>
				</p>
			</td>
		</tr>

		<!-- URL Slug -->
		<tr>
			<th scope="row">
				<label for="sitemap_url_slug">
					<?php esc_html_e( 'URL Slug', 'alynt-404-sitemap' ); ?>
				</label>
			</th>
			<td>
				<div class="notice notice-error inline alynt-404-validation-notice" hidden role="alert" aria-live="assertive"></div>
				<input type="text" 
						id="sitemap_url_slug" 
						name="<?php echo esc_attr( ALYNT_404_PREFIX . 'sitemap_settings[url_slug]' ); ?>" 
						value="<?php echo esc_attr( $settings['url_slug'] ?? 'sitemap' ); ?>" 
						class="regular-text"
						required 
						pattern="[a-zA-Z0-9-]+" />
				<p class="description">
					<?php esc_html_e( 'The URL slug for the sitemap page (e.g., "sitemap" for example.com/sitemap/).', 'alynt-404-sitemap' ); ?>
				</p>
			</td>
		</tr>

		<!-- Content Types -->
		<tr>
			<th scope="row">
				<?php esc_html_e( 'Include Content Types', 'alynt-404-sitemap' ); ?>
			</th>
			<td>
				<fieldset>
					<legend class="screen-reader-text">
						<?php esc_html_e( 'Select content types to include in the sitemap', 'alynt-404-sitemap' ); ?>
					</legend>
					<?php foreach ( $post_types as $current_post_type => $object ) : ?>
						<label>
							<input type="checkbox" 
									name="<?php echo esc_attr( ALYNT_404_PREFIX . 'sitemap_settings[post_types][]' ); ?>" 
									value="<?php echo esc_attr( $current_post_type ); ?>"
									<?php checked( in_array( $current_post_type, $settings['post_types'] ?? array( 'post', 'page' ), true ) ); ?> />
							<?php echo esc_html( $object->labels->name ); ?>
						</label><br>
					<?php endforeach; ?>
					<p class="description">
						<?php esc_html_e( 'Select which content types should appear in the sitemap.', 'alynt-404-sitemap' ); ?>
					</p>
				</fieldset>
			</td>
		</tr>

		<!-- Excluded IDs -->
		<tr>
			<th scope="row">
				<label for="sitemap_excluded_ids">
					<?php esc_html_e( 'Excluded Content', 'alynt-404-sitemap' ); ?>
				</label>
			</th>
			<td>
				<input type="text" 
						id="sitemap_excluded_ids" 
						name="<?php echo esc_attr( ALYNT_404_PREFIX . 'sitemap_settings[excluded_ids]' ); ?>" 
						value="<?php echo esc_attr( $settings['excluded_ids'] ?? '' ); ?>" 
						class="large-text"
						placeholder="1,2,3" />
				<p class="description">
					<?php esc_html_e( 'Enter post/page IDs to exclude from the sitemap, separated by commas.', 'alynt-404-sitemap' ); ?>
				</p>
			</td>
		</tr>

		<!-- Layout Settings -->
		<tr>
			<th scope="row"><?php esc_html_e( 'Column Layout', 'alynt-404-sitemap' ); ?></th>
			<td>
				<fieldset>
					<legend class="screen-reader-text">
						<?php esc_html_e( 'Configure column layout for different screen sizes', 'alynt-404-sitemap' ); ?>
					</legend>
					
					<!-- Desktop Columns -->
					<label for="columns_desktop">
						<?php esc_html_e( 'Desktop Columns:', 'alynt-404-sitemap' ); ?>
						<select id="columns_desktop" 
								name="<?php echo esc_attr( ALYNT_404_PREFIX . 'sitemap_settings[columns_desktop]' ); ?>">
							<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
								<option value="<?php echo esc_attr( $i ); ?>" 
										<?php selected( $settings['columns_desktop'] ?? 4, $i ); ?>>
									<?php echo esc_html( $i ); ?>
								</option>
							<?php endfor; ?>
						</select>
					</label><br>

					<!-- Tablet Columns -->
					<label for="columns_tablet">
						<?php esc_html_e( 'Tablet Columns:', 'alynt-404-sitemap' ); ?>
						<select id="columns_tablet" 
								name="<?php echo esc_attr( ALYNT_404_PREFIX . 'sitemap_settings[columns_tablet]' ); ?>">
							<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
								<option value="<?php echo esc_attr( $i ); ?>" 
										<?php selected( $settings['columns_tablet'] ?? 2, $i ); ?>>
									<?php echo esc_html( $i ); ?>
								</option>
							<?php endfor; ?>
						</select>
					</label><br>

					<!-- Mobile Columns -->
					<label for="columns_mobile">
						<?php esc_html_e( 'Mobile Columns:', 'alynt-404-sitemap' ); ?>
						<select id="columns_mobile" 
								name="<?php echo esc_attr( ALYNT_404_PREFIX . 'sitemap_settings[columns_mobile]' ); ?>">
							<?php for ( $i = 1; $i <= 2; $i++ ) : ?>
								<option value="<?php echo esc_attr( $i ); ?>" 
										<?php selected( $settings['columns_mobile'] ?? 1, $i ); ?>>
									<?php echo esc_html( $i ); ?>
								</option>
							<?php endfor; ?>
						</select>
					</label>
					
					<p class="description">
						<?php esc_html_e( 'Configure how many columns should display at different screen sizes.', 'alynt-404-sitemap' ); ?>
					</p>
				</fieldset>
			</td>
		</tr>
	</table>
</div>

<!-- SEO Section -->
<div class="alynt-404-settings-section">
	<h2><?php esc_html_e( 'SEO Settings', 'alynt-404-sitemap' ); ?></h2>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row">
				<label for="sitemap_meta_description">
					<?php esc_html_e( 'Meta Description', 'alynt-404-sitemap' ); ?>
				</label>
			</th>
			<td>
				<textarea id="sitemap_meta_description" 
							name="<?php echo esc_attr( ALYNT_404_PREFIX . 'sitemap_settings[meta_description]' ); ?>" 
							class="large-text" 
							rows="3" 
							maxlength="160"><?php echo esc_textarea( $settings['meta_description'] ?? '' ); ?></textarea>
				<p class="description meta-description-counter">
					<?php esc_html_e( 'Character count: ', 'alynt-404-sitemap' ); ?>
					<span class="counter">0</span>. 
					<?php esc_html_e( 'Recommended length: 50-160 characters.', 'alynt-404-sitemap' ); ?>
				</p>
			</td>
		</tr>
	</table>
</div>

<!-- Custom CSS Section -->
<div class="alynt-404-settings-section">
	<h2><?php esc_html_e( 'Custom CSS', 'alynt-404-sitemap' ); ?></h2>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row">
				<label for="sitemap_custom_css">
					<?php esc_html_e( 'Custom Styles', 'alynt-404-sitemap' ); ?>
				</label>
			</th>
			<td>
				<textarea id="sitemap_custom_css" 
							name="<?php echo esc_attr( ALYNT_404_PREFIX . 'sitemap_settings[custom_css]' ); ?>" 
							class="large-text code" 
							rows="10"><?php echo esc_textarea( $settings['custom_css'] ?? '' ); ?></textarea>
				<p class="description">
					<?php esc_html_e( 'Add custom CSS styles for the sitemap page. These styles will only apply to the sitemap page.', 'alynt-404-sitemap' ); ?>
					<?php esc_html_e( 'Keyboard tip: Tab inserts indentation. Press Escape then Tab to move focus out of this editor.', 'alynt-404-sitemap' ); ?>
				</p>
			</td>
		</tr>
	</table>
</div>


