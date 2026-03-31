<?php
/**
 * Render and handle the plugin admin page.
 *
 * @package Alynt_404_Sitemap
 * @since   1.0.3
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

/**
 * Renders the plugin admin page and handles tab-level actions.
 *
 * @since 1.0.3
 */
class Alynt_404_Admin_Page {

	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * Active settings tab.
	 *
	 * @var string
	 */
	private $active_tab;

	/**
	 * Constructor.
	 *
	 * @since 1.0.3
	 * @param string $plugin_name Plugin slug.
	 */
	public function __construct( $plugin_name ) {
		$this->plugin_name = $plugin_name;
		$this->active_tab  = $this->resolve_active_tab();
	}

	/**
	 * Display admin page.
	 *
	 * @since 1.0.3
	 * @return void Renders the plugin admin settings page.
	 */
	public function display_plugin_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->handle_reset();
		$this->show_reset_notice_if_present();
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php settings_errors(); ?>
			<?php $this->render_tabs(); ?>
			<div class="settings-forms">
				<form method="post" action="options.php" class="main-form alynt-404-form">
					<?php settings_fields( ALYNT_404_PREFIX . $this->active_tab . '_settings' ); ?>
					<?php $this->render_active_tab(); ?>
					<p class="submit">
						<?php submit_button( null, 'primary alynt-404-submit', 'submit', false, array( 'data-loading-text' => __( 'Saving…', 'alynt-404-sitemap' ) ) ); ?>
					</p>
				</form>

				<form method="post" class="reset-form">
					<?php wp_nonce_field( 'reset_settings_action' ); ?>
					<button type="submit" name="reset_settings" class="button button-secondary alynt-404-reset-button" data-loading-text="<?php echo esc_attr__( 'Resetting…', 'alynt-404-sitemap' ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to reset these settings to defaults?', 'alynt-404-sitemap' ) ); ?>');">
						<?php esc_html_e( 'Reset to Defaults', 'alynt-404-sitemap' ); ?>
					</button>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Resolve active tab.
	 *
	 * @since 1.0.3
	 * @return string Active tab key.
	 */
	private function resolve_active_tab() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading the tab query arg only changes which settings section is displayed.
		$tab     = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general';
		$allowed = array( 'general', '404', 'sitemap' );
		return in_array( $tab, $allowed, true ) ? $tab : 'general';
	}

	/**
	 * Handle reset submissions.
	 *
	 * @since 1.0.3
	 * @return void Resets the current tab to its default values when requested.
	 */
	private function handle_reset() {
		if ( ! isset( $_POST['reset_settings'] ) ) {
			return;
		}

		check_admin_referer( 'reset_settings_action' );

		if ( ! Alynt_404_Settings_Defaults::reset_tab( $this->active_tab ) ) {
			add_settings_error(
				'alynt_404_messages',
				'settings_reset_failed',
				__( 'Could not reset these settings. Please try again.', 'alynt-404-sitemap' ),
				'error'
			);
			return;
		}

		if ( $this->active_tab === 'general' ) {
			$css_result = Alynt_404_Color_Manager::get_instance()->regenerate_css();
			if ( is_wp_error( $css_result ) ) {
				return;
			}
		}

		add_settings_error(
			'alynt_404_messages',
			'settings_reset',
			__( 'Settings have been reset to defaults.', 'alynt-404-sitemap' ),
			'updated'
		);
	}

	/**
	 * Render nav tabs.
	 *
	 * @since 1.0.3
	 * @return void Outputs the admin navigation tabs.
	 */
	private function render_tabs() {
		?>
		<h2 class="nav-tab-wrapper">
			<a href="?page=<?php echo esc_attr( $this->plugin_name ); ?>&tab=general" class="nav-tab <?php echo $this->active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'General', 'alynt-404-sitemap' ); ?>
			</a>
			<a href="?page=<?php echo esc_attr( $this->plugin_name ); ?>&tab=404" class="nav-tab <?php echo $this->active_tab === '404' ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e( '404 Page', 'alynt-404-sitemap' ); ?>
			</a>
			<a href="?page=<?php echo esc_attr( $this->plugin_name ); ?>&tab=sitemap" class="nav-tab <?php echo $this->active_tab === 'sitemap' ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'Sitemap', 'alynt-404-sitemap' ); ?>
			</a>
		</h2>
		<?php
	}

	/**
	 * Render active tab partial.
	 *
	 * @since 1.0.3
	 * @return void Loads the tab partial for the active settings section.
	 */
	private function render_active_tab() {
		$tab_file = plugin_dir_path( __FILE__ ) . 'tabs/tab-' . $this->active_tab . '.php';
		if ( file_exists( $tab_file ) ) {
			require $tab_file;
			return;
		}

		require plugin_dir_path( __FILE__ ) . 'tabs/tab-general.php';
	}

	/**
	 * Keep compatibility for existing transient-based notice flows.
	 *
	 * @since 1.0.3
	 * @return void Displays any persisted reset notice and clears its transient.
	 */
	private function show_reset_notice_if_present() {
		if ( ! get_transient( 'alynt_404_reset_message' ) ) {
			return;
		}

		delete_transient( 'alynt_404_reset_message' );
		add_settings_error(
			'alynt_404_messages',
			'settings_reset',
			__( 'Settings have been reset to defaults.', 'alynt-404-sitemap' ),
			'updated'
		);
	}
}


