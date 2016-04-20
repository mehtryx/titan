<?php
error_reporting( E_ERROR & ~E_DEPRECATED & ~E_STRICT );

if ( ! function_exists( 'pn_is_tablet' ) ) {

	function pn_is_mobile() {

		if ( ! empty( $_SERVER['HTTP_USER_AGENT'] ) && false !== strpos( strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) ), 'iphone' ) ) {
			return true;
		}

		return false;

	}


	function pn_is_tablet() {
		if ( ! empty( $_SERVER['HTTP_USER_AGENT'] ) && false !== strpos( strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) ), 'ipad' ) ) {
			return true;
		}

		return false;
	}
}

class WidgetDeviceVisibilityTest extends WP_UnitTestCase {

	function setUp() {
		#setup code
		parent::setUp();
		$this->device_visibility_plugin = new Postmedia_Widget_Device_Visibility();

		$settings = array(
			'1' => array(
				'title' => 'Sidebar Search',
			),
			'2' => array(
				'title' => 'Sidebar Search',
				'display_desktop' => '1',
			),
			'3' => array(
				'title' => 'Sidebar Search',
				'display_phone' => '1',
			),
			'4' => array(
				'title' => 'Sidebar Search',
				'display_tablet' => '1',
			),
			'5' => array(
				'title' => 'Sidebar Search',
				'display_tablet' => '1',
				'display_desktop' => '1',
			),
			'6' => array(
				'title' => 'Sidebar Search',
				'display_phone' => '1',
				'display_tablet' => '1',
			),
		);

		update_option( 'widget_search', $settings );
	}

	function testConstructor() {

		$this->assertTrue( 10 == has_filter( 'widget_display_callback', array( $this->device_visibility_plugin, 'widget_display_callback' ) ) );
		$this->assertTrue( 10 == has_filter( 'sidebars_widgets', array( $this->device_visibility_plugin, 'sidebars_widgets' ) ) );
		$this->assertFalse( 10 == has_filter( 'widget_update_callback', array( $this->device_visibility_plugin, 'widget_update' ) ) );
		$this->assertFalse( 10 == has_action( 'in_widget_form', array( $this->device_visibility_plugin, 'widget_admin' ) ) );
	}

	function testWidgetAdmin() {

		# register the wordpress search widget to use for testing
		global $wp_widget_factory;
		unregister_widget( 'WP_Widget_Search' );
		register_widget( 'WP_Widget_Search' );

		# set up parameters
		$widget = $wp_widget_factory->widgets['WP_Widget_Search'];
		$return	= null;
		$instance = array();

		# execute admin function and grab output
		ob_start();
		$this->device_visibility_plugin->widget_admin( $widget, $return, $instance );
		$result = ob_get_contents();
		ob_end_clean();

		$expected_output = '<hr><fieldset><legend>Display On Devices:</legend><br />
		<input type="checkbox" class="checkbox" id="widget-search--display_desktop" name="widget-search[][display_desktop]"   />
		<label for="widget-search--display_desktop">Desktop</label><br />
		<input type="checkbox" class="checkbox" id="widget-search--display_phone" name="widget-search[][display_phone]"   />
		<label for="widget-search--display_phone">Smartphone</label><br />
		<input type="checkbox" class="checkbox" id="widget-search--display_tablet" name="widget-search[][display_tablet]"   />
		<label for="widget-search--display_tablet">Tablet</label><br />
		</fieldset>';

		$this->assertSame( preg_replace( '/\s+/', '', $expected_output ), preg_replace( '/\s+/', '', $result ) );
	}

	function provider_test_widget_update() {

		return array(
			array(
				array(
					'display_tablet' => true,
				),
				array(
					'display_phone' => 'on',
				),
				false,
				false,
				true,
				false,
			),
			array(
				array(
					'display_desktop' => true,
				),
				array(
					'display_tablet' => 'on',
				),
				false,
				false,
				false,
				true,
			),
			array(
				array(
					'display_desktop' => true,
					'display_phone' => false,
				),
				array(
					'display_phone' => 'on',
					'display_tablet' => 'on',
				),
				false,
				false,
				true,
				true,
			),
			array(
				array(),
				array(
					'display_tablet' => 'on',
				),
				false,
				false,
				false,
				true,
			),
			array(
				array(),
				array(
					'display_desktop' => 'on',
					'display_phone' => 'on',
				),
				false,
				true,
				true,
				false,
			),
			array(
				array(),
				array(
					'display_desktop' => 'xyz',
					'display_phone' => 'on',
				),
				false,
				false,
				true,
				false,
			),
			array(
				array(
					'display_desktop' => true,
					'display_phone' => true,
					'display_tablet' => true,
				),
				array(),
				true,
				false,
				false,
				false,
			),
		);
	}

