<?php
/**
 * Demo Code to Break stuff
 */
class Titan {

	private $topics;

	/**
	 * Class construct
	 *
	 * @since 0.0.0
	 *
	 */
	public function __construct() {

		// Add admin hooks
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'create_settings_menu' ) );
			add_action( 'admin_init', array( $this, 'register_settings' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		}
	}
}
