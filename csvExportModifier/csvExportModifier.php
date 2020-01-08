<?php
/**
* Plugin Name: AlloyGator wooCommerce CSV Export modifier
* Description: Modifies the CSV Export output to add extra line items to the export for shipping and the different colours and sizes ordered.
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


define( 'e64_csv',
	array(
		'plugin-path' => plugin_dir_path( __FILE__ ),
		'plugin-url' => plugin_dir_url( __FILE__ ),
	)
);


if ( strpos($_SERVER['SERVER_NAME'], "pre64.co.uk") !== false) {
	$jsver = "1.4.9". rand();
}
else {
	$jsver = "1.1.5";
}

require_once( e64_csv['plugin-path'] . 'admin/csvexp_admin.php' );


 /**
 * Uses the WooCommerce currency display settings to format CSV price values instead of machine-readable values
 *
 * @param array $order_data the data for the export row
 * @param \WC_Order $order
 * @param \WC_Customer_Order_CSV_Export_Generator $generator
 * @return array - updated row data
 */
function e64_customer_order_add_extra_rows( $order_data, $order, $generator ) {
	//$output = print_r($order_data, true);
	//file_put_contents(e64_csv['plugin-path'].'order_data.txt', 'ORIGINAL RECORD ARRAY'.PHP_EOL.$output, FILE_APPEND);	

	$fittingOption = get_option('e64_csv_order_with_fitting_dropdown_text', '');
	$fittingSKU = get_option('e64_csv_order_with_fitting_sage_sku', '');
	$fittingDesc = get_option('e64_csv_order_with_fitting_sage_description', '');
	$fittingCost = floatval(get_option('e64_csv_fitting_cost_for_sage_import', '0'));

	$VATPercentage = floatval(get_option('e64_csv_percentage_for_calculating_vat', '0')) / 100;

	$shippingSKU = get_option('e64_csv_order_shipping_sage_sku', '');
	$shippingDesc = get_option('e64_csv_order_shipping_sage_description', '');

	if (""==$fittingOption) {
		$new_output = $order_data;
	}
	else {
		$new_output = []; // This will be the array that gets returned to the calling procedure
		$addFitting = false;

		foreach ($order_data as $order) {
			if (is_array($order)) {
				
				if (floatval($order["order_id"]) > 0) {

					// Save out the order total as it stands right now, so we can modifiy it if we need to
					$order_total = floatval($order["item_subtotal"]);

					// Get the meta data into an array so we can check it for anything else we need to process and loop through
					// it adding any extra line items we need to, and also modifying the original line item total if needs be.
					$meta = explode(",", $order['item_meta']);
					foreach ($meta as $option) {
						if (strpos($option, "=")) {	
							$opts = explode("=", $option);
							switch ($opts[1]) {
								case $fittingOption:
									$addFitting = true;
									$order_total = $order_total - floatval($fittingCost);
									break;
							}
						}
					}

					// Now update the order total with the possibly modified value, and then copy the original
					// line item into the new order array
					$order["item_subtotal"] = $order_total;
					$order["item_subtotal_tax"] = round($order_total * $VATPercentage, 2);
					$new_output[] = $order;
					
					// Add the shipping line item 
					$order["item_sku"] = $shippingSKU;
					$order["item_name"] = $shippingDesc;
					$order["item_subtotal"] = $order["shipping_total"];
					$order["item_subtotal_tax"] = round(floatval($order["shipping_total"]) * $VATPercentage, 2);
					$new_output[] = $order;

					// next check if we need to add a fitting line, and add it if we do
					if (addFitting) {
						$order["item_sku"] = $fittingSKU;
						$order["item_name"] = $fittingDesc;
						$order["item_subtotal"] = $fittingCost;
						$order["item_subtotal_tax"] = round(floatval($fittingCost) * $VATPercentage, 2);
						$new_output[] = $order;
					}
				}

			}
		}
		$new_output["shipping_phone"] = $order_data["shipping_phone"];
		
		//$output = print_r($new_output, true);
		//file_put_contents(e64_csv['plugin-path'].'order_data.txt', 'UPDATED RECORD ARRAY'.PHP_EOL.$output, FILE_APPEND);	
	}

	return $new_output;
}
add_filter( 'wc_customer_order_csv_export_order_row', 'e64_customer_order_add_extra_rows', 10, 3 );



/**
 * Add weight to line item data
 *
 * @param array $line_item the original line item data
 * @param array $item the item's order data
 * @param object $product the \WC_Product object for the line
 * @param object $order the \WC_Order object being exported
 * @return array the updated line item data
 */
function e64_add_extra_line_items_to_csv_export( $line_item, $item, $product, $order ) {

	printOutArray($line_item, "line_item.txt");
	printOutArray($item, "item.txt");
	printOutArray($product, "product.txt");
	printOutArray($order, "order.txt");
	
	$new_row = $line_item;

	$return_items = array_push($line_item, ...$new_row);


	return $return_items;
}
//add_filter( 'wc_customer_order_csv_export_order_line_item', 'e64_add_extra_line_items_to_csv_export', 10, 4 );

