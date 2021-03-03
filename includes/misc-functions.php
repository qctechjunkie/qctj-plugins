<?php
/**
 * Misc Functions
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
 * Is Test Mode
 *
 * @since 1.0
 * @return bool $ret True if test mode is enabled, false otherwise
 */
function qctj_is_test_mode() {
	$ret = qctj_get_option( 'test_mode', false );
	return (bool) apply_filters( 'qctj_is_test_mode', $ret );
}

/**
 * Is Debug Mode
 *
 * @since 1.0
 * @return bool $ret True if debug mode is enabled, false otherwise
 */
function qctj_is_debug_mode() {
	$ret = qctj_get_option( 'debug_mode', false );
	if( defined( 'QCTJ_DEBUG_MODE' ) && QCTJ_DEBUG_MODE ) {
		$ret = true;
	}
	return (bool) apply_filters( 'qctj_is_debug_mode', $ret );
}

/**
 * Is Odd
 *
 * Checks whether an integer is odd.
 *
 * @since 1.0
 * @param int     $int The integer to check
 * @return bool Is the integer odd?
 */
function qctj_is_odd( $int ) {
	return (bool) ( $int & 1 );
}

/**
 * Get User IP
 *
 * Returns the IP address of the current visitor
 *
 * @since 1.0
 * @return string $ip User's IP address
 */
function qctj_get_ip() {

	$ip = false;

	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		// Check ip from share internet.
		$ip = filter_var( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ), FILTER_VALIDATE_IP );
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {

		// To check ip is pass from proxy.
		// Can include more than 1 ip, first is the public one.

		// WPCS: sanitization ok.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$ips = explode( ',', wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		if ( is_array( $ips ) ) {
			$ip = filter_var( $ips[0], FILTER_VALIDATE_IP );
		}
	} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = filter_var( wp_unslash( $_SERVER['REMOTE_ADDR'] ), FILTER_VALIDATE_IP );
	}

	$ip = false !== $ip ? $ip : '127.0.0.1';

	// Fix potential CSV returned from $_SERVER variables.
	$ip_array = explode( ',', $ip );
	$ip_array = array_map( 'trim', $ip_array );

	return apply_filters( 'qctj_get_ip', $ip_array[0] );
}


/**
 * Get user host
 *
 * Returns the webhost this site is using if possible
 *
 * @since 1.0
 * @return mixed string $host if detected, false otherwise
 */
function qctj_get_host() {
	$host = false;

	if( defined( 'WPE_APIKEY' ) ) {
		$host = 'WP Engine';
	} elseif( defined( 'PAGELYBIN' ) ) {
		$host = 'Pagely';
	} elseif( DB_HOST == 'localhost:/tmp/mysql5.sock' ) {
		$host = 'ICDSoft';
	} elseif( DB_HOST == 'mysqlv5' ) {
		$host = 'NetworkSolutions';
	} elseif( strpos( DB_HOST, 'ipagemysql.com' ) !== false ) {
		$host = 'iPage';
	} elseif( strpos( DB_HOST, 'ipowermysql.com' ) !== false ) {
		$host = 'IPower';
	} elseif( strpos( DB_HOST, '.gridserver.com' ) !== false ) {
		$host = 'MediaTemple Grid';
	} elseif( strpos( DB_HOST, '.pair.com' ) !== false ) {
		$host = 'pair Networks';
	} elseif( strpos( DB_HOST, '.stabletransit.com' ) !== false ) {
		$host = 'Rackspace Cloud';
	} elseif( strpos( DB_HOST, '.sysfix.eu' ) !== false ) {
		$host = 'SysFix.eu Power Hosting';
	} elseif( isset( $_SERVER['SERVER_NAME'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ), 'Flywheel' ) !== false ) {
		$host = 'Flywheel';
	} else {

		// Adding a general fallback for data gathering.
		if ( isset( $_SERVER['SERVER_NAME'] ) ) {
			$server_name = sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) );
		}

		$host = 'DBH: ' . DB_HOST . ', SRV: ' . $server_name;
	}

	return $host;
}


/**
 * Check site host
 *
 * @since 1.0
 * @param $host The host to check
 * @return bool true if host matches, false if not
 */
