<?php
namespace Postmedia\Web;

/**
 * Trait used to easily get the plugin path or url
 *
 * Can only be used in a class. In class put "use Postmedia\Web\Paths;". Then you can:
 * get the path: $this->path - it will return the base path to the theme or plugin
 * get the url: $this->url - it will return the url to to the base of the theme or plugin
 *
 * Basic example:
 *  class myClass {
 *	  use Postmedia\Web\Paths;
 *
 *    public function some_function_that_needs_path() {
 *	    return $this->path() . 'css/main.css<br/>'; // returns path/to/pluginOrTheme/directory/css/main.css
 *	  }
 *
 * 	  public function some_function_that_needs_uri() {
 *		return '<img src="' . $this->url('classes')  . 'imgs/banner.png" alt="banner image of cars" /><br/>';
 *	  }
 *  }
 *
 *
 * If a user has not used this trait from the default classes/Postmedia library path then
 * an alternate library keword can be set. This should be the top level directory of the library.
 * So if your library is stored in my-plugin/library/Web. You would pass in the keyord like:
 * $this->path('library')
 *
 */
trait PathsThatDontMatch {

	/**
	 * Get the path to the base directory
	 *
	 * @param  string $top_level_dir    - (optional) top-level library directory where this trait is stored
	 * @param  $path_delimiter			- (optional) string character delimiter
	 * @return string                   - the base path of plugin or theme
	 */
	public function path( $top_level_dir = false, $path_delimiter = '/' ) {

		// Stop bad data being passed into $top_level_dir
		if ( ! empty( $top_level_dir ) && 'string' != gettype( $top_level_dir ) ) {
			return;
		}

		return $this->get_base_path( explode( $path_delimiter, plugin_dir_path( __FILE__ ) ), $top_level_dir );
	}

	/**
	 * Get the url to the base directory
	 *
	 * @param  string $top_level_dir    - (optional) top-level library directory where this trait is stored
	 * @param  $path_delimiter			- (optional) string character delimiter
	 * @return string                   - the base url path of plugin or theme
	 */
	public function url( $top_level_dir = false, $path_delimiter = '/' ) {

		// Stop bad data being passed into $top_level_dir
		if ( ! empty( $top_level_dir ) && 'string' != gettype( $top_level_dir ) ) {
			return;
		}

		return $this->get_base_path( explode( $path_delimiter, plugins_url( __FILE__ ) ), $top_level_dir );
	}

	/**
	 * Determines the base path of the plugin or theme
	 * @param  array  $path             - the path values
	 * @param  string $top_level_dir    - (optional) top-level library directory of the library where this trait is stored
	 * @param  $path_delimiter			- (optional) string character delimiter
	 * @return string                   - the base path of plugin or theme
	 */
	private function get_base_path( $path, $top_level_dir = false, $path_delimiter = '/' ) {

		$cnt = count( $path );

		if ( false == $top_level_dir ) {
			$i = array_search( 'classes', $path );

			if ( false != $i ) {
				if ( 'Postmedia' == $path[ $i + 1 ] ) {
					$i++;
					for ( $i; $i <= $cnt; $i++ ) {
						array_pop( $path ); // trim off each library directory, end trimming at the base directory
					}
				}
			} else {
				return;
			}
		} else {
			$i = array_search( $top_level_dir, $path );
			if ( false != $i ) {
				$i++;
				for ( $i; $i <= $cnt; $i++ ) {
					array_pop( $path );// trim off each library directory, end trimming at the base directory
				}
			} else {
				return;
			}
		}

		return implode( $path_delimiter, $path );
	}

	public function unit_test_get_base_path_private_method( $path, $top_level_dir = false, $path_delimiter = '/' ) {
		return $this->get_base_path( $path, $top_level_dir, $path_delimiter );
	}
}

/**
 * Full Paths Trait usage
 */

/*
Paths Trait Usage

The Paths trait is designed to rid plugins and themes of global variables, defines and other properties 
that are subject to bugs. Instead this trait is simple to ad and use and does not maintain global state

Basic Use

class myClass {
	use Postmedia\Web\Paths; // include the Paths trait in the class

	public function some_function_that_needs_path() {
		return $this->path() . 'css/main.css<br/>'; // returns path/to/pluginOrTheme/directory/css/main.css
	}

	public function some_function_that_needs_uri() {
		return '<img src="' . $this->url('classes')  . 'imgs/banner.png" alt="banner image of cars" /><br/>';
	}
}

			
Custom Path

Normally the postmedia-library is installed as a project git sub module like plugin/classes/Postmedia... If 
you where to not install the library fo the sub module and clone the library into your project it might look like 
plugin/postmedia-library... If you where to do this, then to get the proper path to your project/plugin directory 
you would need to specific a custom top level directory like:

// Path: plugin/postmedia-library/Web/Paths.php

class myClass {
	use Postmedia\Web\Paths; // include the Paths trait in the class

	public function use_of_custom_path() {
		return $this->path('postmedia-library'); // would return 'plugin'
	}
}
			
Custom Delimiter

Unlikely, but possible to have a server file system use a differnet path delimiter than '/', so a custom delimiter 
can be specific

// Path: theme_dir/library/Web/Paths.php

class myClass {
	use Postmedia\Web\Paths; // include the Paths trait in the class

	public function use_of_custom_delimiter() {
		// Plugin path that the server returns is 'plugin:library:with:special:delimiter'
		return $this->path('library', ':'); // would return 'theme_dir'
	}
}
 */
