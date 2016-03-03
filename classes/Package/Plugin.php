<?php
/**
 * Plugin package.
 *
 * @package   AudioTheme\Agent
 * @copyright Copyright 2016 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     1.0.0
 */

/**
 * Plugin package class.
 *
 * @package AudioTheme\Agent
 * @since   1.0.0
 */
class AudioTheme_Agent_Package_Plugin extends AudioTheme_Agent_Package_AbstractPackage {
	/**
	 * Plugin file.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $file;

	/**
	 * Whether the plugin is active.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	public function is_active() {
		return $this->is_installed() && is_plugin_active( $this->get_file() );
	}

	/**
	 * Retrieve the plugin file.
	 *
	 * @since 1.0.0
	 *
	 * @return string Relative path to the plugin file from the root plugins directory.
	 */
	public function get_file() {
		return $this->file;
	}

	/**
	 * Set the plugin file.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file Relative path to the plugin file from the root plugins directory.
	 */
	public function set_file( $file ) {
		$this->file = $file;
		return $this;
	}

	/**
	 * Retrieve the package type.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_type() {
		return 'plugin';
	}

	/**
	 * Retrieve a human-readable type label.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_type_label() {
		return esc_html__( 'Plugin', 'audiotheme-agent' );
	}

	/**
	 * Retrieve a button to manage the package.
	 *
	 * @todo Unavailable, expired/requires renewal
	 * @todo Update available
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_action_button() {
		$html = '';

		if ( $this->is_installed() && $this->is_active() ) {
			$html = sprintf( '<span class="button button-disabled">%s</span>', esc_html__( 'Installed', 'audiotheme-agent' ) );
		} elseif ( $this->is_installed() && ! $this->is_active() && current_user_can( 'activate_plugins' ) ) {
			$html = sprintf(
				'<a href="%s" class="button">%s</a>',
				esc_url( $this->get_activation_url() ),
				esc_html__( 'Activate', 'audiotheme-agent' )
			);
		} elseif ( ! $this->is_installed() && current_user_can( 'install_plugins' ) && $this->has_download_url() ) {
			$html = sprintf(
				'<a href="%s" class="button js-install" data-slug="%s">%s</a>',
				esc_url( '' ),
				esc_attr( $this->get_slug() ),
				esc_html__( 'Install Now', 'audiotheme-agent' )
			);
		}

		return $html;
	}

	/**
	 * Install the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return true|null|WP_Error
	 */
	public function install() {
		if ( $this->is_installed() ) {
			return new WP_Error( 'cannot_reinstall', esc_html__( 'The plugin is already installed.', 'audiotheme-agent' ) );
		}

		if ( ! current_user_can( 'install_plugins' ) ) {
			return new WP_Error( 'unauthorized', esc_html__( 'You do not have sufficient permissions to install plugins on this site.', 'audiotheme-agent' ) );
		}

		$upgrader = new Plugin_Upgrader( new Automatic_Upgrader_Skin() );
		$result   = $upgrader->install( $this->get_download_url() );

		if ( true === $result ) {
			$plugins = get_plugins( '/' . $this->get_slug() );
			$plugin_file = $this->get_slug() . '/' . key( $plugins );

			$this
				->set_file( $plugin_file )
				->set_installed( true )
				->set_installed_version( $this->get_current_version() );
		}

		return $result;
	}

	/**
	 * Retrieve an activatoin URL for the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_activation_url() {
		$args = array(
			'action'   => 'activate',
			'plugin'   => $this->get_file(),
			'referrer' => 'audiotheme-agent',
		);

		return wp_nonce_url(
			add_query_arg(
				$args,
				self_admin_url( 'plugins.php' )
			),
			'activate-plugin_' . $this->get_file()
		);
	}
}
