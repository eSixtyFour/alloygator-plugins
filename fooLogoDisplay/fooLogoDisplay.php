<?php
/**
* Plugin Name: fooGallery Logo Display by e64
* Description: Replace car logos for the foo Gallery filter text.
* Version: 0.9.2
* WC requires at least: 5.2.2
* WC tested up to: 5.2.2
* Author: e64
* Author URI: http://e64.com
* Text Domain: wporg
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/


defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

define( 'e64_fld',
	array(
		'plugin-path' => plugin_dir_path( __FILE__ ),
		'plugin-url' => plugin_dir_url( __FILE__ )
	)
);


// First we register our resources using the init hook
function e64_fooLogoDisplay_register() {
	$jsver = "1.2.0";// + rand();
	
	wp_register_script("e64foo-js", plugins_url("js/e64foo.js", __FILE__), array(), $jsver, "all");
	
	$defValues = array ('adUrl'=> __(e64_fld['plugin-url']) );
	
	wp_localize_script("e64foo-js", 'php_vars', $defValues );
	wp_enqueue_script("e64foo-js");
	
	wp_register_style("e64foo-css", plugins_url("css/e64foo.css", __FILE__), array(), $jsver, "all");
	wp_enqueue_style("e64foo-css");

}

// Now call the init hook
add_action( 'init', 'e64_fooLogoDisplay_register' );

function e64_fooDisplayLogo_enqueue() {
	if (is_page('gallery')) {
		wp_enqueue_script("e64foo-js");
		wp_enqueue_style("e64foo-css");
	}
}
add_action( 'wp_enqueue_scripts', 'e64_fooDisplayLogo_enqueue');

?>
