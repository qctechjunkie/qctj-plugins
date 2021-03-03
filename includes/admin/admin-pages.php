<?php
/**
 * Admin Pages
 *
 * @package     QCTJ
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2021, Joe Miller
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Creates the admin submenu pages under the Downloads menu and assigns their
 * links to global variables
 *
 * @since 1.0
 * @global $qctj_settings_page
 * @return void
 */
function qctj_settings_menu() {
	global $qctj_settings_page, $qctj_tools_page;

	// add settings page
	add_menu_page( __( 'QCTechJunkie Settings', 'qctechjunkie-plugins' ), __( 'QCTechJunkie', 'qctechjunkie-plugins' ), 'manage_options', 'qctj-plugins', 'qctj_options_page','dashicons-sos' );
	$qctj_settings_page = add_submenu_page( 'qctj-plugins', __( 'QCTechJunkie Plugins Settings', 'qctechjunkie-plugins' ), __( 'Settings', 'qctechjunkie-plugins' ), 'manage_options', 'qctj-plugins', 'qctj_options_page' );
	$qctj_tools_page	= add_submenu_page( 'qctj-plugins', __( 'QCTechJunkie Plugins Info and Tools', 'qctechjunkie-plugins' ), __( 'Tools', 'qctechjunkie-plugins' ), 'manage_options', 'qctj-tools', 'qctj_tools_page' );

}
add_action( 'admin_menu', 'qctj_settings_menu' );

/**
 * Determines whether or not the current page is an RCP admin page.
 *
 * @since 1.0
 * @return bool
 */
function qctj_is_admin_page() {

	$screen = get_current_screen();

	global $qctj_settings_page, $qctj_tools_page;
	$pages = array( $qctj_settings_page, $qctj_tools_page,  );

	$is_admin = in_array( $screen->id, $pages );

	/**
	 * Filters whether or not the current page is an RCP admin page.
	 *
	 * @param bool      $is_admin
	 * @param WP_Screen $screen
	 *
	 * @since 1.0
	 */
	return apply_filters( 'qctj_is_admin_page', $is_admin, $screen );

}
