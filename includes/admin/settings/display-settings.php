<?php
/**
 * Admin Options Page
 *
 * @package     QCTJ
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2021, Joe Miller
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Options Page
 *
 * Renders the options page contents.
 *
 * @since 1.0
 * @return void
 */
function qctj_options_page() {

	$settings_tabs = qctj_get_settings_tabs();
	$settings_tabs = empty($settings_tabs) ? array() : $settings_tabs;
	$active_tab    = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
	$active_tab    = array_key_exists( $active_tab, $settings_tabs ) ? $active_tab : 'general';
	$sections      = qctj_get_settings_tab_sections( $active_tab );
	$key           = 'main';

	if ( ! empty( $sections ) ) {
		$key = key( $sections );
	}

	$registered_sections = qctj_get_settings_tab_sections( $active_tab );
	$section             = isset( $_GET['section'] ) && ! empty( $registered_sections ) && array_key_exists( $_GET['section'], $registered_sections ) ? sanitize_text_field( $_GET['section'] ) : $key;

	// Unset 'main' if it's empty and default to the first non-empty if it's the chosen section
	$all_settings = qctj_get_registered_settings();

	// Let's verify we have a 'main' section to show
	$has_main_settings = true;
	if ( empty( $all_settings[ $active_tab ]['main'] ) ) {
		$has_main_settings = false;
	}

	// Check for old non-sectioned settings (see #4211 and #5171)
	if ( ! $has_main_settings ) {
		foreach( $all_settings[ $active_tab ] as $sid => $stitle ) {
			if ( is_string( $sid ) && ! empty( $sections) && array_key_exists( $sid, $sections ) ) {
				continue;
			} else {
				$has_main_settings = true;
				break;
			}
		}
	}

	$override = false;
	if ( false === $has_main_settings ) {
		unset( $sections['main'] );

		if ( 'main' === $section ) {
			foreach ( $sections as $section_key => $section_title ) {
				if ( ! empty( $all_settings[ $active_tab ][ $section_key ] ) ) {
					$section  = $section_key;
					$override = true;
					break;
				}
			}
		}
	}

	ob_start();
	?>
	<div class="wrap <?php echo 'wrap-' . $active_tab; ?>">
		<h2><?php _e( 'QCTechJunkie Settings', 'qctechjunkie-plugins' ); ?></h2>
		<h2 class="nav-tab-wrapper">
			<?php
			foreach ( qctj_get_settings_tabs() as $tab_id => $tab_name ) {
				$tab_url = add_query_arg( array(
					'settings-updated' => false,
					'tab'              => $tab_id,
				) );

				// Remove the section from the tabs so we always end up at the main section
				$tab_url = remove_query_arg( 'section', $tab_url );

				$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

				echo '<a href="' . esc_url( $tab_url ) . '" class="nav-tab' . $active . '">';
					echo esc_html( $tab_name );
				echo '</a>';
			}
			?>
		</h2>
		<?php

		$number_of_sections = count( $sections );
		$number = 0;
		if ( $number_of_sections > 1 ) {
			echo '<div class="wp-clearfix"><ul class="subsubsub">';
			foreach( $sections as $section_id => $section_name ) {
				echo '<li>';
				$number++;
				$tab_url = add_query_arg( array(
					'settings-updated' => false,
					'tab' => $active_tab,
					'section' => $section_id
				) );
				$class = '';
				if ( $section == $section_id ) {
					$class = 'current';
				}
				echo '<a class="' . $class . '" href="' . esc_url( $tab_url ) . '">' . $section_name . '</a>';

				if ( $number != $number_of_sections ) {
					echo ' | ';
				}
				echo '</li>';
			}
			echo '</ul></div>';
		}

		// Find out if we're displaying a sidebar.
		$is_promo_active = qctj_is_promo_active();
		$wrapper_class   = ( true === $is_promo_active )
			? array( ' qctj-has-sidebar' )
			: array();
		?>
		<div id="tab_container" class="<?php echo esc_attr( $active_tab . '-tab' ); ?>">
			<div class="qctj-settings-wrap<?php echo esc_attr( implode( ' ', $wrapper_class ) ); ?> wp-clearfix">
				<div class="qctj-settings-content">
					<form method="post" action="options.php">
						<table class="form-table">
							<?php

							settings_fields( 'qctj_settings' );

							if ( 'main' === $section ) {
								do_action( 'qctj_settings_tab_top', $active_tab );
							}

							do_action( 'qctj_settings_tab_top_' . $active_tab . '_' . $section );

							do_settings_sections( 'qctj_settings_' . $active_tab . '_' . $section );

							do_action( 'qctj_settings_tab_bottom_' . $active_tab . '_' . $section );

							// For backwards compatibility.
							if ( 'main' === $section ) {
								do_action( 'qctj_settings_tab_bottom', $active_tab );
							}

							// If the main section was empty and we overrode the view with the next subsection, prepare the section for saving.
							if ( true === $override ) {
								?>
								<input type="hidden" name="qctj_section_override" value="<?php echo esc_attr( $section ); ?>" />
								<?php
							}
							?>
						</table>
						<?php submit_button(); ?>
					</form>
				</div>
				<?php
				if ( true === $is_promo_active ) {
					qctj_options_sidebar();
				}
				?>
			</div>
		</div><!-- #tab_container-->
	</div><!-- .wrap -->
	<?php
	echo ob_get_clean();
}

