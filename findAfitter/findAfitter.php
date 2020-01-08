<?php
/**
* Plugin Name: Find a Fitter by e64
* Description: AlloyGator Fitter Database with Google Maps integration for AlloyGator.
* Version: 1.5.2
* WC requires at least: 5.2.2
* WC tested up to: 5.2.2
* Author: e64
* Author URI: http://e64.com
* Text Domain: wporg
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

defined( 'ABSPATH' ) or die( 'Must be run within WordPress!' );

define( 'e64_faf',
	array(
		'plugin-path' => plugin_dir_path( __FILE__ ),
		'plugin-url' => plugin_dir_url( __FILE__ ),
		'new-url' => admin_url('admin.php')."?page=faf-new-fitter"
	)
);


if ( strpos($_SERVER['SERVER_NAME'], "pre64.co.uk") !== false) {
	$jsver = "1.4.9". rand();
}
else {
	$jsver = "1.12.8";
}

require_once( e64_faf['plugin-path'] . 'admin/faf_adminfunc.php' );
require_once( e64_faf['plugin-path'] . 'admin/faf_dbcontrol.php' );
require_once( e64_faf['plugin-path'] . 'admin/faf_classes.php' );

register_activation_hook( __FILE__, 'e64_faf_install' );

add_action('init', 'e64_findAfitter_register' );
add_action('wp_logout','e64_end_session');
add_action('wp_login','e64_end_session');

add_action( 'wp_ajax_e64_ajax_handler', 'e64_ajax_handler' );
add_action( 'wp_ajax_nopriv_e64_ajax_handler', 'e64_ajax_handler' );

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'add_action_links' );

e64_wooCommerceHooks();

/*
 *
 *
 *
 *
 *
 */

function e64_faf_update_checks() {

	checkUpdateFAFOption('e64_faf_google_maps_api_id', "");
	checkUpdateFAFOption('e64_faf_google_maps_initial_zoom', 12);
	
	checkUpdateFAFOption('e64_faf_fitter_types', 'Mobile\nStatic');
	checkUpdateFAFOption('e64_faf_radius_options_in_miles', '5\n10\n20\n30\n40\n50\n75\n100');
	checkUpdateFAFOption('e64_faf_search_in_country', '');
	
	checkUpdateFAFOption('e64_faf_initial_page_html', '<h2>Find your nearest<br/>Five Star Fitter</h2>\n<p>Enter your postcode to find AlloyGator fitters in your area.</p>\n<p>Please note: you will receive full contact details for your chosen Five Star Fitter in your order confirmation email.</p>');
	checkUpdateFAFOption('e64_faf_book_fitter_product_url', '/product/set-of-4-alloygators/?attribute_pa_buy-with-fitting=buy-with-fitting');
	
	checkUpdateFAFOption('e64_faf_picking_a_fitter_from_product_page_button_text', 'Use this Fitter');
	
	checkUpdateFAFOption('e64_faf_buy_with_selected_fitter_cart_text', 'To be fitted by @companyName@');
	
	checkUpdateFAFOption('e64_faf_enable_find_a_fitter_sales_funnels', 'N');
	
}


function add_action_links ( $links ) {
	$mylinks = array(
		'<a href="/wp-admin/admin.php?page=faf-settings">Settings</a>',
	);
	return array_merge( $links, $mylinks );
}


function e64_end_session() {
	session_destroy ();
}

