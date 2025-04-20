<?php

	/**
	 *
	 * @link              https://pixable.co/
	 * @since             0.0.1
	 * @package           LMB Core Plugin
	 *
	 * @wordpress-plugin
	 * Plugin Name:       LMB Core Plugin
	 * Plugin URI:        https://pixable.co/
	 * Description:       Core Plugin & Functions For LBM For Push
	 * Version:           0.0.1
	 * Author:            Pixable
	 * Author URI:        https://pixable.co/
	 * License:           GPL-2.0+
	 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
	 * Text Domain:       lbm-core
	 * Tested up to:      6.7
	 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LBM {

	private function __construct() {
		$this->define_constants();
		$this->load_dependency();
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
		add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
	}

	public static function init() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

			return $instance;
	}

	public function define_constants() {
		define( 'LBM_VERSION', '0.0.1' );
		define( 'LBM_PLUGIN_FILE', __FILE__ );
		define( 'LBM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		define( 'LBM_ROOT_DIR_PATH', plugin_dir_path( __FILE__ ) );
		define( 'LBM_ROOT_DIR_URL', plugin_dir_url( __FILE__ ) );
		define( 'LBM_INCLUDES_DIR_PATH', LBM_ROOT_DIR_PATH . 'includes/' );
		define( 'LBM_PLUGIN_SLUG', 'lbm-core' );
	}

	public function on_plugins_loaded() {
		do_action( 'lbm_loaded' );
	}

	public function init_plugin() {
		$this->load_textdomain();
		$this->dispatch_hooks();
	}

	public function dispatch_hooks() {
		LBMCore\Autoload::init();
		LBMCore\Enqueue::init();
		LBMCore\Shortcodes::init();
		LBMCore\API::init();
		LBMCore\Ajax::init();
		LBMCore\Actions::init();
	}

	public function load_textdomain() {
		load_plugin_textdomain(
			'lbm-core',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages/'
		);
	}

	public function load_dependency() {
		require_once LBM_INCLUDES_DIR_PATH . 'class-autoload.php';
	}

	public function activate() {
	}

	public function deactivate() {
	}
}

function lbmcore_start() {
	return LBM::init();
}


lbmcore_start();