function qctj_is_host( $host = false ) {

	$return = false;

	if( $host ) {
		$host = str_replace( ' ', '', strtolower( $host ) );

		switch( $host ) {
			case 'wpengine':
				if( defined( 'WPE_APIKEY' ) )
					$return = true;
				break;
			case 'pagely':
				if( defined( 'PAGELYBIN' ) )
					$return = true;
				break;
			case 'icdsoft':
				if( DB_HOST == 'localhost:/tmp/mysql5.sock' )
					$return = true;
				break;
			case 'networksolutions':
				if( DB_HOST == 'mysqlv5' )
					$return = true;
				break;
			case 'ipage':
				if( strpos( DB_HOST, 'ipagemysql.com' ) !== false )
					$return = true;
				break;
			case 'ipower':
				if( strpos( DB_HOST, 'ipowermysql.com' ) !== false )
					$return = true;
				break;
			case 'mediatemplegrid':
				if( strpos( DB_HOST, '.gridserver.com' ) !== false )
					$return = true;
				break;
			case 'pairnetworks':
				if( strpos( DB_HOST, '.pair.com' ) !== false )
					$return = true;
				break;
			case 'rackspacecloud':
				if( strpos( DB_HOST, '.stabletransit.com' ) !== false )
					$return = true;
				break;
			case 'sysfix.eu':
			case 'sysfix.eupowerhosting':
				if( strpos( DB_HOST, '.sysfix.eu' ) !== false )
					$return = true;
				break;
			case 'flywheel':
				if ( isset( $_SERVER['SERVER_NAME'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ), 'Flywheel' ) !== false )
					$return = true;
				break;
			default:
				$return = false;
		}
	}

	return $return;
}

/**
 * Month Num To Name
 *
 * Takes a month number and returns the name three letter name of it.
 *
 * @since 1.0
 *
 * @param integer $n
 * @return string Short month name
 */
function qctj_month_num_to_name( $n ) {
	$timestamp = mktime( 0, 0, 0, $n, 1, 2005 );

	return date_i18n( "M", $timestamp );
}

/**
 * Get PHP Arg Separator Output
 *
 * @since 1.0
 * @return string Arg separator output
 */
function qctj_get_php_arg_separator_output() {
	return ini_get( 'arg_separator.output' );
}

/**
 * Get the current page URL
 *
 * @since 1.0
 * @param  bool   $nocache  If we should bust cache on the returned URL
 * @return string $page_url Current page URL
 */
function qctj_get_current_page_url( $nocache = false ) {

	global $wp;

	if( get_option( 'permalink_structure' ) ) {

		$base = trailingslashit( home_url( $wp->request ) );

	} else {

		$base = add_query_arg( $wp->query_string, '', trailingslashit( home_url( $wp->request ) ) );
		$base = remove_query_arg( array( 'post_type', 'name' ), $base );

	}

	$scheme = is_ssl() ? 'https' : 'http';
	$uri    = set_url_scheme( $base, $scheme );

	if ( is_front_page() ) {
		$uri = home_url( '/' );
	} elseif ( qctj_is_checkout() ) {
		$uri = qctj_get_checkout_uri();
	}

	$uri = apply_filters( 'qctj_get_current_page_url', $uri );

	if ( $nocache ) {
		$uri = qctj_add_cache_busting( $uri );
	}

	return $uri;
}

/**
 * Adds the 'nocache' parameter to the provided URL
 *
 * @since 1.0
 * @param  string $url The URL being requested
 * @return string      The URL with cache busting added or not
 */
function qctj_add_cache_busting( $url = '' ) {

	$no_cache_checkout = qctj_get_option( 'no_cache_checkout', false );

	if ( qctj_is_caching_plugin_active() || ( qctj_is_checkout() && $no_cache_checkout ) ) {
		$url = add_query_arg( 'nocache', 'true', $url );
	}

	return $url;
}


/**
 * Checks whether function is disabled.
 *
 * @since 1.0
 *
 * @param string  $function Name of the function.
 * @return bool Whether or not function is disabled.
 */
function qctj_is_func_disabled( $function ) {
	$disabled = explode( ',',  ini_get( 'disable_functions' ) );

	return in_array( $function, $disabled );
}

/**
 * QCTJ Let To Num
 *
 * Does Size Conversions
 *
 * @since 1.0
 * @usedby qctj_settings()
 * @author Chris Christoff
 *
 * @param unknown $v
 * @return int
 */
function qctj_let_to_num( $v ) {
	$l   = substr( $v, -1 );
	$ret = substr( $v, 0, -1 );

	switch ( strtoupper( $l ) ) {
		case 'P': // fall-through
		case 'T': // fall-through
		case 'G': // fall-through
		case 'M': // fall-through
		case 'K': // fall-through
			$ret *= 1024;
			break;
		default:
			break;
	}

	return (int) $ret;
}

/**
 * Given an object or array of objects, convert them to arrays
 *
 * @since 1.0
 * @internal Updated in 2.6
 * @param    object|array $object An object or an array of objects
 * @return   array                An array or array of arrays, converted from the provided object(s)
 */