function e64_wooCommerceHooks() {
	
	$enableFunnel = strtoupper(esc_attr(get_option('e64_faf_enable_find_a_fitter_sales_funnels')));
	
	if ("Y"==$enableFunnel) {
		add_action( 'woocommerce_before_add_to_cart_button', 'e64_faf_before_add_to_cart_button' );
		add_action( 'woocommerce_after_single_product', 'e64_find_a_fitter_overlay', 20 );
		
		add_filter( 'woocommerce_add_cart_item_data', 'e64_faf_woocommerce_add_cart_item_data', 10, 3 );
		add_filter( 'woocommerce_get_item_data', 'e64_faf_woocommerce_get_item_data', 10, 2 );
		add_filter( 'woocommerce_checkout_fields', 'e64_faf_woocommerce_shipping_fields');
		add_filter( 'woocommerce_admin_shipping_fields' , 'e64_faf_woocommerce_admin_shipping_fields');
		
		// add fitter recipient to specific emails
		add_filter( 'woocommerce_email_recipient_customer_processing_order' , 'e64_add_fitter_recipient', 20, 2 );
		add_filter( 'woocommerce_email_recipient_customer_completed_order' , 'e64_add_fitter_recipient', 20, 2 );
		add_filter( 'woocommerce_email_recipient_customer_note' , 'e64_add_fitter_recipient', 20, 2 );
		
		// display fitter meta key in email
		add_action( 'woocommerce_before_template_part' , 'e64_fitter_before_email_addresses', 10, 4 );
	}
}

// First we register our resources using the init hook
function e64_findAfitter_register() {
	global $jsver, $e64_faf_registered;
	
	e64_faf_update_checks();

	wp_register_script("e64faf-js", e64_faf['plugin-url']."js/e64faf.js", array('jquery'), $jsver, true);
	
	wp_register_style("e64faf-css", e64_faf['plugin-url']."css/e64faf.css", array(), $jsver, "all");
	wp_register_style("e64faf-mid", e64_faf['plugin-url']."css/e64faf-mid.css", array(), $jsver, "(max-width: 1136px)");
	wp_register_style("e64faf-sml", e64_faf['plugin-url']."css/e64faf-sml.css", array(), $jsver, "(max-width: 800px)");
	wp_register_style("e64faf-mbl", e64_faf['plugin-url']."css/e64faf-mbl.css", array(), $jsver, "(max-width: 640px)");
		
	wp_enqueue_style("e64faf-css");
	wp_enqueue_style("e64faf-mid");
	wp_enqueue_style("e64faf-sml");
	wp_enqueue_style("e64faf-mbl");

	$enableFunnel = strtoupper(esc_attr(get_option('e64_faf_enable_find_a_fitter_sales_funnels')));
	if ("Y"==$enableFunnel) {
		wp_register_script("e64fafall-js", e64_faf['plugin-url']."js/e64faf_all.js", array('jquery'), $jsver, true);
		wp_enqueue_script("e64fafall-js");
	}
	
	e64_enqueueDynamicScripts();
	
	add_shortcode( 'e64-find-a-fitter', 'e64_findAfitter_search' );
		
	if(!session_id()) session_start();
}

function e64_enqueueDynamicScripts() {
}

