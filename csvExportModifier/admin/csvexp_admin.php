<?php

add_action( 'admin_menu', 'e64_csv_admin_menu' );

//$accessCapability = 'manage_woocommerce_csv_exports';
//$accessCapability = 'manage_woocommerce';
$accessCapability = 'manage_options';

function e64_csv_admin_register() {
	
	global $jsver, $pagenow;
	
	if ($pagenow == 'admin.php') {
		$page = $_GET['page'];
		
		if ($page == 'e64-csv-settings') {
			
		}
	}
}


function e64_csv_admin_menu() {
	global $accessCapability;
	// 'woocommerce', 'AlloyGatoy CSV Options', 'Settings', $accessCapability, 'e64-csv-settings', 'e64_csvexp_settings' ); 
	add_submenu_page(
		'woocommerce',
		__( 'AG CSV Settings', 'e64-csv-settings' ),
		__( 'AG CSV Settings', 'e64-csv-settings' ),
		$accessCapability,
		'e64-csv-settings',
		'e64_csvexp_settings',

	);

	add_action( 'admin_init', 'e64_csvSettings_init' );
	
}

function e64_csvexp_settings() {
	global $accessCapability;
	
    // check user capabilities
    if (!current_user_can($accessCapability)) {
        return;
    }
	
	include_once( e64_csv['plugin-path'] . 'admin/view_settings.php' );
}



function e64_csvSettings_init() {
	
	$sectionid = e64_csv_addFieldSection('CSV Export Options');
		e64_csv_addInput('Order with fitting dropdown text', $sectionid);
		e64_csv_addInput('Fitting cost for Sage import', $sectionid);
		e64_csv_addInput('Percentage for calculating VAT', $sectionid);

	$sectionid = e64_csv_addFieldSection('Added Line Item Options');
		e64_csv_addInput('Order with Fitting Sage SKU', $sectionid);
		e64_csv_addInput('Order with Fitting Sage Description', $sectionid);
		e64_csv_addInput('Order Shipping Sage SKU', $sectionid);
		e64_csv_addInput('Order Shipping Sage Description', $sectionid);
		
}

function e64_csv_addFieldSection($title) {
	
	$id = e64_csv_getAdminID($title);
	
    add_settings_section(
        $id, 			// id of the section
        $title, 		// title to be displayed
        '', 			// callback function to be called when opening section, currently empty
        'e64-csvexp-settings'	// page on which to display the section
    );
	
	return $id;
	
}


function e64_csv_addInput($title, $section, $group="") {
	
	$name = e64_getAdminName($title, 'e64_csv_');
	
	register_setting('e64-csvexp-settings', $name);
	
	$class = 'e64-settings-field';
	if (""!=$group) {
		$class .= ' e64-group-'.$group;
	}
	$extras = array( 'label_for' => $name, 'class' => $class );
	
	add_settings_field(
		str_replace('_', '-', $name), // id of the settings field
		$title, // title
		e64_getAdminName($title, 'e64_csv_'), // callback function
		'e64-csvexp-settings', // page on which settings display
		$section, // section on which to show settings
		$extras,
	);
}

function e64_csv_order_with_fitting_dropdown_text() 		{ 
	$dets = array("size"=>30, "desc"=>"The value in the product dropdown that denotes the product has been bought with fitting.", "default"=>"Buy with fitting");
	echo e64_csv_settings_field(__FUNCTION__, $dets);
}

function e64_csv_fitting_cost_for_sage_import() 		{ 
	$dets = array("size"=>5, "desc"=>"The cost EXCLUDING VAT to use for the extra line item being added to the export for fitting.", "default"=>"50");
	echo e64_csv_settings_field(__FUNCTION__, $dets);
}

function e64_csv_percentage_for_calculating_vat() 		{ 
	$dets = array("size"=>3, "desc"=>"The percentage to use when calulating VAT from an ex VAT amount<br />DO NOT include the percent symbol, e.g. 20.", "default"=>"20");
	echo e64_csv_settings_field(__FUNCTION__, $dets);
}

function e64_csv_order_with_fitting_sage_sku() 		{ 
	$dets = array("size"=>10, "desc"=>"The SKU to use when adding the fitting line item to the CSV export.", "default"=>"LAB");
	echo e64_csv_settings_field(__FUNCTION__, $dets);
}

function e64_csv_order_with_fitting_sage_description() 		{ 
	$dets = array("size"=>50, "desc"=>"The 'product name' to use for the added fitting line item in the CSV export.", "default"=>"AlloyGaytor Custom LAB");
	echo e64_csv_settings_field(__FUNCTION__, $dets);
}

function e64_csv_order_shipping_sage_sku() 		{ 
	$dets = array("size"=>10, "desc"=>"The SKU to use when adding shipping line item to the CSV export.", "default"=>"SHPRSTD");
	echo e64_csv_settings_field(__FUNCTION__, $dets);
}

function e64_csv_order_shipping_sage_description() 		{ 
	$dets = array("size"=>50, "desc"=>"The 'product name' to use for the added shipping line item in the CSV export'.", "default"=>"Standard Shipping");
	echo e64_csv_settings_field(__FUNCTION__, $dets);
}


function e64_csv_settings_field($fld, $dets=array()) {
	$value = get_option($fld);
	if (""==$value) $value = $dets["default"];

	$maxlen = "";
	if (strlen($value) > 10 ) {
		$maxlen = "length='".strlen($value)."'";
	}
	
	$extra = $dets["desc"]."<br/>";
	$size = "size='".$dets["size"]."'";
	
	return $extra."<input id='$fld' type='text' name='$fld' $size $maxlen value='".$value."'>";
}


function e64_csv_settings_texta($fld, $dets=array()) {
	
	$value = esc_attr(get_option($fld));
	if (""==$value) $value = $dets["default"];
	
	$extra = $dets["desc"]."<br/>";
	$cols = "cols='".$dets["cols"]."'";
	$rows = "rows='".$dets["rows"]."'";
	
	return $extra."<textarea $rows $cols id='$fld' name='$fld' style='resize: vertical;'>".$value."</textarea>";
}


function e64_csv_settings_dropd($fld, $opts) {
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

function csv_settings_hide($fld) {
	return "<input id='$fld' type='hidden' name='$fld' value='".esc_attr(get_option($fld))."' hidden>";
}

function csv_settings_check($fld, $opts) {
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

function e64_csv_getAdminID($name, $prefix='csv_')  {
	$return = $prefix.str_replace(' ', '-', strtolower($name));
	$return = str_replace('/', '_', $return);
	return $return;
}

 function e64_getAdminName($name, $prefix='csv_')  {
	$return = $prefix.str_replace(' ', '_', strtolower($name));
	$return = str_replace('/', '_', $return);
	return $return;
}

?>
