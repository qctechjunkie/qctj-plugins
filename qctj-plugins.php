<?php
/**
 * Plugin Name: QCTechJunkie - Plugins
 * Plugin URI: https://qctecjunkie.com
 * Description: QCTechJunkie Plugins all under one roof.
 * Author: TechJunkie, LLC
 * Author URI: https://qctechjunkie.com
 * Version: 1.0.0
 * Text Domain: qctechjunkie-plugins
 *
 * QCTechJunkie Plugins is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * QCTechJunkie Plugins is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with QCTechJunkie Plugins. If not, see <http://www.gnu.org/licenses/>.
 * @package QCTJ
 * @category Core
 * @author Joe Miller
 * @version 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'QCTechJunkie_Plugins' ) ) :

/**
 * Main QCTechJunkie_Plugins Class.
 *
 * @since 1.0
 */
final class QCTechJunkie_Plugins {
	/** Singleton *************************************************************/

	/**
	 * @var QCTechJunkie_Plugins The one true QCTechJunkie_Plugins
	 * @since 1.0
	 */
	private static $instance;

	/**
	 * Main QCTechJunkie_Plugins Instance.
	 *
	 * Insures that only one instance of QCTechJunkie_Plugins exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 1.0
	 * @static
	 * @staticvar array $instance
	 * @uses QCTechJunkie_Plugins::setup_constants() Setup the constants needed.
	 * @uses QCTechJunkie_Plugins::includes() Include the required files.
	 * @see QCTJ()
	 * @return object|QCTechJunkie_Plugins The one true QCTechJunkie_Plugins
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof QCTechJunkie_Plugins ) ) {
			self::$instance = new QCTechJunkie_Plugins;
			self::$instance->setup_constants();

			// add_action( 'plugins_loaded', array( self::$instance ) );

			self::$instance->includes();
		}

		return self::$instance;
	}

	/**
	 * Throw error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 1.0
	 * @access protected
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'qctechjunkie-plugins' ), '1.0.0' );
	}

	/**
	 * Disable unserializing of the class.
	 *
	 * @since 1.0
	 * @access protected
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'qctechjunkie-plugins' ), '1.0.0' );
	}

	/**
	 * Setup plugin constants.
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private function setup_constants() {

		// Plugin version.
		if ( ! defined( 'QCTJ_VERSION' ) ) {
			define( 'QCTJ_VERSION', '1.0.0' );
		}

		// Plugin Folder Path.
		if ( ! defined( 'QCTJ_PLUGIN_DIR' ) ) {
			define( 'QCTJ_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Folder URL.
		if ( ! defined( 'QCTJ_PLUGIN_URL' ) ) {
			define( 'QCTJ_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Root File.
		if ( ! defined( 'QCTJ_PLUGIN_FILE' ) ) {
			define( 'QCTJ_PLUGIN_FILE', __FILE__ );
		}
	}

	/**
	 * Include required files.
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private function includes() {
		global $qctj_options;

		require_once QCTJ_PLUGIN_DIR . 'includes/admin/settings/register-settings.php';
		$qctj_options = qctj_get_settings();

		require_once QCTJ_PLUGIN_DIR . 'includes/class-qctj-license-handler.php';
		require_once QCTJ_PLUGIN_DIR . 'includes/formatting.php';
		require_once QCTJ_PLUGIN_DIR . 'includes/misc-functions.php';
		require_once QCTJ_PLUGIN_DIR . 'includes/scripts.php';

		if ( is_admin() ) {
			require_once QCTJ_PLUGIN_DIR . 'includes/admin/admin-pages.php';
			require_once QCTJ_PLUGIN_DIR . 'includes/admin/settings/display-settings.php';
			require_once QCTJ_PLUGIN_DIR . 'includes/admin/tools.php';
		}

	}

}

endif; // End if class_exists check.


/**
 * The main function for that returns QCTechJunkie_Plugins
 *
 * The main function responsible for returning the one true QCTechJunkie_Plugins
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $qctj = QCTJ(); ?>
 *
 * @since 1.0
 * @return object|QCTechJunkie_Plugins The one true QCTechJunkie_Plugins Instance.
 */
if (!function_exists('QCTJ')) {
	function QCTJ() {
		return QCTechJunkie_Plugins::instance();
	}
}

// Get QCTJ Running.
QCTJ();
