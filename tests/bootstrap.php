<?php

$_tests_dir = getenv('WP_TESTS_DIR');
if ( !$_tests_dir ) $_tests_dir = '/tmp/wordpress-tests-lib';

require_once $_tests_dir . '/includes/functions.php';

# Including plugin or theme functionality 
function _manually_load_plugin_theme() {
	require dirname( __FILE__ ) . '/path/to/file_name_goes_here.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin_theme' );


require $_tests_dir . '/includes/bootstrap.php';

