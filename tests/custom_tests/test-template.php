<?php
error_reporting( E_ERROR & ~E_DEPRECATED & ~E_STRICT );

class PostmediaTest extends WP_UnitTestCase {

	function setUp() {
		#setup code
		parent::setUp();
	}

	# Additional functions for tests need to be prefixed with 'test' i.e.:
	# function testSomeFunctionality(){ }

	function testSample() {
		// replace this with some actual testing code
		$this->assertTrue( false );
	}
	function testSample2() {
		// replace this with some actual testing code
		$this->assertTrue( true );
	}
	function testSample3() {
		// replace this with some actual testing code
		$this->assertTrue( true );
	}

	function tearDown() {
		# tear down code
		parent::tearDown();
	}

}

