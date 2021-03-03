<?php
/**
 * Register Settings
 *
 * @package     QCTJ
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2021, Joe Miller
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @since 1.0
 * @global $qctj_options Array of all the QCTJ Options
 * @return mixed
 */
function qctj_get_option( $key = '', $default = false ) {
	global $qctj_options;
	$value = ! empty( $qctj_options[ $key ] ) ? $qctj_options[ $key ] : $default;
	$value = apply_filters( 'qctj_get_option', $value, $key, $default );
	return apply_filters( 'qctj_get_option_' . $key, $value, $key, $default );
}

/**
 * Update an option
 *
 * Updates an edd setting value in both the db and the global variable.
 * Warning: Passing in an empty, false or null string value will remove
 *          the key from the qctj_options array.
 *
 * @since 1.0
 * @param string $key The Key to update
 * @param string|bool|int $value The value to set the key to
 * @global $qctj_options Array of all the QCTJ Options
 * @return boolean True if updated, false if not.
 */
function qctj_update_option( $key = '', $value = false ) {

	// If no key, exit
	if ( empty( $key ) ){
		return false;
	}

	if ( empty( $value ) ) {
		$remove_option = qctj_delete_option( $key );
		return $remove_option;
	}

	// First let's grab the current settings
	$options = get_option( 'qctj_settings' );

	// Let's let devs alter that value coming in
	$value = apply_filters( 'qctj_update_option', $value, $key );

	// Next let's try to update the value
	$options[ $key ] = $value;
	$did_update = update_option( 'qctj_settings', $options );

	// If it updated, let's update the global variable
	if ( $did_update ){
		global $qctj_options;
		$qctj_options[ $key ] = $value;

	}

	return $did_update;
}

/**
 * Remove an option
 *
 * Removes an edd setting value in both the db and the global variable.
 *
 * @since 1.0
 * @param string $key The Key to delete
 * @global $qctj_options Array of all the QCTJ Options
 * @return boolean True if removed, false if not.
 */
function qctj_delete_option( $key = '' ) {
	global $qctj_options;

	// If no key, exit
	if ( empty( $key ) ){
		return false;
	}

	// First let's grab the current settings
	$options = get_option( 'qctj_settings' );

	// Next let's try to update the value
	if( isset( $options[ $key ] ) ) {

		unset( $options[ $key ] );

	}

	// Remove this option from the global QCTJ settings to the array_merge in qctj_settings_sanitize() doesn't re-add it.
	if( isset( $qctj_options[ $key ] ) ) {

		unset( $qctj_options[ $key ] );

	}

	$did_update = update_option( 'qctj_settings', $options );

	// If it updated, let's update the global variable
	if ( $did_update ){
		global $qctj_options;
		$qctj_options = $options;
	}

	return $did_update;
}

/**
 * Get Settings
 *
 * Retrieves all plugin settings
 *
 * @since 1.0
 * @return array QCTJ settings
 */
function qctj_get_settings() {

	$settings = get_option( 'qctj_settings' );

	if( empty( $settings ) ) {

		// Update old settings with new single option

		$general_settings = is_array( get_option( 'qctj_settings_general' ) )    ? get_option( 'qctj_settings_general' )    : array();
		$ext_settings     = is_array( get_option( 'qctj_settings_extensions' ) ) ? get_option( 'qctj_settings_extensions' ) : array();
		$license_settings = is_array( get_option( 'qctj_settings_licenses' ) )   ? get_option( 'qctj_settings_licenses' )   : array();

		$settings = array_merge( $general_settings, $ext_settings, $license_settings );

		update_option( 'qctj_settings', $settings );

	}
	return apply_filters( 'qctj_get_settings', $settings );
}