	/**
	 * @dataProvider provider_test_widget_update
	 */
	function testWidgetUpdate( $old_instance, $new_instance, $expected_settings, $display_on_desktop, $display_on_phone, $display_on_tablet ) {
		$settings = array();
		$settings = $this->device_visibility_plugin->widget_update( $old_instance, $new_instance, array() );

		$this->assertTrue( empty( $settings ) == $expected_settings );
		$this->assertTrue( $settings['display_desktop'] == $display_on_desktop );
		$this->assertTrue( $settings['display_phone'] == $display_on_phone );
		$this->assertTrue( $settings['display_tablet'] == $display_on_tablet );
	}

	function provider_test_widget_display_callback() {

		return array(
			array(
				array(
					'display_phone' => true,
				),
				'Mozilla/5.0 (iPhone; CPU iPhone OS 7_1_2 like Mac OS X) AppleWebKit/537.51.2 (KHTML like Gecko) Version/7.0 Mobile/11D257 Safari/9537.53',
				'array',
			),
			array(
				array(
					'display_desktop' => true,
				),
				'Mozilla/5.0 (iPhone; CPU iPhone OS 7_1_2 like Mac OS X) AppleWebKit/537.51.2 (KHTML like Gecko) Version/7.0 Mobile/11D257 Safari/9537.53',
				'boolean',
			),
			array(
				array(
					'display_tablet' => true,
				),
				'Mozilla/5.0 (iPhone; CPU iPhone OS 7_1_2 like Mac OS X) AppleWebKit/537.51.2 (KHTML like Gecko) Version/7.0 Mobile/11D257 Safari/9537.53',
				'boolean',
			),
			array(
				array(
					'display_phone' => true,
				),
				'Mozilla/5.0 (iPad; CPU OS 8_0_2 like Mac OS X) AppleWebKit/600.1.4 (KHTML like Gecko) Mobile/12A405 Version/7.0 Safari/9537.53',
				'boolean',
			),
			array(
				array(
					'display_desktop' => true,
				),
				'Mozilla/5.0 (iPad; CPU OS 8_0_2 like Mac OS X) AppleWebKit/600.1.4 (KHTML like Gecko) Mobile/12A405 Version/7.0 Safari/9537.53',
				'boolean',
			),
			array(
				array(
					'display_tablet' => true,
				),
				'Mozilla/5.0 (iPad; CPU OS 8_0_2 like Mac OS X) AppleWebKit/600.1.4 (KHTML like Gecko) Mobile/12A405 Version/7.0 Safari/9537.53',
				'array',
			),
			array(
				array(
					'display_phone' => true,
				),
				'Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0',
				'boolean',
			),
			array(
				array(
					'display_desktop' => true,
				),
				'Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0',
				'array',
			),
			array(
				array(
					'display_tablet' => true,
				),
				'Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0',
				'boolean',
			),
			array(
				array(
					'display_desktop' => true,
					'display_phone' => true,
				),
				'Mozilla/5.0 (iPhone; CPU iPhone OS 7_1_2 like Mac OS X) AppleWebKit/537.51.2 (KHTML like Gecko) Version/7.0 Mobile/11D257 Safari/9537.53',
				'array',
			),
			array(
				array(
					'display_desktop' => true,
					'display_phone' => true,
				),
				'Mozilla/5.0 (iPad; CPU OS 8_0_2 like Mac OS X) AppleWebKit/600.1.4 (KHTML like Gecko) Mobile/12A405 Version/7.0 Safari/9537.53',
				'boolean',
			),
		);
	}

	/**
	 * @dataProvider provider_test_widget_display_callback
	 */
	function testWidgetDisplayCallback( $instance, $user_agent, $expected_result ) {

		$_SERVER['HTTP_USER_AGENT'] = $user_agent;

		$result = $this->device_visibility_plugin->widget_display_callback( $instance );
		$this->assertSame( gettype( $result ), $expected_result );
	}

