<?php
namespace Postmedia\Web\Widgets;
use Postmedia\Web\Utilities;
/**
 * Plugin Name: TV Listings Widget
 * Description: Display a TV Listings widget
 * Author: Postmedia Network Inc.
 * Version: 1.0
 * Author: Postmedia Network Inc.
 * Copyright © 2016 Postmedia Network Inc.
*/
class TestClass extends \WP_Widget {
	function __construct() {
		parent::__construct( 'TvListings', 'TV Listings Widget', array( 'description' => 'Add a TV listings widget' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'tv_listings_widget_css_enqueue' ) );
		// boom test
	}
}