/**
 * Add all settings sections and fields
 *
 * @since 1.0
 * @return void
*/
function qctj_register_settings() {

	if ( false == get_option( 'qctj_settings' ) ) {
		add_option( 'qctj_settings' );
	}

	foreach ( qctj_get_registered_settings() as $tab => $sections ) {
		foreach ( $sections as $section => $settings) {

			// Check for backwards compatibility
			$section_tabs = qctj_get_settings_tab_sections( $tab );
			if ( ! is_array( $section_tabs ) || ! array_key_exists( $section, $section_tabs ) ) {
				$section = 'main';
				$settings = $sections;
			}

			add_settings_section(
				'qctj_settings_' . $tab . '_' . $section,
				__return_null(),
				'__return_false',
				'qctj_settings_' . $tab . '_' . $section
			);

			foreach ( $settings as $option ) {
				// For backwards compatibility
				if ( empty( $option['id'] ) ) {
					continue;
				}

				$args = wp_parse_args( $option, array(
				    'section'       => $section,
				    'id'            => null,
				    'desc'          => '',
				    'name'          => '',
				    'size'          => null,
				    'options'       => '',
				    'std'           => '',
				    'min'           => null,
				    'max'           => null,
				    'step'          => null,
				    'chosen'        => null,
				    'multiple'      => null,
				    'placeholder'   => null,
				    'allow_blank'   => true,
				    'readonly'      => false,
				    'faux'          => false,
				    'tooltip_title' => false,
				    'tooltip_desc'  => false,
				    'field_class'   => '',
				) );

				add_settings_field(
					'qctj_settings[' . $args['id'] . ']',
					$args['name'],
					function_exists( 'qctj_' . $args['type'] . '_callback' ) ? 'qctj_' . $args['type'] . '_callback' : 'qctj_missing_callback',
					'qctj_settings_' . $tab . '_' . $section,
					'qctj_settings_' . $tab . '_' . $section,
					$args
				);
			}
		}

	}

	// Creates our settings in the options table
	register_setting( 'qctj_settings', 'qctj_settings', 'qctj_settings_sanitize' );

}
add_action( 'admin_init', 'qctj_register_settings' );

/**
 * Retrieve the array of plugin settings
 *
 * @since 1.0
 * @return array
*/
function qctj_get_registered_settings() {

	/**
	 * 'Whitelisted' QCTJ settings, filters are provided for each settings
	 * section to allow extensions and other plugins to add their own settings
	 */

	$qctj_settings = array(
		/** General Settings */
		'general' => apply_filters( 'qctj_settings_general',
			array(
				'main' => array(
					'tracking_settings' => array(
						'id'   => 'tracking_settings',
						'name' => '<h3>' . __( 'Tracking', 'qctechjunkie-plugins' ) . '</h3>',
						'desc' => '',
						'type' => 'header',
					),
					'allow_tracking' => array(
						'id'   => 'allow_tracking',
						'name' => __( 'Allow Usage Tracking?', 'qctechjunkie-plugins' ),
						'desc' => sprintf(
							__( 'Allow QCTechJunkie to anonymously track how this plugin is used and help us make the plugin better. Opt-in to tracking and our newsletter and immediately be emailed a discount to the QCTJ shop, valid towards the <a href="%s" target="_blank">purchase of extensions</a>. No sensitive data is tracked.', 'qctechjunkie-plugins' ),
							'https://qctechjunkie.com/downloads/?utm_source=' . substr( md5( get_bloginfo( 'name' ) ), 0, 10 ) . '&utm_medium=admin&utm_term=settings&utm_campaign=QCTJUsageTracking'
						),
						'type' => 'checkbox',
					),
				),
			)
		),
		/** Extension Settings */
		'extensions' => apply_filters('qctj_settings_extensions',	array()	),
		'licenses' => apply_filters('qctj_settings_licenses',	array()	),
	);

	return apply_filters( 'qctj_registered_settings', $qctj_settings );
}

/**
 * Settings Sanitization
 *
 * Adds a settings error (for the updated message)
 * At some point this will validate input
 *
 * @since 1.0
 *
 * @param array $input The value inputted in the field
 * @global array $qctj_options Array of all the QCTJ Options
 *
 * @return string $input Sanitized value
 */