	function provider_test_sidebar_widgets() {
		return array(
			array(
				array(
					'wp_inactive_widgets' => array(
						'pn_galleries-widget-3',
					),
					'sidebar' => array(
						'search-1',
					),
				)
				,
				'Mozilla/5.0 (iPhone; CPU iPhone OS 7_1_2 like Mac OS X) AppleWebKit/537.51.2 (KHTML like Gecko) Version/7.0 Mobile/11D257 Safari/9537.53',
				array(
					'wp_inactive_widgets' => array(
						'pn_galleries-widget-3',
					),
					'sidebar' => array(
						'search-1',
					),
				 ),
			),
			array(
				array(
					'wp_inactive_widgets' => array(
						'pn_galleries-widget-3',
					),
					'sidebar' => array(
						'search-2',
					),
				)
				,
				'Mozilla/5.0 (iPhone; CPU iPhone OS 7_1_2 like Mac OS X) AppleWebKit/537.51.2 (KHTML like Gecko) Version/7.0 Mobile/11D257 Safari/9537.53',
				array(
					'wp_inactive_widgets' => array(
						'pn_galleries-widget-3',
					),
					'sidebar' => array(),
				),
			),
			array(
				array(
					'wp_inactive_widgets' => array(
						'pn_galleries-widget-3',
					),
					'sidebar' => array(
						'search-3',
					),
				)
				,
				'Mozilla/5.0 (iPhone; CPU iPhone OS 7_1_2 like Mac OS X) AppleWebKit/537.51.2 (KHTML like Gecko) Version/7.0 Mobile/11D257 Safari/9537.53',
				array(
					'wp_inactive_widgets' => array(
						'pn_galleries-widget-3',
					),
					'sidebar' => array(
						'search-3',
					),
				),
			),
			array(
				array(
					'wp_inactive_widgets' => array(
						'pn_galleries-widget-3',
					),
					'sidebar' => array(
						'search-4',
					),
				)
				,
				'Mozilla/5.0 (iPhone; CPU iPhone OS 7_1_2 like Mac OS X) AppleWebKit/537.51.2 (KHTML like Gecko) Version/7.0 Mobile/11D257 Safari/9537.53',
				array(
					'wp_inactive_widgets' => array(
						'pn_galleries-widget-3',
					),
					'sidebar' => array(),
				),
			),
			array(
				array(
					'wp_inactive_widgets' => array(
						'pn_galleries-widget-3',
					),
					'sidebar' => array(
						'search-1',
					),
				)
				,
				'Mozilla/5.0 (iPad; CPU OS 8_0_2 like Mac OS X) AppleWebKit/600.1.4 (KHTML like Gecko) Mobile/12A405 Version/7.0 Safari/9537.53',
				array(
					'wp_inactive_widgets' => array(
						'pn_galleries-widget-3',
					),
					'sidebar' => array(
						'search-1',
					),
				),
			),
			array(
				array(
					'wp_inactive_widgets' => array(
						'pn_galleries-widget-3',
					),
					'sidebar' => array(
						'search-2',
					),
				)
				,
				'Mozilla/5.0 (iPad; CPU OS 8_0_2 like Mac OS X) AppleWebKit/600.1.4 (KHTML like Gecko) Mobile/12A405 Version/7.0 Safari/9537.53',
				array(
					'wp_inactive_widgets' => array(
						'pn_galleries-widget-3',
					),
					'sidebar' => array(),
				),
			),
			array(
				array(
					'wp_inactive_widgets' => array(
						'pn_galleries-widget-3',
					),
					'sidebar' => array(
						'search-5',
					),
				)
				,
				'Mozilla/5.0 (iPad; CPU OS 8_0_2 like Mac OS X) AppleWebKit/600.1.4 (KHTML like Gecko) Mobile/12A405 Version/7.0 Safari/9537.53',
				array(
					'wp_inactive_widgets' => array(
						'pn_galleries-widget-3',
					),
					'sidebar' => array(
						'search-5',
					),
				),
			),
			array(
				array(
					'wp_inactive_widgets' => array(
						'pn_galleries-widget-3',
					),
					'sidebar' => array(
						'search-6',
					),
				)
				,
				'Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0',
				array(
					'wp_inactive_widgets' => array(
						'pn_galleries-widget-3',
					),
					'sidebar' => array(),
				),
			),
		);
	}

	/**
	 * @dataProvider provider_test_sidebar_widgets
	 */
	function testSidebarWidgets( $sidebar_widgets, $user_agent, $expected_result ) {

		$_SERVER['HTTP_USER_AGENT'] = $user_agent;

		$result = $this->device_visibility_plugin->sidebars_widgets( $sidebar_widgets );

		$this->assertEquals( $result, $expected_result );
	}

	function tearDown() {
		# tear down code
		parent::tearDown();
		delete_option( 'widget_search' );
	}
}