function e64_findAfitter_search($atts = [], $content = null, $tag = '') {
	// normalize attribute keys, lowercase
	$atts = array_change_key_case((array)$atts, CASE_LOWER);
	
	$enableFunnel = strtoupper(esc_attr(get_option('e64_faf_enable_find_a_fitter_sales_funnels')));
	
	// override default attributes with user attributes
	$cSselector_atts = shortcode_atts([
									 '5-star-only' => 'N',
									 'book-button' => '',
									 'postcode' => '',
									 'radius' => ''
								 ], $atts, $tag);
	
	$fiveStar = $cSselector_atts['5-star-only'];
	$bookBtn = $cSselector_atts['book-button'];
	$def_postcode = $cSselector_atts['postcode'];
	$def_radius = $cSselector_atts['radius'];
	
	if ("Y"==$bookBtn) {
		$bookBtn = 'Book this Fitter';
	}
	
	global $wpdb;
	
	$table_name = $wpdb->prefix.'e64_faf_Fitters';
	
	$sql = "Select id,
				fitterCompany,
				fitterAddrLine1,
				fitterAddrLine2,
				fitterTown,
				fitterCity,
				fitterPostcode,
				fitterCountry,
				fitterPhone,
				fitterEmail,
				fiveStarFitter,
				fitterType,
				radiusCovered,
				fitterLongitude,
				fitterLatitude
			from ".$table_name." ";
	
	if ("Y"==$fiveStar) {
		$sql .= "Where fiveStarFitter = 'Y' ";
	}
	$sql .= "Order by fitterCompany ";
	
	$fitterRecords = json_encode($wpdb->get_results($sql));
	
	$defValues = array (
		'fiveStar' => $fiveStar, 
		'bookBtn' => $bookBtn,
		'mapZoom' => esc_attr(get_option('e64_faf_google_maps_initial_zoom')),
		'adUrl' => e64_faf['plugin-url'],
		'fitters' => $fitterRecords,
		'bookUrl' => esc_attr(get_option('e64_faf_book_fitter_product_url')),
		'ajaxurl' => admin_url('admin-ajax.php'),
		'enableFunnel' => $enableFunnel,
	);
	
	wp_localize_script("e64faf-js", 'php_vars', $defValues );
	wp_enqueue_script("e64faf-js");	
	
	$mapsAPI = esc_attr(get_option('e64_faf_google_maps_api_id'));
	wp_register_script("e64-gmaps", "https://maps.googleapis.com/maps/api/js?key=".$mapsAPI."&callback=e64_mapsLoaded&libraries=geometry", array(), $jsver, true);
	wp_enqueue_script("e64-gmaps");
		
	$zoom = esc_attr(get_option('e64_faf_google_maps_initial_zoom'));
	$fitterTypes = esc_attr(get_option('e64_faf_fitter_types'));
	$radiusCovered = esc_attr(get_option('e64_faf_radius_options_in_miles'));
	$restrictCountry = esc_attr(get_option('e64_faf_search_in_country'));
		
	$pageHTML = get_option('e64_faf_initial_page_html');
	
	$radius = explode("\r\n", $radiusCovered);
	
	$radiusOpts = "";
	foreach ($radius as &$opts) {
		$radiusOpts .= "<option value='$opts'";
		if ($opts==$def_radius) $radiusOpts .= ' selected="selected"';
		$radiusOpts .= ">$opts miles</option>";
	}
	
	ob_start();
	?>
	
	<div class="e64-container">
		<?php if (""==$fitterTypes || ""==$radiusCovered || ""==$zoom) { ?>
			<div class='e64-admin-row'>
				<h3>Plugin Setup required</h3>
				<p>Thsi plug-in has not been properly set up, please contact the website administrator.</p>
			</div>
		<?php }
		else {
			?>
			<div>
				<form method="post" class="fitterSearchForm">
					<input type="hiddden" name="e64-currentLatitude" id="e64-currentLatitude" hidden />
					<input type="hiddden" name="e64-currentLongitude" id="e64-currentLongitude" hidden />
					<input type="hiddden" name="e64-restrictCountry" id="e64-restrictCountry"value="<?php echo $restrictCountry ?>" hidden />
					<div class='e64-faf-row e64-extra-query'>
						<div class='e64-col e64-col-12'>
							<input type='text' name='e64-postcode' id='e64-postcode-2' class='e64-postcode' value='<?php echo $def_postcode; ?>' placeholder="Your postcode or town" />
							
							<select name='e64_radius' id='e64_radius_2' class='e64_radius'>
								<option value="0"></option>
								<?php echo $radiusOpts; ?>
							</select>
							
							<a class="e64_findFitters e64-faf-button" href="#">Find a Fitter</a>
							<a class="e64_useCurrentLocation e64-faf-button" href="#"><i class="fa fa-map-marker"></i>Or Search Using Current Location</a>
						</div>
					</div>
					<div class='e64-faf-row'>
						<div class='e64-col e64-col-4 e64-mapList'></div>
						<div class='e64-col e64-col-4 e64-firstQuery e64-active-search'>
							<?php echo $pageHTML; ?>
							<div class='e64-faf-row'>
								<div class='e64-col e64-col-12'>
									<input type='text' name='e64-postcode' id='e64-postcode-1' class='e64-postcode' value='<?php echo $def_postcode; ?>' size='10' placeholder='Your postcode or town' />
								</div>
							</div>
							<div class='e64-faf-row'>
								<div class='e64-col e64-col-12'>
									<select name='e64_radius' id='e64_radius_1' class='e64_radius'>
										<option value="0"></option>
										<?php echo $radiusOpts; ?>
									</select>
								</div>
							</div>
							<div class='e64-faf-row'>
								<div class='e64-col e64-col-12'>
									<a class="e64_findFitters e64-faf-button" href="#">Find a Fitter</a>
									<a class="e64_useCurrentLocation e64-faf-button" href="#"><i class="fa fa-map-marker"></i>Or Search Using Current Location</a>
								</div>
							</div>
						</div>
						<div class='e64-col e64-col-8 mapContainer'>
							<div id='e64-fitterMap'></div>
						</div>
					</div>
					
				</form>
			</div>
			<?php
		}
		?>
	</div>

	<?php
	
	$fitterSearch = ob_get_clean();
	
	return $fitterSearch;
	
}

