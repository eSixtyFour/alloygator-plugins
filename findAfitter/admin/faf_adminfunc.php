<?php

add_action( 'plugins_loaded', 'e64_faf_admin_setup' );
add_action( 'admin_menu', 'e64_faf_admin_menu' );
add_action( 'admin_enqueue_scripts', 'e64_faf_admin_register');

//$accessCapability = 'manage_options';
$accessCapability = 'manage_woocommerce';

function e64_faf_admin_register() {
	
	global $jsver, $pagenow;
	
	if ($pagenow == 'admin.php') {
		$page = $_GET['page'];
		$mapsAPI = esc_attr(get_option('e64_faf_google_maps_api_id'));
		
		if ($page == 'alloygator-fitters'||$page == 'faf-new-fitter'||$page == 'faf-settings') {
			
			wp_register_script("e64faf-adminjs", e64_faf['plugin-url']."admin/js/faf-admin.js", array('jquery'), $jsver, true);
			
			wp_register_style("e64faf-admincss", e64_faf['plugin-url']."admin/css/faf-admin.css", array(), $jsver, "all");
			
			wp_enqueue_style("e64faf-admincss");
			
			$enableFunnel = strtoupper(esc_attr(get_option('e64_faf_enable_find_a_fitter_sales_funnels')));
			
			$defValues = array(
				'mapAPI' => $mapsAPI,
				'mapZoom' => esc_attr(get_option('e64_faf_google_maps_initial_zoom')),
				'adUrl' => e64_faf['plugin-url'],
				'bookUrl' => esc_attr(get_option('e64_faf_book_fitter_product_url')),
				'enableFunnel' => $enableFunnel,
			);
			
			wp_localize_script("e64faf-adminjs", 'php_vars', $defValues );	
			
			wp_enqueue_script("e64faf-adminjs");
			wp_register_script("e64-gmaps", "https://maps.googleapis.com/maps/api/js?key=".$mapsAPI."&callback=e64_adminMapsLoaded&libraries=geometry", array(), $jsver, true);
			wp_enqueue_script("e64-gmaps");
		}
	}
}


function e64_faf_admin_setup() {
	
	$formCtl = new e64_formControl();
    $formCtl->saveFitterInit();
	
}

function e64_faf_admin_menu() {
	global $accessCapability;
	
	add_menu_page(	__( 'AlloyGator Fitters', 'textdomain' ), 
					__( 'AlloyGator Fitters', 'textdomains' ), 
					$accessCapability, 
					'alloygator-fitters', 
					'e64_faf_admin_page', 
					e64_faf['plugin-url'].'/admin/assets/logo-20x20.png', 
					6
				);
	
	add_submenu_page( 'alloygator-fitters', 'Add A New Fitter', 'Add New Fitter', $accessCapability, 'faf-new-fitter', 'e64_faf_new_fitter' ); 
	add_submenu_page( 'alloygator-fitters', 'Find A Fitter Admin Settings', 'Settings', $accessCapability, 'faf-settings', 'e64_findAfitter_settings' ); 
	
	add_action( 'admin_init', 'e64_fafSettings_init' );
	
}


function e64_faf_admin_page() {
	global $accessCapability;
	
    // check user capabilities
    if (!current_user_can($accessCapability)) {
        return;
    }
 	
	include_once( e64_faf['plugin-path'] . 'admin/pages/view-fitters.php' );

}


function e64_faf_new_fitter() {
	global $accessCapability;
	
    // check user capabilities
    if (!current_user_can($accessCapability)) {
        return;
    }
	
	include_once( e64_faf['plugin-path'] . 'admin/pages/new-fitter.php' );

}

function e64_findAfitter_settings() {
	global $accessCapability;
	
    // check user capabilities
    if (!current_user_can($accessCapability)) {
        return;
    }
	
	include_once( e64_faf['plugin-path'] . 'admin/pages/view_settings.php' );
}


function e64_fafSettings_init() {
	
	$sectionid = e64_addFieldSection('Google Maps API');
		e64_addInput('Google Maps API ID', $sectionid);
		e64_addInput('Google Maps Initial Zoom', $sectionid);

	$sectionid = e64_addFieldSection('Fitter Options');
		e64_addInput('Fitter Types', $sectionid);
		e64_addInput('Radius Options in Miles', $sectionid);
		e64_addInput('Search in Country', $sectionid);

	$sectionid = e64_addFieldSection('Search Page Options');
		e64_addInput('Initial Page HTML', $sectionid);

	$sectionid = e64_addFieldSection('Sales Funnel Options');
		e64_addInput('Enable Find a Fitter Sales Funnels', $sectionid);
		e64_addInput('Book Fitter Product URL', $sectionid, 'salesFunnel');
		e64_addInput('Picking a Fitter from Product Page Button Text', $sectionid, 'salesFunnel');
		e64_addInput('Buy with Selected Fitter Cart Text', $sectionid, 'salesFunnel');
		
}

function e64_addFieldSection($title) {
	
	$formCtl = new e64_formControl();
	$id = $formCtl->e64_getAdminID($title);
	
    add_settings_section(
        $id, 			// id of the section
        $title, 		// title to be displayed
        '', 			// callback function to be called when opening section, currently empty
        'faf-settings'	// page on which to display the section
    );
	
	return $id;
	
}

