<?php
/**
 * Scripts
 *
 * @package     QCTJ
 * @subpackage  Functions
 * @copyright   Copyright (c) 2021, Joe Miller
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Load Admin Scripts
 *
 * Enqueues the required admin scripts.
 *
 * @since 1.0
 * @global $post
 * @param string $hook Page hook
 * @return void
 */
function qctj_load_admin_scripts( $hook ) {

	if ( ! qctj_is_admin_page() ) {
		return;
	}

	global $post;

	$js_dir  = QCTJ_PLUGIN_URL . 'assets/js/';
	$css_dir = QCTJ_PLUGIN_URL . 'assets/css/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	$suffix  = '';

	// wp_register_script( 'qctj-admin-scripts', $js_dir . 'admin-scripts' . $suffix . '.js', $admin_deps, QCTJ_VERSION, false );
	// 
	// wp_enqueue_script( 'qctj-admin-scripts' );

	wp_register_style( 'qctj-admin', $css_dir . 'qctj-admin' . $suffix . '.css', array(), QCTJ_VERSION );
	wp_enqueue_style( 'qctj-admin' );
}
add_action( 'admin_enqueue_scripts', 'qctj_load_admin_scripts', 100 );

/**
 * Determine if the frontend scripts should be loaded in the footer or header (default: footer)
 *
 * @since 1.0
 * @return mixed
 */
function qctj_scripts_in_footer() {
	return apply_filters( 'qctj_load_scripts_in_footer', true );
}