function qctj_object_to_array( $object = array() ) {

	if ( empty( $object ) || ( ! is_object( $object ) && ! is_array( $object ) ) ) {
		return $object;
	}

	if ( is_array( $object ) ) {
		$return = array();
		foreach ( $object as $item ) {
			$return[] = qctj_object_to_array( $item );
		}
	} else {
		$return = get_object_vars( $object );

		// Now look at the items that came back and convert any nested objects to arrays
		foreach ( $return as $key => $value ) {
			$value = ( is_array( $value ) || is_object( $value ) ) ? qctj_object_to_array( $value ) : $value;
			$return[ $key ] = $value;
		}
	}

	return $return;

}


if ( ! function_exists( 'cal_days_in_month' ) ) {
	// Fallback in case the calendar extension is not loaded in PHP
	// Only supports Gregorian calendar
	function cal_days_in_month( $calendar, $month, $year ) {
		return date( 't', mktime( 0, 0, 0, $month, 1, $year ) );
	}
}


if ( ! function_exists( 'hash_equals' ) ) :
/**
 * Compare two strings in constant time.
 *
 * This function was added in PHP 5.6.
 * It can leak the length of a string.
 *
 * @since 1.0
 *
 * @param string $a Expected string.
 * @param string $b Actual string.
 * @return bool Whether strings are equal.
 */
function hash_equals( $a, $b ) {
	$a_length = strlen( $a );
	if ( $a_length !== strlen( $b ) ) {
		return false;
	}
	$result = 0;

	// Do not attempt to "optimize" this.
	for ( $i = 0; $i < $a_length; $i++ ) {
		$result |= ord( $a[ $i ] ) ^ ord( $b[ $i ] );
	}

	return $result === 0;
}
endif;

if ( ! function_exists( 'getallheaders' ) ) :

	/**
	 * Retrieve all headers
	 *
	 * Ensure getallheaders function exists in the case we're using nginx
	 *
	 * @since 1.0
	 * @return array
	 */
	function getallheaders() {
		$headers = array();
		foreach ( $_SERVER as $name => $value ) {
			if ( substr( $name, 0, 5 ) == 'HTTP_' ) {
				$headers[ str_replace( ' ', '-', ucwords( strtolower( str_replace( '_', ' ', substr( $name, 5 ) ) ) ) ) ] = $value;
			}
		}
		return $headers;
	}

endif;

/**
 * Abstraction for WordPress cron checking, to avoid code duplication.
 *
 * In future versions of QCTJ, this function will be changed to only refer to
 * QCTJ specific cron related jobs. You probably won't want to use it until then.
 *
 * @since 1.0
 *
 * @return boolean
 */
function qctj_doing_cron() {

	// Bail if not doing WordPress cron (>4.8.0)
	if ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) {
		return true;

	// Bail if not doing WordPress cron (<4.8.0)
	} elseif ( defined( 'DOING_CRON' ) && ( true === DOING_CRON ) ) {
		return true;
	}

	// Default to false
	return false;
}

/**
 * Check to see if we should be displaying promotional content
 *
 * In various parts of the plugin, we may choose to promote something like a sale for a limited time only. This
 * function should be used to set the conditions under which the promotions will display.
 *
 * @since 1.0
 *
 * @return bool
 */
function qctj_is_promo_active() {

	// Set the date/time range based on UTC.
	$start = strtotime( '2019-11-29 06:00:00' );
	$end   = strtotime( '2019-12-07 05:59:59' );
	$now   = time();

	// Only display sidebar if the page is loaded within the date range.
	if ( ( $now > $start ) && ( $now < $end ) ) {
		return true;
	}

	return false;
}

/**
 * Polyfills for is_countable and is_iterable
 *
 * This helps with plugin compatibility going forward. Many extensions have issues with more modern PHP versions,
 * however unless teh customer is running WP 4.9.6 or PHP 7.3, we cannot use these functions.
 *
 */
if ( ! function_exists( 'is_countable' ) ) {
	/**
	 * Polyfill for is_countable() function added in PHP 7.3 or WP 4.9.6.
	 *
	 * Verify that the content of a variable is an array or an object
	 * implementing the Countable interface.
	 *
	 * @since 1.0
	 *
	 * @param mixed $var The value to check.
	 *
	 * @return bool True if `$var` is countable, false otherwise.
	 */
	function is_countable( $var ) {
		return ( is_array( $var )
		         || $var instanceof Countable
		         || $var instanceof SimpleXMLElement
		         || $var instanceof ResourceBundle
		);
	}
}

if ( ! function_exists( 'is_iterable' ) ) {
	/**
	 * Polyfill for is_iterable() function added in PHP 7.1  or WP 4.9.6.
	 *
	 * Verify that the content of a variable is an array or an object
	 * implementing the Traversable interface.
	 *
	 * @since 1.0
	 *
	 * @param mixed $var The value to check.
	 *
	 * @return bool True if `$var` is iterable, false otherwise.
	 */
	function is_iterable( $var ) {
		return ( is_array( $var ) || $var instanceof Traversable );
	}
}