function e64_faf_woocommerce_add_cart_item_data($cart_item_data, $product_id, $variation_id ) {
	
	$id = filter_input( INPUT_POST, 'e64-fitter-id' );
	
	if (intval($id) > 0 ) {
		$cart_item_data['e64-fitter-id'] = $id;
	}
	
	return $cart_item_data;		
}

function e64_faf_woocommerce_get_item_data( $item_data, $cart_item ) {
	
	$id = $cart_item['e64-fitter-id'];
	
	if (intval($id) > 0 ) {
		$data = getFitterDetails($id);
		
		$item_data[] = array(
			'key'     => __( 'Fitted by', 'e64' ),
			'value'   => $id,
			'display' => $data['fitterCompany'],
		);
	}
	
	return $item_data;
}



function e64_faf_before_add_to_cart_button() {
	global $wpdb;
	
	$id = $_GET['fid'];
	if (""==$id) $id = $_POST['e64-fitter-id'];
	
	$useid = "";
	
	$text = get_option('e64_faf_buy_with_selected_fitter_cart_text');
	
	if (intval($id) <= 0 ) {
		echo '<div class="woocommerce-variation e64-fitting-company e64-display-none" data-template="'.$text.'"><div class="woocommerce-variation-description"><p></p></div></div>';
	}
	else {
		$table_name = $wpdb->prefix.'e64_faf_Fitters';
		
		$rowcount = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE id = $id");
		
		if ($rowcount>0) {
			
			$useid = $id;
			
			$data = getFitterDetails($id);
			
			$text = str_replace('@companyName@', $data['fitterCompany'], $text);
						
			$classes = esc_attr(get_option('e64_faf_buy_with_selected_fitter_classes'));
			
			echo	'<div class="woocommerce-variation e64-fitting-company">'.
						'<div class="woocommerce-variation-description" data-template="'.$text.'"><p>'.$text.'</p>'.
						'</div>'.
					'</div>';
			
			// '<button class="button go-checkout">Go to Checkout</button>'.
			// '<button type="submit" class="e64-buy-with-fitting '.$classes.'">'.$text.'</button>';
			
		}
	}	
	echo '<input type="hidden" value="'.$useid.'" name="e64-fitter-id" id="e64-fitterid" hidden/>';
}


function e64_faf_woocommerce_shipping_fields($fields) {
	
	$id = getFitterIdFromCart();
	
	if (intval($id) > 0 ) {
		
		unset($fields['shipping']['shipping_first_name']);
		unset($fields['shipping']['shipping_last_name']);
		unset($fields['shipping']['shipping_state']);
		
		unset($fields['shipping']['address_book']);
		
		$data = getFitterDetails($id);
		
		$townCity = $data['fitterCity'];
		if (""==$townCity) $townCity = $data['fitterTown'];
		
		$fields['shipping']['shipping_company']['label'] = 'Fitting Company';
		$fields['shipping']['shipping_company']['required'] = true;
		$fields = setShippingField($fields, 'shipping_company', $data['fitterCompany'], 5);
		
		if ( ""!=$data['fitterEmail'] && is_email($data['fitterEmail']) ) {
			$fields['shipping']['shipping_email']['label'] = 'Fitter Email';
			$fields['shipping']['shipping_email']['required'] = true;
			$fields = setShippingField($fields, 'shipping_email', $data['fitterEmail'], 7);
		}
		
		$fields = setShippingField($fields, 'shipping_phone', $data['fitterPhone'], 10);
		$fields = setShippingField($fields, 'shipping_address_1', $data['fitterAddrLine1'], 15);
		$fields = setShippingField($fields, 'shipping_address_2', $data['fitterAddrLine2'], 20);
		$fields = setShippingField($fields, 'shipping_city', $townCity, 25);
		$fields = setShippingField($fields, 'shipping_postcode', $data['fitterPostcode'], 30);
		$fields = setShippingField($fields, 'shipping_country', $data['fitterCountry'], 35);
		
	}
	
	return $fields;
}


