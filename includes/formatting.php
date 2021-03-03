<?php
/**
 * Formatting functions for taking care of proper number formats and such
 *
 * @package     QCTJ
 * @subpackage  Functions/Formatting
 * @copyright   Copyright (c) 2021, Joe Miller
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Sanitizes a string key for QCTJ Settings
 *
 * Keys are used as internal identifiers. Alphanumeric characters, dashes, underscores, stops, colons and slashes are allowed
 *
 * @since 1.0
 * @param  string $key String key
 * @return string Sanitized key
 */
function qctj_sanitize_key( $key ) {
	$raw_key = $key;
	$key = preg_replace( '/[^a-zA-Z0-9_\-\.\:\/]/', '', $key );

	/**
	 * Filter a sanitized key string.
	 *
	 * @since 1.0
	 * @param string $key     Sanitized key.
	 * @param string $raw_key The key prior to sanitization.
	 */
	return apply_filters( 'qctj_sanitize_key', $key, $raw_key );
}
