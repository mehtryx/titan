<?php
error_reporting( E_ERROR & ~E_DEPRECATED & ~E_STRICT );


class WidgetDeviceVisibiltiyAdminTest extends WP_UnitTestCase {

	function setUp() {

		#setup code
		parent::setUp();
		set_current_screen(); //switch to admin context
		$this->device_visibility_plugin = new Postmedia_Widget_Device_Visibility();
	}

	function testConstructorAdmin() {
		$this->assertFalse( 10 == has_filter( 'widget_display_callback', array( $this->device_visibility_plugin, 'widget_display_callback' ) ) );
		$this->assertFalse( 10 == has_filter( 'sidebars_widgets', array( $this->device_visibility_plugin, 'sidebars_widgets' ) ) );
		$this->assertTrue( 10 == has_filter( 'widget_update_callback', array( $this->device_visibility_plugin, 'widget_update' ) ) );
		$this->assertTrue( 10 == has_action( 'in_widget_form', array( $this->device_visibility_plugin, 'widget_admin' ) ) );
	}

	function provider_test_sidebar_widgets_admin() {
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
				),
				'Mozilla/5.0 (iPhone; CPU iPhone OS 7_1_2 like Mac OS X) AppleWebKit/537.51.2 (KHTML like Gecko) Version/7.0 Mobile/11D257 Safari/9537.53',
				array(
					'wp_inactive_widgets' => array(
						'pn_galleries-widget-3',
					),
					'sidebar' => array(
						'search-2',
					),
				),
			),
		);
	}

	/**
	 * @dataProvider provider_test_sidebar_widgets_admin
	 */
	function testSidebarWidgetsAdmin( $sidebar_widgets, $user_agent, $expected_result ) {

		$_SERVER['HTTP_USER_AGENT'] = $user_agent;

		$result = $this->device_visibility_plugin->sidebars_widgets( $sidebar_widgets );

		$this->assertEquals( $result, $expected_result );
	}


	function tearDown() {
		# tear down code
		parent::tearDown();
		set_current_screen( 'front' ); //switch context back to front-end
	}
}