function e64_faf_woocommerce_admin_shipping_fields($fields) {
	$fields['shipping_email'] = array(
		'label'         => 'Fitter Email',
		'required'      => true,
		'class'         => array( 'form-row-first' ),
		'validate'      => array( 'email' ),
	);
	return $fields;
}


function getFitterIdFromCart() {
	
	$id = 0;
	
	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				
		$fitterid = $cart_item['e64-fitter-id'];
		
		$id = intval($fitterid);
		if ($id > 0) {
			break;
		}
	}
	
	return $id;
}


function setShippingField($fields, $name, $val, $priority) {
	if (""==$val) {
		unset($fields['shipping'][$name]);
	}
	else {
		$fields['shipping'][$name]['class'] = array('e64-default-value');
		$fields['shipping'][$name]['custom_attributes'] = Array('data-e64value' => $val);
		$fields['shipping'][$name]['priority'] = $priority;
		$fields['shipping'][$name]['options'] = array('readonly' => 'readonly');
		$fields['shipping'][$name]['required'] = true;
	}
	return $fields;
}


function getFitterDetails($id) {
	global $wpdb;

	$table_name = $wpdb->prefix.'e64_faf_Fitters';
	
	$sql = "Select id,
				fitterContact,
				fitterCompany,
				fitterAddrLine1,
				fitterAddrLine2,
				fitterTown,
				fitterCity,
				fitterPostcode,
				fitterCountry,
				fitterPhone,
				fitterEmail,
				fiveStarFitter,
				fitterType,
				radiusCovered,
				fitterLongitude,
				fitterLatitude
			from ".$table_name." 
		Where id = ".$id;
	
	$rec = $wpdb->get_results($sql);
	
	$data = (array)$rec[0];
	
	return $data;
}

function checkUpdateFAFOption($opt, $val) {
	if (get_option($opt) === false)
		update_option($opt, $val);
}


function e64_find_a_fitter_overlay() {
	
	$btnText = get_option('e64_faf_picking_a_fitter_from_product_page_button_text');
	$radius = get_option('e64_faf_google_maps_initial_zoom');
	
	echo "<div class='e64-faf-grey-overlay'>";
					
		echo e64_findAfitter_search(array('book-button'=>$btnText, 'radius'=>'30', '5-star-only'=>'Y'));
		
		echo "<div class='e64-close-button'><a href='#' class='e64-closeOverlay'><i class='fa fa-close'></i> Close</a></div>";
		
		// <a href='#' class=''><i class='fa fa-close fa-2x'></i></a>
		
	echo "</div>";
}


function e64_add_fitter_recipient( $email, $order ) {
	
	$additional_email = get_post_meta( $order->get_id(), '_shipping_email', true );
	
	if( $additional_email && is_email( $additional_email )){
		if( is_array( $email ) ){
			$email = explode( ',', $email );
			array_push( $email, $additional_email );
			$email = implode( ',', $email );
		} elseif( is_string( $email ) ){
			$email .= "," . $additional_email;
		}
	}
	
	return $email;
}


/**
 * Display meta in my-account area Order overview
 *
 * @var  array $fields
 * @return  array
 * @since 1.0
 */

function e64_fitter_before_email_addresses( $template_name, $template_path, $located, $args ){
	
	if( $template_name == 'emails/email-addresses.php' && isset( $args['order' ] ) && ( $value = get_post_meta( $args['order']->get_id(), '_shipping_email', true ) ) ){ 

		if ( isset( $args['plain_text'] ) && $args['plain_text'] ){

			echo 'Fitters Email: ' . $value . "\n";

		} else {

			echo '<p><strong>Fitters Email:</strong> ' . $value . '</p>';

		}

	}

}

?>