function qctj_settings_sanitize( $input = array() ) {
	global $qctj_options;

	$doing_section = false;
	if ( ! empty( $_POST['_wp_http_referer'] ) ) {
		$doing_section = true;
	}

	$setting_types = qctj_get_registered_settings_types();
	$input         = $input ? $input : array();

	if ( $doing_section ) {

		parse_str( $_POST['_wp_http_referer'], $referrer ); // Pull out the tab and section
		$tab      = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';
		$section  = isset( $referrer['section'] ) ? $referrer['section'] : 'main';

		if ( ! empty( $_POST['qctj_section_override'] ) ) {
			$section = sanitize_text_field( $_POST['qctj_section_override'] );
		}

		$setting_types = qctj_get_registered_settings_types( $tab, $section );

		// Run a general sanitization for the tab for special fields (like taxes)
		$input = apply_filters( 'qctj_settings_' . $tab . '_sanitize', $input );

		// Run a general sanitization for the section so custom tabs with sub-sections can save special data
		$input = apply_filters( 'qctj_settings_' . $tab . '-' . $section . '_sanitize', $input );

	}

	// Merge our new settings with the existing
	$output = array_merge( $qctj_options, $input );

	foreach ( $setting_types as $key => $type ) {

		if ( empty( $type ) ) {
			continue;
		}

		// Some setting types are not actually settings, just keep moving along here
		$non_setting_types = apply_filters( 'qctj_non_setting_types', array(
			'header', 'descriptive_text', 'hook',
		) );

		if ( in_array( $type, $non_setting_types ) ) {
			continue;
		}

		if ( array_key_exists( $key, $output ) ) {
			$output[ $key ] = apply_filters( 'qctj_settings_sanitize_' . $type, $output[ $key ], $key );
			$output[ $key ] = apply_filters( 'qctj_settings_sanitize', $output[ $key ], $key );
		}

		if ( $doing_section ) {
			switch( $type ) {
				case 'checkbox':
				case 'multicheck':
					if ( array_key_exists( $key, $input ) && $output[ $key ] === '-1' ) {
						unset( $output[ $key ] );
					}
					break;
				case 'text':
					if ( array_key_exists( $key, $input ) && empty( $input[ $key ] ) ) {
						unset( $output[ $key ] );
					}
					break;
				default:
					if ( array_key_exists( $key, $input ) && empty( $input[ $key ] ) || ( array_key_exists( $key, $output ) && ! array_key_exists( $key, $input ) ) ) {
						unset( $output[ $key ] );
					}
					break;
			}
		} else {
			if ( empty( $input[ $key ] ) ) {
				unset( $output[ $key ] );
			}
		}

	}

	if ( $doing_section ) {
		add_settings_error( 'qctj-notices', '', __( 'Settings updated.', 'qctechjunkie-plugins' ), 'updated' );
	}

	return $output;
}

/**
 * Flattens the set of registered settings and their type so we can easily sanitize all the settings
 * in a much cleaner set of logic in qctj_settings_sanitize
 *
 * @since 1.0
 *
 * @param $filtered_tab bool|string     A tab to filter setting types by.
 * @param $filtered_section bool|string A section to filter setting types by.
 * @return array Key is the setting ID, value is the type of setting it is registered as
 */
function qctj_get_registered_settings_types( $filtered_tab = false, $filtered_section = false ) {
	$settings      = qctj_get_registered_settings();
	$setting_types = array();
	foreach ( $settings as $tab_id => $tab ) {

		if ( false !== $filtered_tab && $filtered_tab !== $tab_id ) {
			continue;
		}

		foreach ( $tab as $section_id => $section_or_setting ) {

			// See if we have a setting registered at the tab level for backwards compatibility
			if ( false !== $filtered_section && is_array( $section_or_setting ) && array_key_exists( 'type', $section_or_setting ) ) {
				$setting_types[ $section_or_setting['id'] ] = $section_or_setting['type'];
				continue;
			}

			if ( false !== $filtered_section && $filtered_section !== $section_id ) {
				continue;
			}

			foreach ( $section_or_setting as $section => $section_settings ) {

				if ( ! empty( $section_settings['type'] ) ) {
					$setting_types[ $section_settings['id'] ] = $section_settings['type'];
				}

			}

		}

	}

	return $setting_types;
}

/**
 * Misc Accounting Settings Sanitization
 *
 * @since 1.0
 * @param array $input The value inputted in the field
 * @return string $input Sanitized value
 */
function qctj_settings_sanitize_misc_accounting( $input ) {

	if( ! current_user_can( 'manage_options' ) ) {
		return $input;
	}

	if( ! empty( $input['enable_sequential'] ) && ! qctj_get_option( 'enable_sequential' ) ) {

		// Shows an admin notice about upgrading previous order numbers
		QCTJ()->session->set( 'upgrade_sequential', '1' );

	}

	return $input;
}
add_filter( 'qctj_settings_misc-accounting_sanitize', 'qctj_settings_sanitize_misc_accounting' );

