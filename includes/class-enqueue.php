<?php

namespace LBMCore;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Enqueue {

	public static function init() {
		$self = new self();
		add_action( 'wp_enqueue_scripts', array( $self, 'lbm_scripts' ) );
	}

	public function lbm_scripts() {
	        $current_user_id = get_current_user_id();
	        wp_enqueue_style( 'lbm-shortcode-style', LBM_ROOT_DIR_URL . 'includes/assets/shortcode/style.css' );
	        wp_enqueue_script( 'lbm-shortcode-script', LBM_ROOT_DIR_URL . 'includes/assets/shortcode/scripts.js', 'jquery', '0.0.1', true );
// 			wp_enqueue_style( 'lbm-build-style', LBM_ROOT_DIR_URL . 'includes/assets/build/frontend.css' );
// 			wp_enqueue_script( 'lbm-build-script', LBM_ROOT_DIR_URL . 'includes/assets/build/frontend.js', 'jquery', '0.0.1', true );
			wp_localize_script(
				'lbm-shortcode-script',
				'lbm_settings',
				array(
					'ajax_url'        => admin_url( 'admin-ajax.php' ),
					'nonce'           => wp_create_nonce( 'lbm_nonce' ),
					'engineer_id' => get_field('engineer_id', 'user_' . $current_user_id)
				)
			);

		add_filter( 'script_loader_tag', array( $this, 'add_module_type_to_script' ), 10, 3 );
	}

	public function add_module_type_to_script( $tag, $handle, $src ) {
		if ( 'lbm-build-script' === $handle ) {
			$tag = '<script type="module" src="' . esc_url( $src ) . '"></script>';
		}
		return $tag;
	}
}
