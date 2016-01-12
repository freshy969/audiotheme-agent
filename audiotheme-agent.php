<?php
/**
 * AudioTheme Agent
 *
 * @package   AudioTheme\Agent
 * @copyright Copyright (c) 2016, AudioTheme, LLC
 * @license   GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: AudioTheme Agent
 * Plugin URI:  https://audiotheme.com/
 * Description: Priority support and automatic updates for AudioTheme.com premium themes and plugins.
 * Version:     1.0.0
 * Author:      AudioTheme
 * Author URI:  https://audiotheme.com/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: audiotheme-agent
 * Domain Path: /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Autoloader callback.
 *
 * Converts a class name to a file path and requires it if it exists.
 *
 * @since 1.0.0
 *
 * @param string $class Class name.
 */
function audiotheme_agent_autoloader( $class ) {
	if ( 0 !== strpos( $class, 'AudioTheme_Agent_' ) ) {
		return;
	}

	$file  = dirname( __FILE__ ) . '/classes/';
	$file .= str_replace( array( 'AudioTheme_Agent_', '_' ), array( '', '/' ), $class );
	$file .= '.php';

	if ( file_exists( $file ) ) {
		require_once( $file );
	}
}
spl_autoload_register( 'audiotheme_agent_autoloader' );

/**
 * Retrieve the main plugin instance.
 *
 * @since 1.0.0
 *
 * @return AudioTheme_Agent_Plugin
 */
function audiotheme_agent() {
	static $instance;

	if ( null === $instance ) {
		$client   = new AudioTheme_Agent_Client();
		$packages = new AudioTheme_Agent_PackageManager( $client );
		$instance = new AudioTheme_Agent_Plugin( $client, $packages );
	}

	return $instance;
}

$audiotheme_agent = audiotheme_agent();

$audiotheme_agent
	->set_basename( plugin_basename( __FILE__ ) )
	->set_directory( plugin_dir_path( __FILE__ ) )
	->set_file( __FILE__ )
	->set_slug( 'audiotheme-agent' )
	->set_url( plugin_dir_url( __FILE__ ) );

if ( is_admin() ) {
	$audiotheme_agent
		->register_hooks( new AudioTheme_Agent_Provider_I18n() )
		->register_hooks( new AudioTheme_Agent_Provider_AJAX() )
		->register_hooks( $audiotheme_agent->packages )
		->register_hooks( new AudioTheme_Agent_Screen_Main_Subscriptions() );
}

/**
 * Load the plugin.
 */
add_action( 'plugins_loaded', array( $audiotheme_agent, 'load_plugin' ) );
