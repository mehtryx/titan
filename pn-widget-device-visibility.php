<?php
/*
Plugin Name: Postmedia Device Visibliity Plugin
Description: Adds the ability to show/hide widgets based on device
Version: 1.0
Author: Postmedia Network Inc.
Contributor: Kelly Spriggs
*/



$pn_widget_device_visibility = new Postmedia_Widget_Device_Visibility();

class Postmedia_Widget_Device_Visibility {

	protected $device_options = array();

	/**
	 * Class Constructor
	 *
	 * Adds Action Hooks and Filters
	 *
	 * @uses add_action
	 * @uses add_filter
	 */
	public function __construct() {

		$this->device_options = array(
			'desktop' 	=> 'Desktop',
			'phone' 	=> 'Smartphone',
			'tablet'	=> 'Tablet',
		);

		if ( is_admin() ) {
			add_action( 'in_widget_form', array( &$this, 'widget_admin' ), 10, 3 );
			add_filter( 'widget_update_callback', array( &$this, 'widget_update' ), 10, 3 );
		} else {
			add_filter( 'widget_display_callback', array( &$this, 'widget_display_callback' ) );
			add_filter( 'sidebars_widgets', array( &$this, 'sidebars_widgets' ) );
		}
	}

	/**
	 * Adds the Display on Device checkboxes on every Widget form
	 *
	 * @param WP_Widget $widget The widget instance, passed by reference
	 * @param null $return Return null if nuew fields are added
	 * @param array $instance An array of the widget's settings
	 *
	 * @uses esc_attr()
	 * @uses get_field_id()
	 * @uses get_field_name()
	 * @uses checked()
	 *
	 */
	public function widget_admin( $widget, $return, $instance ) {

		echo '<hr><fieldset><legend>Display On Devices:</legend><br />';
		foreach ( $this->device_options as $option => $label ) {
			$field_id = 'display_' . $option;
			?>		
			<input type="checkbox" class="checkbox" id="<?php echo esc_attr( $widget->get_field_id( $field_id ) ); ?>" name="<?php echo esc_attr( $widget->get_field_name( $field_id ) ); ?>"  <?php checked( ( isset( $instance[ $field_id ] ) ? $instance[ $field_id ] : false ), true ); ?> />
			<label for="<?php echo esc_attr( $widget->get_field_id( $field_id ) ); ?>"><?php echo esc_html( $label ); ?></label><br />
			<?php
		}
		echo '</fieldset>';
	}

	/**
	 * Saves the device visibility settings when the widget is updated
	 *
	 * @param array $instance  The current widget instance settings
	 * @param array $new_instance  Array of new widget settings
	 * @param array $old_instance  Array of old (previous) widget settings
	 * @return array The instance settings to be saved
	 */
	public function widget_update( $instance, $new_instance, $old_instance ) {

		foreach ( $this->device_options as $option => $label ) {
			$option_name = 'display_' . $option;
			if ( isset( $instance[ $option_name ] ) ) {
				unset( $instance[ $option_name ] );
			}
			if ( isset( $new_instance[ $option_name ] ) && 'on' == $new_instance[ $option_name ] ) {
				$instance[ $option_name ] = true;
			}
		}
		return $instance;
	}

	/**
	 * Filters the settings for a particular widget instance and determine whether to show/hide widget based on device.
	 *
	 * Returning FALSE will prevent the display of the widget
	 *
	 * @param  array $instance  The current widget instance settings
	 * @return mixed  The array of the current widget's' instance settings or FALSE
	 */
	public function widget_display_callback( $instance ) {

		if ( empty( $instance['display_desktop'] ) && empty( $instance['display_tablet'] ) && empty( $instance['display_phone'] ) ) {
			return $instance;
		}

		if ( false !== $instance ) {

			$is_tablet  = $this->is_tablet();
			$is_phone   = $this->is_mobile();
			$is_desktop = ( $is_phone || $is_tablet ) ? false : true;

			if ( $is_desktop && ( empty( $instance['display_desktop'] ) || ! $instance['display_desktop'] ) ) {
				return false;
			}

			if ( $is_tablet && ( empty( $instance['display_tablet'] ) || ! $instance['display_tablet'] ) ) {
				return false;
			}

			if ( $is_phone && ( empty( $instance['display_phone'] ) || ! $instance['display_phone'] ) ) {
				return false;
			}
		}

		return $instance;

	}

	/**
	 * Filter the list of widgets for a sidebar so that active sidebars work as expected.
	 * Removes widgets hidden by the device visibility setting from the sidebars
	 *
	 * @param 	array	$sidebar_widgets 	Associate array of sidebars and their widgets
	 * @return 	array						The array of sidebars and their widgets
	 *
	 * @uses is_admin()
	 * @uses preg_match()
	 * @uses get_option()
	 * @uses intval()
	 * @uses is_null()
	 * @uses $this->widget_display_callback()
	 */
	public function sidebars_widgets( $sidebar_widgets ) {

		$sidebar_settings = array();

		if ( is_admin() ) {
			return $sidebar_widgets;
		}

		foreach ( $sidebar_widgets as $sidebar => $widgets ) {

			if ( empty( $widgets ) ) {
				continue;
			}

			if ( 'wp_inactive_widgets' == $sidebar || false !== strpos( $sidebar, 'orphaned_widgets' ) ) {

				continue;
			}

			foreach ( $widgets as $position => $widget_id ) {

				if ( preg_match( '/^(.+?)-(\d+)$/', $widget_id, $matches ) ) {
					$id_base = $matches[1];
					$widget_number = intval( $matches[2] );
				} else {
					$id_base = $widget_id;
					$widget_number = null;
				}

				if ( ! isset( $sidebar_settings[ $id_base ] ) ) {
					$sidebar_settings[ $id_base ] = get_option( 'widget_' . $id_base );
				}

				//multi widgets (WP_Widget)
				if ( ! is_null( $widget_number ) ) {
					if ( isset( $sidebar_settings[ $id_base ][ $widget_number ] ) && false === $this->widget_display_callback( $sidebar_settings[ $id_base ][ $widget_number ] ) ) {
						unset( $sidebar_widgets[ $sidebar ][ $position ] );
					}
				} elseif ( ! empty( $sidebar_settings[ $id_base ] ) && false === $this->widget_display_callback( $sidebar_settings[ $id_base ] ) ) {
					unset( $sidebar_widgets[ $sidebar ][ $position ] );
				}
			}
		}

		return $sidebar_widgets;
	}

	/**
	 * Determines if the current request is from a mobile device
	 *
	 */
	private function is_mobile() {

		if ( function_exists( 'pn_is_mobile' ) ) {
			return pn_is_mobile();
		} elseif ( function_exists( 'jetpack_is_mobile' ) ) {
			return jetpack_is_mobile();
		} elseif ( function_exists( 'wp_is_mobile' ) ) {
			return wp_is_mobile();
		} else {
			return false;
		}

	}

	/**
	 * Determines if the current request is from a tablet
	 *
	 */
	private function is_tablet() {

		if ( function_exists( 'pn_is_tablet' ) ) {
			return pn_is_tablet();
		} elseif ( class_exists( 'Jetpack_User_Agent_Info' ) ) {
			return Jetpack_User_Agent_Info::is_tablet();
		} else {
			return false;
		}
	}
}