function e64_addInput($title, $section, $group="") {
	
	$formCtl = new e64_formControl();
	$name = $formCtl->e64_getAdminName($title, 'e64_faf_');
	
	register_setting('faf-settings', $name);
	
	$class = 'e64-settings-field';
	if (""!=$group) {
		$class .= ' e64-group-'.$group;
	}
	$extras = array( 'label_for' => $name, 'class' => $class );
	
	add_settings_field(
		str_replace('_', '-', $name), // id of the settings field
		$title, // title
		$formCtl->e64_getAdminName($title, 'e64_faf_'), // callback function
		'faf-settings', // page on which settings display
		$section, // section on which to show settings
		$extras,
	);
}

function e64_faf_google_maps_api_id() 		{ 
	$dets = array("size"=>50, "desc"=>"You must also enable the Geocoding API for this API");
	echo faf_settings_field(__FUNCTION__, $dets);
}

function e64_faf_google_maps_initial_zoom()	{ echo faf_settings_dropd(__FUNCTION__, array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20)); }

function e64_faf_fitter_types()	{ 
	$dets = array("cols"=>"30", "rows"=>"3", "desc"=>"Enter one fitter type per line:", "default"=>"Mobile\nStatic");
	echo faf_settings_texta(__FUNCTION__, $dets);
}

function e64_faf_radius_options_in_miles()	{
	$dets = array("cols"=>"10", "rows"=>"8", "desc"=>"Enter one option per line, as numbers only:", "default"=>"5\n10\n20\n30\n40\n50\n75\n100");
	echo faf_settings_texta(__FUNCTION__, $dets);
}

function e64_faf_search_in_country() 		{ 
	$dets = array("size"=>30, "desc"=>"Restrict Fitter searches to be within a country.<br/>For example: <strong>GB</strong> or <strong>United Kingdom</strong>, blank means no restriction.");
	echo faf_settings_field(__FUNCTION__, $dets);
}

function e64_faf_initial_page_html()	{ 
	$default = "<h2>Find your nearest<br/>Five Star Fitter</h2>\n".
				"<p>Enter your postcode to find AlloyGator fitters in your area.</p>\n".
				"<p>Please note: you will receive full contact details for your chosen Five Star Fitter in your order confirmation email.</p>";
	$dets = array("cols"=>"100", "rows"=>"5", "desc"=>"Enter the HTML for the left panel display on Search page", "default"=>$default);
	echo faf_settings_texta(__FUNCTION__, $dets);
}

function e64_faf_enable_find_a_fitter_sales_funnels()	{
	$dets = array("desc"=>"This feature activates the Sales Funnnel features of the Find a Fitter plugin. If this is unchecked<br/>then there will be no Sales Funnel integration in the Find a Fitter tool or the product pages.", "default"=>"N");
	echo faf_settings_check(__FUNCTION__, $dets);
}
function e64_faf_book_fitter_product_url() 		{ 
	$dets = array("size"=>50, "desc"=>"This should be the page address for the product you want the customer<br />to see when the choose to book a fitter from the Find A Fitter seach tool.");
	echo faf_settings_field(__FUNCTION__, $dets);
}

function e64_faf_picking_a_fitter_from_product_page_button_text() 		{ 
	$dets = array("size"=>50, "desc"=>"The text displayed on the button when selecting a fitter from<br />the add to cart option on the product page, and you can use html.");
	echo faf_settings_field(__FUNCTION__, $dets);
}

function e64_faf_buy_with_selected_fitter_cart_text() 		{ 
	$dets = array("size"=>50, "desc"=>"The text displayed on the product page after choosing 'Buy with Fitting' option.<br />You can use html and @companyName@ (case sensitive) to show the fitter's company name.' .");
	echo faf_settings_field(__FUNCTION__, $dets);
}


function faf_settings_field($fld, $dets=array()) {
	$value = get_option($fld);
	$maxlen = "";
	if (strlen($value) > 10 ) {
		$maxlen = "length='".strlen($value)."'";
	}
	
	$extra = $dets["desc"]."<br/>";
	$size = "size='".$dets["size"]."'";
	
	return $extra."<input id='$fld' type='text' name='$fld' $size $maxlen value='".$value."'>";
}


function faf_settings_texta($fld, $dets=array()) {
	
	$value = esc_attr(get_option($fld));
	if (""==$value) $value = $dets["default"];
	
	$extra = $dets["desc"]."<br/>";
	$cols = "cols='".$dets["cols"]."'";
	$rows = "rows='".$dets["rows"]."'";
	
	return $extra."<textarea $rows $cols id='$fld' name='$fld' style='resize: vertical;'>".$value."</textarea>";
}


function faf_settings_dropd($fld, $opts) {
	$return = "<select name='$fld' id='$fld'><option value=''> </option>";
	
	$value = esc_attr(get_option($fld));
	
	foreach ($opts as &$option) {
		if (strlen($option)>=1 && substr($option, 0, 1) == "-") {
			$return .= "<option disabled>$option</option>";
		}
		else {
			$return .= "<option value='$option'";
			if ($option==$value) {
				$return .= " selected";
			}
			$return .= ">$option</option>";
		}
	}
	$return .= "</select>";

	return $return;
}

function faf_settings_hide($fld) {
	return "<input id='$fld' type='hidden' name='$fld' value='".esc_attr(get_option($fld))."' hidden>";
}

function faf_settings_check($fld, $opts) {
	$val = esc_attr(get_option($fld, ''));
	
	if ('Y' == $val) {
		$checked = 'checked=true';
	}
	else {
		$checked = '';
	}
	
	$extra = $opts["desc"]."<br/>";
	
	return $extra."<input id='$fld' type='checkbox' name='$fld' value='Y' $checked>";
}

?>


