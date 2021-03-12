<?php
/**
 * Front-end Actions
 *
 * @package     QCTJ
 * @subpackage  Functions
 * @copyright   Copyright (c) 2021, Joe Miller
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Hooks QCTJ actions, when present in the $_GET superglobal. Every qctj_action
 * present in $_GET is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since 1.0
 * @return void
*/
function qctj_get_actions() {
	$key = ! empty( $_GET['qctj_action'] ) ? sanitize_key( $_GET['qctj_action'] ) : false;

	$is_delayed_action = qctj_is_delayed_action( $key );

	if ( $is_delayed_action ) {
		return;
	}

	if ( ! empty( $key ) ) {
		do_action( "qctj_{$key}" , $_GET );
	}
}
add_action( 'init', 'qctj_get_actions' );

/**
 * Hooks QCTJ actions, when present in the $_POST superglobal. Every qctj_action
 * present in $_POST is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since 1.0
 * @return void
*/
function qctj_post_actions() {
	$key = ! empty( $_POST['qctj_action'] ) ? sanitize_key( $_POST['qctj_action'] ) : false;

	$is_delayed_action = qctj_is_delayed_action( $key );

	if ( $is_delayed_action ) {
		return;
	}

	if ( ! empty( $key ) ) {
		do_action( "qctj_{$key}", $_POST );
	}
}
add_action( 'init', 'qctj_post_actions' );

/**
 * Call any actions that should have been delayed, in order to be sure that all necessary information
 * has been loaded by WP Core.
 *
 * Hooks QCTJ actions, when present in the $_GET superglobal. Every qctj_action
 * present in $_POST is called using WordPress's do_action function. These
 * functions are called on template_redirect.
 *
 * @since 1.0
 * @return void
 */
function qctj_delayed_get_actions() {
	$key = ! empty( $_GET['qctj_action'] ) ? sanitize_key( $_GET['qctj_action'] ) : false;
	$is_delayed_action = qctj_is_delayed_action( $key );

	if ( ! $is_delayed_action ) {
		return;
	}

	if ( ! empty( $key ) ) {
		do_action( "qctj_{$key}", $_GET );
	}
}
add_action( 'template_redirect', 'qctj_delayed_get_actions' );

/**
 * Call any actions that should have been delayed, in order to be sure that all necessary information
 * has been loaded by WP Core.
 *
 * Hooks QCTJ actions, when present in the $_POST superglobal. Every qctj_action
 * present in $_POST is called using WordPress's do_action function. These
 * functions are called on template_redirect.
 *
 * @since 1.0
 * @return void
 */
function qctj_delayed_post_actions() {
	$key = ! empty( $_POST['qctj_action'] ) ? sanitize_key( $_POST['qctj_action'] ) : false;
	$is_delayed_action = qctj_is_delayed_action( $key );

	if ( ! $is_delayed_action ) {
		return;
	}

	if ( ! empty( $key ) ) {
		do_action( "qctj_{$key}", $_POST );
	}
}
add_action( 'template_redirect', 'qctj_delayed_post_actions' );

/**
 * Get the list of actions that QCTJ has determined need to be delayed past init.
 *
 * @since 1.0
 *
 * @return array
 */
function qctj_delayed_actions_list() {
	return (array) apply_filters( 'qctj_delayed_actions', array(
		'add_to_cart'
	) );
}

/**
 * Determine if the requested action needs to be delayed or not.
 *
 * @since 1.0
 *
 * @param string $action
 *
 * @return bool
 */
function qctj_is_delayed_action( $action = '' ) {
	return in_array( $action, qctj_delayed_actions_list() );
}