/**
 * Sanitize text fields
 *
 * @since 1.0
 * @param array $input The field value
 * @return string $input Sanitized value
 */
function qctj_sanitize_text_field( $input ) {
	$tags = array(
		'p' => array(
			'class' => array(),
			'id'    => array(),
		),
		'span' => array(
			'class' => array(),
			'id'    => array(),
		),
		'a' => array(
			'href'   => array(),
			'target' => array(),
			'title'  => array(),
			'class'  => array(),
			'id'     => array(),
		),
		'strong' => array(),
		'em' => array(),
		'br' => array(),
		'img' => array(
			'src'   => array(),
			'title' => array(),
			'alt'   => array(),
			'id'    => array(),
		),
		'div' => array(
			'class' => array(),
			'id'    => array(),
		),
		'ul' => array(
			'class' => array(),
			'id'    => array(),
		),
		'li' => array(
			'class' => array(),
			'id'    => array(),
		)
	);

	$allowed_tags = apply_filters( 'qctj_allowed_html_tags', $tags );

	return trim( wp_kses( $input, $allowed_tags ) );
}
add_filter( 'qctj_settings_sanitize_text', 'qctj_sanitize_text_field' );

/**
 * Sanitize HTML Class Names
 *
 * @since 1.0
 * @param  string|array $class HTML Class Name(s)
 * @return string $class
 */
function qctj_sanitize_html_class( $class = '' ) {

	if ( is_string( $class ) ) {
		$class = sanitize_html_class( $class );
	} else if ( is_array( $class ) ) {
		$class = array_values( array_map( 'sanitize_html_class', $class ) );
		$class = implode( ' ', array_unique( $class ) );
	}

	return $class;

}

/**
 * Retrieve settings tabs
 *
 * @since 1.0
 * @return array $tabs
 */
function qctj_get_settings_tabs() {

	$settings = qctj_get_registered_settings();

	$tabs             = array();
	$tabs['general']  = __( 'General', 'qctechjunkie-plugins' );

	if( ! empty( $settings['extensions'] ) ) {
		$tabs['extensions'] = __( 'Extensions', 'qctechjunkie-plugins' );
	}
	if( ! empty( $settings['licenses'] ) ) {
		$tabs['licenses'] = __( 'Licenses', 'qctechjunkie-plugins' );
	}

	return apply_filters( 'qctj_settings_tabs', $tabs );
}

/**
 * Retrieve settings tabs
 *
 * @since 1.0
 * @return array $section
 */
function qctj_get_settings_tab_sections( $tab = false ) {

	$tabs     = array();
	$sections = qctj_get_registered_settings_sections();

	if( $tab && ! empty( $sections[ $tab ] ) ) {
		$tabs = $sections[ $tab ];
	} else if ( $tab ) {
		$tabs = array();
	}

	return $tabs;
}

/**
 * Get the settings sections for each tab
 * Uses a static to avoid running the filters on every request to this function
 *
 * @since 1.0
 * @return array Array of tabs and sections
 */
function qctj_get_registered_settings_sections() {

	static $sections = false;

	if ( false !== $sections ) {
		return $sections;
	}

	$sections = array(
		'general'    => apply_filters( 'qctj_settings_sections_general', array(
			'main'               => __( 'General', 'qctechjunkie-plugins' ),
		) ),
		'extensions' => apply_filters( 'qctj_settings_sections_extensions', array(
			'main'               => __( 'Main', 'qctechjunkie-plugins' )
		) ),
		'licenses'   => apply_filters( 'qctj_settings_sections_licenses', array() ),
		'misc'       => apply_filters( 'qctj_settings_sections_misc', array(
			'main'               => __( 'Miscellaneous', 'qctechjunkie-plugins' ),
		) ),
	);

	$sections = apply_filters( 'qctj_settings_sections', $sections );

	return $sections;
}

/**
 * Retrieve a list of all published pages
 *
 * On large sites this can be expensive, so only load if on the settings page or $force is set to true
 *
 * @since 1.0
 * @param bool $force Force the pages to be loaded even if not on settings
 * @return array $pages_options An array of the pages
 */
