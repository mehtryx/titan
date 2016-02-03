<?php

$_tests_dir = 'WP_TESTS_DIR'; // DO NOT alter this line, it is updated when ci builds

require_once $_tests_dir . '/includes/functions.php';

# Including plugin or theme functionality 
function _manually_load_plugin_theme() {
	// require dirname( __FILE__ ) . '/path/to/file_name_goes_here.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin_theme' );


require $_tests_dir . '/includes/bootstrap.php';

