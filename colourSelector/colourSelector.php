<?php
/**
* Plugin Name: Colour Selector by e64
* Description: Fully responsive and mobile ready colour selector for AlloyGator.
* Version: 0.9.1
* WC requires at least: 5.2.2
* WC tested up to: 5.2.2
* Author: e64
* Author URI: http://e64.com
* Text Domain: wporg
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/


defined( 'ABSPATH' ) or die( 'Must be run within WordPress!' );

define( 'e64_cs',
	array(
		'plugin-path' => plugin_dir_path( __FILE__ ),
		'plugin-url' => plugin_dir_url( __FILE__ )
	)
);


// First we register our resources using the init hook
function e64_colourSelector_register() {
	if ( strpos($_SERVER['SERVER_NAME'], "pre64.co.uk") !== false) {
		$jsver = "1.5.1". rand();
	}
	else {
		$jsver = "1.8.5";
	}
	
	wp_register_script("e64cs-wheel", plugins_url("js/colourWheel.js", __FILE__), array(), $jsver, true);
	wp_register_script("e64cs-js", plugins_url("js/e64cs.js", __FILE__), array(), $jsver, true);
	wp_register_script("e64cs-canvas", plugins_url("js/html2canvas.min.js", __FILE__), array(), $jsver, true);
	wp_register_script("e64cs-image", plugins_url("js/canvas2image.js", __FILE__), array(), $jsver, true);
	
	wp_register_style("e64cs-css", plugins_url("css/e64cs.css", __FILE__), array(), $jsver, "all");
	wp_register_style("e64cs-mid", plugins_url("css/e64cs-mid.css", __FILE__), array(), $jsver, "(max-width: 1136px)");
	wp_register_style("e64cs-sml", plugins_url("css/e64cs-sml.css", __FILE__), array(), $jsver, "(max-width: 800px)");
	wp_register_style("e64cs-mbl", plugins_url("css/e64cs-mbl.css", __FILE__), array(), $jsver, "(max-width: 640px)");
	
	add_shortcode( 'colour-selector', 'e64_colourSelector' );
}

// Now call the init hook
add_action( 'init', 'e64_colourSelector_register' );


function e64_colourSelector($atts = [], $content = null, $tag = '')
{
	// normalize attribute keys, lowercase
	$atts = array_change_key_case((array)$atts, CASE_LOWER);
	
	// override default attributes with user attributes
	$cSselector_atts = shortcode_atts([
									 'title' => 'Choose your Alloy Gator options',
									 'colour' => '#ff0000',
									 'car' => 'bmw',
									 'colour-boxes' => 'y'
								 ], $atts, $tag);

	$title = $cSselector_atts['title'];
	$colour = $cSselector_atts['colour'];
	$carType = $cSselector_atts['car'];
	$useboxes = strtolower($cSselector_atts['colour-boxes']);
	
	wp_enqueue_script("e64cs-wheel");
	wp_enqueue_script("e64cs-js");
	wp_enqueue_script("e64cs-canvas");
	wp_enqueue_script("e64cs-image");
	
	wp_enqueue_style("e64cs-css");
	wp_enqueue_style("e64cs-mid");
	wp_enqueue_style("e64cs-sml");
	wp_enqueue_style("e64cs-mbl");
	
	$defValues = array ('colour' => $colour, 'bookUrl' => esc_attr(get_option('e64_faf_book_fitter_product_url')));
	
	wp_localize_script("e64cs-js", 'php_vars', $defValues );
	
	$o = str_replace("@@plugin_url@@", e64_cs['plugin-url'], file_get_contents(e64_cs['plugin-path']."selector-template.html", __FILE__));
	
	$boxes = "";
	if ("y"==$useboxes) {
		$boxes = str_replace("@@plugin_url@@", e64_cs['plugin-url'], file_get_contents(e64_cs['plugin-path']."colour-boxes.html", __FILE__));
	}
	$alloys = "";
	$alloys = file_get_contents(plugins_url("alloy-colours.html", __FILE__));		
	
	$tags = array("@@title@@", "@@colour-boxes@@", "@@alloy-colours@@");
	$vals = array($title, $boxes, $alloys);
	
	$o = str_replace($tags, $vals, $o);
	
	// return output
	return $o;
}

?>