function qctj_get_pages( $force = false ) {

	$pages_options = array( '' => '' ); // Blank option

	if( ( ! isset( $_GET['page'] ) || 'qctj-settings' != $_GET['page'] ) && ! $force ) {
		return $pages_options;
	}

	$pages = get_pages();
	if ( $pages ) {
		foreach ( $pages as $page ) {
			$pages_options[ $page->ID ] = $page->post_title;
		}
	}

	return $pages_options;
}

/**
 * Header Callback
 *
 * Renders the header.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @return void
 */
function qctj_header_callback( $args ) {
	echo apply_filters( 'qctj_after_setting_output', '', $args );
}

/**
 * Checkbox Callback
 *
 * Renders checkboxes.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function qctj_checkbox_callback( $args ) {
	$qctj_option = qctj_get_option( $args['id'] );

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$name = '';
	} else {
		$name = 'name="qctj_settings[' . qctj_sanitize_key( $args['id'] ) . ']"';
	}

	$class = qctj_sanitize_html_class( $args['field_class'] );

	$checked  = ! empty( $qctj_option ) ? checked( 1, $qctj_option, false ) : '';
	$html     = '<input type="hidden"' . $name . ' value="-1" />';
	$html    .= '<input type="checkbox" id="qctj_settings[' . qctj_sanitize_key( $args['id'] ) . ']"' . $name . ' value="1" ' . $checked . ' class="' . $class . '"/>';
	$html    .= '<label for="qctj_settings[' . qctj_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'qctj_after_setting_output', $html, $args );
}

/**
 * Multicheck Callback
 *
 * Renders multiple checkboxes.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function qctj_multicheck_callback( $args ) {
	$qctj_option = qctj_get_option( $args['id'] );

	$class = qctj_sanitize_html_class( $args['field_class'] );

	$html = '';
	if ( ! empty( $args['options'] ) ) {
		$html .= '<input type="hidden" name="qctj_settings[' . qctj_sanitize_key( $args['id'] ) . ']" value="-1" />';
		foreach( $args['options'] as $key => $option ):
			if( isset( $qctj_option[ $key ] ) ) { $enabled = $option; } else { $enabled = NULL; }
			$html .= '<input name="qctj_settings[' . qctj_sanitize_key( $args['id'] ) . '][' . qctj_sanitize_key( $key ) . ']" id="qctj_settings[' . qctj_sanitize_key( $args['id'] ) . '][' . qctj_sanitize_key( $key ) . ']" class="' . $class . '" type="checkbox" value="' . esc_attr( $option ) . '" ' . checked($option, $enabled, false) . '/>&nbsp;';
			$html .= '<label for="qctj_settings[' . qctj_sanitize_key( $args['id'] ) . '][' . qctj_sanitize_key( $key ) . ']">' . wp_kses_post( $option ) . '</label><br/>';
		endforeach;
		$html .= '<p class="description">' . $args['desc'] . '</p>';
	}

	echo apply_filters( 'qctj_after_setting_output', $html, $args );
}

/**
 * Radio Callback
 *
 * Renders radio boxes.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function qctj_radio_callback( $args ) {
	$qctj_options = qctj_get_option( $args['id'] );

	$html = '';

	$class = qctj_sanitize_html_class( $args['field_class'] );

	foreach ( $args['options'] as $key => $option ) :
		$checked = false;

		if ( $qctj_options && $qctj_options == $key )
			$checked = true;
		elseif( isset( $args['std'] ) && $args['std'] == $key && ! $qctj_options )
			$checked = true;

		$html .= '<input name="qctj_settings[' . qctj_sanitize_key( $args['id'] ) . ']" id="qctj_settings[' . qctj_sanitize_key( $args['id'] ) . '][' . qctj_sanitize_key( $key ) . ']" class="' . $class . '" type="radio" value="' . qctj_sanitize_key( $key ) . '" ' . checked(true, $checked, false) . '/>&nbsp;';
		$html .= '<label for="qctj_settings[' . qctj_sanitize_key( $args['id'] ) . '][' . qctj_sanitize_key( $key ) . ']">' . esc_html( $option ) . '</label><br/>';
	endforeach;

	$html .= '<p class="description">' . apply_filters( 'qctj_after_setting_output', wp_kses_post( $args['desc'] ), $args ) . '</p>';

	echo $html;
}

/**
 * Text Callback
 *
 * Renders text fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function qctj_text_callback( $args ) {
	$qctj_option = qctj_get_option( $args['id'] );

	if ( $qctj_option ) {
		$value = $qctj_option;
	} elseif( ! empty( $args['allow_blank'] ) && empty( $qctj_option ) ) {
		$value = '';
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$args['readonly'] = true;
		$value = isset( $args['std'] ) ? $args['std'] : '';
		$name  = '';
	} else {
		$name = 'name="qctj_settings[' . esc_attr( $args['id'] ) . ']"';
	}

	$class = qctj_sanitize_html_class( $args['field_class'] );

	$disabled = ! empty( $args['disabled'] ) ? ' disabled="disabled"' : '';
	$readonly = $args['readonly'] === true ? ' readonly="readonly"' : '';
	$size     = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html     = '<input type="text" class="' . $class . ' ' . sanitize_html_class( $size ) . '-text" id="qctj_settings[' . qctj_sanitize_key( $args['id'] ) . ']" ' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"' . $readonly . $disabled . ' placeholder="' . esc_attr( $args['placeholder'] ) . '"/>';
	$html    .= '<label for="qctj_settings[' . qctj_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'qctj_after_setting_output', $html, $args );
}

/**
 * Email Callback
 *
 * Renders email fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function qctj_email_callback( $args ) {
	$qctj_option = qctj_get_option( $args['id'] );

	if ( $qctj_option ) {
		$value = $qctj_option;
	} elseif( ! empty( $args['allow_blank'] ) && empty( $qctj_option ) ) {
		$value = '';
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$args['readonly'] = true;
		$value = isset( $args['std'] ) ? $args['std'] : '';
		$name  = '';
	} else {
		$name = 'name="qctj_settings[' . esc_attr( $args['id'] ) . ']"';
	}

	$class = qctj_sanitize_html_class( $args['field_class'] );

	$disabled = ! empty( $args['disabled'] ) ? ' disabled="disabled"' : '';
	$readonly = $args['readonly'] === true ? ' readonly="readonly"' : '';
	$size     = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html     = '<input type="email" class="' . $class . ' ' . sanitize_html_class( $size ) . '-text" id="qctj_settings[' . qctj_sanitize_key( $args['id'] ) . ']" ' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"' . $readonly . $disabled . ' placeholder="' . esc_attr( $args['placeholder'] ) . '"/>';
	$html    .= '<label for="qctj_settings[' . qctj_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'qctj_after_setting_output', $html, $args );
}

/**
 * Number Callback
 *
 * Renders number fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function qctj_number_callback( $args ) {
	$qctj_option = qctj_get_option( $args['id'] );

	if ( $qctj_option ) {
		$value = $qctj_option;
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$args['readonly'] = true;
		$value = isset( $args['std'] ) ? $args['std'] : '';
		$name  = '';
	} else {
		$name = 'name="qctj_settings[' . esc_attr( $args['id'] ) . ']"';
	}

	$class = qctj_sanitize_html_class( $args['field_class'] );

	$max  = isset( $args['max'] ) ? $args['max'] : 999999;
	$min  = isset( $args['min'] ) ? $args['min'] : 0;
	$step = isset( $args['step'] ) ? $args['step'] : 1;

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="number" step="' . esc_attr( $step ) . '" max="' . esc_attr( $max ) . '" min="' . esc_attr( $min ) . '" class="' . $class . ' ' . sanitize_html_class( $size ) . '-text" id="qctj_settings[' . qctj_sanitize_key( $args['id'] ) . ']" ' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<label for="qctj_settings[' . qctj_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'qctj_after_setting_output', $html, $args );
}

/**
 * Textarea Callback
 *
 * Renders textarea fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function qctj_textarea_callback( $args ) {
	$qctj_option = qctj_get_option( $args['id'] );

	if ( $qctj_option ) {
		$value = $qctj_option;
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$class = qctj_sanitize_html_class( $args['field_class'] );

	$html = '<textarea class="' . $class . ' large-text" cols="50" rows="5" id="qctj_settings[' . qctj_sanitize_key( $args['id'] ) . ']" name="qctj_settings[' . esc_attr( $args['id'] ) . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
	$html .= '<label for="qctj_settings[' . qctj_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'qctj_after_setting_output', $html, $args );
}

/**
 * Missing Callback
 *
 * If a function is missing for settings callbacks alert the user.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @return void
 */