/**
 * Display the sidebar
 *
 * @since 1.0
 *
 * @return string
 */
function qctj_options_sidebar() {

	// Get settings tab and section info
	$active_tab     = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
	$active_tab     = array_key_exists( $active_tab, qctj_get_settings_tabs() ) ? $active_tab : 'general';
	$active_section = isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : 'main';
	$active_section = array_key_exists( $active_section, qctj_get_settings_tab_sections( $active_tab ) ) ? $active_section : 'main';

	// The coupon code we're promoting
	$coupon_code = 'BFCM2019';

	// Build the main URL for the promotion
	$args = array(
		'utm_source'   => 'settings',
		'utm_medium'   => 'wp-admin',
		'utm_campaign' => 'bfcm2019',
		'utm_content'  => 'sidebar-promo-' . $active_tab . '-' . $active_section,
	);
	$url  = add_query_arg( $args, 'https://qctechjunkie.com/pricing/' );
	?>
	<div class="qctj-settings-sidebar">
		<div class="qctj-settings-sidebar-content">
			<div class="qctj-sidebar-header-section">
				<img class="qctj-bfcm-header" src="<?php echo esc_url( QCTJ_PLUGIN_URL . 'assets/images/promo/bfcm-header.svg' ); ?>">
			</div>
			<div class="qctj-sidebar-description-section">
				<p class="qctj-sidebar-description"><?php _e( 'Save 25% on all QCTechJunkie purchases <strong>this week</strong>, including renewals and upgrades!', 'qctechjunkie-plugins' ); ?></p>
			</div>
			<div class="qctj-sidebar-coupon-section">
				<label for="qctj-coupon-code"><?php _e( 'Use code at checkout:', 'qctechjunkie-plugins' ); ?></label>
				<input id="qctj-coupon-code" type="text" value="<?php echo $coupon_code; ?>" readonly>
				<p class="qctj-coupon-note"><?php _e( 'Sale ends 23:59 PM December 6th CST. Save 25% on <a href="https://sandhillsdev.com/projects/" target="_blank">our other plugins</a>.', 'qctechjunkie-plugins' ); ?></p>
			</div>
			<div class="qctj-sidebar-footer-section">
				<a class="qctj-cta-button" href="<?php echo esc_url( $url ); ?>" target="_blank"><?php _e( 'Shop Now!', 'qctechjunkie-plugins' ); ?></a>
			</div>
		</div>
	</div>
	<?php
}
