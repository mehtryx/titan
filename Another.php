<?php
/**
 * Demo Code to Break stuff
 */
class TitanProject extends \WPWigit{

	private $topics;

	/**
	 * Class construct
	 *
	 * @since 0.0.0
	 *
	 */
	public function __construct() {
		$test = 'anyting';
		// Add admin hooks
		if ( is_admin() ) {
			if ( isset( $test ) ) {
				echo Postmedia\Web\Utilities::esc_layouts( $test ); // should be escaped
				echo $test; // will fail escaping
			}
			add_action( 'admin_menu', array( $this, 'create_settings_menu' ) );
			add_action( 'admin_init', array( $this, 'register_settings' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		}
	}
}