function qctj_missing_callback($args) {
	printf(
		__( 'The callback function used for the %s setting is missing.', 'qctechjunkie-plugins' ),
		'<strong>' . $args['id'] . '</strong>'
	);
}

/**
 * Select Callback
 *
 * Renders select fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function qctj_select_callback($args) {
	$qctj_option = qctj_get_option( $args['id'] );

	if ( $qctj_option ) {
		$value = $qctj_option;
	} else {

		// Properly set default fallback if the Select Field allows Multiple values
		if ( empty( $args['multiple'] ) ) {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		} else {
			$value = ! empty( $args['std'] ) ? $args['std'] : array();
		}

	}

	if ( isset( $args['placeholder'] ) ) {
		$placeholder = $args['placeholder'];
	} else {
		$placeholder = '';
	}

	$class = qctj_sanitize_html_class( $args['field_class'] );

	if ( isset( $args['chosen'] ) ) {
		$class .= ' qctj-select-chosen';
	}

	$nonce = isset( $args['data']['nonce'] )
		? ' data-nonce="' . sanitize_text_field( $args['data']['nonce'] ) . '" '
		: '';

	// If the Select Field allows Multiple values, save as an Array
	$name_attr = 'qctj_settings[' . esc_attr( $args['id'] ) . ']';
	$name_attr = ( $args['multiple'] ) ? $name_attr . '[]' : $name_attr;

	$html = '<select ' . $nonce . ' id="qctj_settings[' . qctj_sanitize_key( $args['id'] ) . ']" name="' . $name_attr . '" class="' . $class . '" data-placeholder="' . esc_html( $placeholder ) . '" ' . ( ( $args['multiple'] ) ? 'multiple="true"' : '' ) . '>';

	foreach ( $args['options'] as $option => $name ) {

		if ( ! $args['multiple'] ) {
			$selected = selected( $option, $value, false );
			$html .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
		} else {
			// Do an in_array() check to output selected attribute for Multiple
			$html .= '<option value="' . esc_attr( $option ) . '" ' . ( ( in_array( $option, $value ) ) ? 'selected="true"' : '' ) . '>' . esc_html( $name ) . '</option>';
		}

	}

	$html .= '</select>';
	$html .= '<label for="qctj_settings[' . qctj_sanitize_key( $args['id'] ) . ']"> ' . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'qctj_after_setting_output', $html, $args );
}

/**
 * Descriptive text callback.
 *
 * Renders descriptive text onto the settings field.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @return void
 */
function qctj_descriptive_text_callback( $args ) {
	$html = wp_kses_post( $args['desc'] );

	echo apply_filters( 'qctj_after_setting_output', $html, $args );
}

/**
 * Registers the license field callback for Software Licensing
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
if ( ! function_exists( 'qctj_license_key_callback' ) ) {
	function qctj_license_key_callback( $args ) {
		$qctj_option = qctj_get_option( $args['id'] );

		$messages = array();
		$license  = get_option( $args['options']['is_valid_license_option'] );

		if ( $qctj_option ) {
			$value = $qctj_option;
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		if( ! empty( $license ) && is_object( $license ) ) {

			// activate_license 'invalid' on anything other than valid, so if there was an error capture it
			if ( false === $license->success ) {

				switch( $license->error ) {

					case 'expired' :

						$class = 'expired';
						$messages[] = sprintf(
							__( 'Your license key expired on %s. Please <a href="%s" target="_blank">renew your license key</a>.', 'qctechjunkie-plugins' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ),
							'https://qctechjunkie.com/checkout/?qctj_license_key=' . $value . '&utm_campaign=admin&utm_source=licenses&utm_medium=expired'
						);

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'revoked' :

						$class = 'error';
						$messages[] = sprintf(
							__( 'Your license key has been disabled. Please <a href="%s" target="_blank">contact support</a> for more information.', 'qctechjunkie-plugins' ),
							'https://qctechjunkie.com/support?utm_campaign=admin&utm_source=licenses&utm_medium=revoked'
						);

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'missing' :

						$class = 'error';
						$messages[] = sprintf(
							__( 'Invalid license. Please <a href="%s" target="_blank">visit your account page</a> and verify it.', 'qctechjunkie-plugins' ),
							'https://qctechjunkie.com/your-account?utm_campaign=admin&utm_source=licenses&utm_medium=missing'
						);

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'invalid' :
					case 'site_inactive' :

						$class = 'error';
						$messages[] = sprintf(
							__( 'Your %s is not active for this URL. Please <a href="%s" target="_blank">visit your account page</a> to manage your license key URLs.', 'qctechjunkie-plugins' ),
							$args['name'],
							'https://qctechjunkie.com/your-account?utm_campaign=admin&utm_source=licenses&utm_medium=invalid'
						);

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'item_name_mismatch' :

						$class = 'error';
						$messages[] = sprintf( __( 'This appears to be an invalid license key for %s.', 'qctechjunkie-plugins' ), $args['name'] );

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'no_activations_left':

						$class = 'error';
						$messages[] = sprintf( __( 'Your license key has reached its activation limit. <a href="%s">View possible upgrades</a> now.', 'qctechjunkie-plugins' ), 'https://qctechjunkie.com/your-account/' );

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'license_not_activable':

						$class = 'error';
						$messages[] = __( 'The key you entered belongs to a bundle, please use the product specific license key.', 'qctechjunkie-plugins' );

						$license_status = 'license-' . $class . '-notice';
						break;

					default :

						$class = 'error';
						$error = ! empty(  $license->error ) ?  $license->error : __( 'unknown_error', 'qctechjunkie-plugins' );
						$messages[] = sprintf( __( 'There was an error with this license key: %s. Please <a href="%s">contact our support team</a>.', 'qctechjunkie-plugins' ), $error, 'https://qctechjunkie.com/support' );

						$license_status = 'license-' . $class . '-notice';
						break;
				}

			} else {

				switch( $license->license ) {

					case 'valid' :
					default:

						$class = 'valid';

						$now        = current_time( 'timestamp' );
						$expiration = strtotime( $license->expires, current_time( 'timestamp' ) );

						if( 'lifetime' === $license->expires ) {

							$messages[] = __( 'License key never expires.', 'qctechjunkie-plugins' );

							$license_status = 'license-lifetime-notice';

						} elseif( $expiration > $now && $expiration - $now < ( DAY_IN_SECONDS * 30 ) ) {

							$messages[] = sprintf(
								__( 'Your license key expires soon! It expires on %s. <a href="%s" target="_blank">Renew your license key</a>.', 'qctechjunkie-plugins' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ),
								'https://qctechjunkie.com/checkout/?qctj_license_key=' . $value . '&utm_campaign=admin&utm_source=licenses&utm_medium=renew'
							);

							$license_status = 'license-expires-soon-notice';

						} else {

							$messages[] = sprintf(
								__( 'Your license key expires on %s.', 'qctechjunkie-plugins' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) )
							);

							$license_status = 'license-expiration-date-notice';

						}

						break;

				}

			}

		} else {
			$class = 'empty';

			$messages[] = sprintf(
				__( 'To receive updates, please enter your valid %s license key.', 'qctechjunkie-plugins' ),
				$args['name']
			);

			$license_status = null;
		}

		$class .= ' ' . qctj_sanitize_html_class( $args['field_class'] );

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<input type="text" class="' . sanitize_html_class( $size ) . '-text" id="qctj_settings[' . qctj_sanitize_key( $args['id'] ) . ']" name="qctj_settings[' . qctj_sanitize_key( $args['id'] ) . ']" value="' . esc_attr( $value ) . '"/>';

		if ( ( is_object( $license ) && 'valid' == $license->license ) || 'valid' == $license ) {
			$html .= '<input type="submit" class="button-secondary" name="' . $args['id'] . '_deactivate" value="' . __( 'Deactivate License',  'qctechjunkie-plugins' ) . '"/>';
		}

		$html .= '<label for="qctj_settings[' . qctj_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

		if ( ! empty( $messages ) ) {
			foreach( $messages as $message ) {

				$html .= '<div class="qctj-license-data qctj-license-' . $class . ' ' . $license_status . '">';
					$html .= '<p>' . $message . '</p>';
				$html .= '</div>';

			}
		}

		wp_nonce_field( qctj_sanitize_key( $args['id'] ) . '-nonce', qctj_sanitize_key( $args['id'] ) . '-nonce' );

		echo $html;
	}
}

/**
 * Hook Callback
 *
 * Adds a do_action() hook in place of the field
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @return void
 */
function qctj_hook_callback( $args ) {
	do_action( 'qctj_' . $args['id'], $args );
}
