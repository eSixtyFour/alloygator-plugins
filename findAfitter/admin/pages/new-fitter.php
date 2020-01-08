<?php
global $wpdb;

$formCtl = new e64_formControl();

$countries = $formCtl->supportedCountries();

$action = strtolower($_GET['action']);
$id = (int)$_GET['id'];

$data = array();
$editing = "N";

$initHideClass = "e64_inithide";

$Lat = 0;
$Lng = 0;

$zoom = esc_attr(get_option('e64_faf_google_maps_initial_zoom'));
$fitterTypes = esc_attr(get_option('e64_faf_fitter_types'));
$radiusCovered = esc_attr(get_option('e64_faf_radius_options_in_miles'));


if ("edit" == $action && $id > 0) {
	
	$table_name = $wpdb->prefix.'e64_faf_Fitters';
	
	$sql = "SELECT * FROM ".$table_name." WHERE id = ".$id;
	
	$rec = $wpdb->get_results($sql);
	
	$data = (array)$rec[0];
	
	
	$editing = "Y";
	
	$Lat = $data['fitterLatitude'];
	$Lng = $data['fitterLongitude'];
	
	$initHideClass = "";
}
?>

<script>var e64_initLat = <?php echo $Lat; ?>, e64_initLng = <?php echo $Lng; ?>, e64_zoom=<?php echo $zoom; ?>;</script>
<div class="wrap">
 
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
 	
	<?php if (""==$fitterTypes || ""==$radiusCovered || ""==$zoom) { ?>
		<div class='e64-admin-row'>
			<h3>Plugin Setup required</h3>
			<p>You don't appear to have completed the plugin setup. Please go to the <a href="<?php echo admin_url('admin.php') ?>?page=faf-settings">plugin settings</a> page and complete all the setup options.</p>
		</div>
	<?php }
	else {
		?>
		<form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
			<div class='e64-admin-row'>
				<ul class='e64-linear'>
				<?php
					echo $formCtl->e64_add_hidden_field('fitter_id', $id);
					echo $formCtl->e64_add_hidden_field('editing', $editing);
					echo $formCtl->e64_add_hidden_field('Longitude', $data['fitterLongitude']);
					echo $formCtl->e64_add_hidden_field('Latitude', $data['fitterLatitude']);
					echo $formCtl->e64_add_hidden_field('Five Star Rated', $data['fiveStarFitter']);
				?>
				</ul>
			</div>
			
			<div class='e64-admin-row'>
				<ul class='e64-linear'>
					<li><h2>Fitter Details</h2></li>
					
					<?php
						
						echo $formCtl->e64_add_text_field('Company Name', $data['fitterCompany'], true);
						echo $formCtl->e64_add_text_field('Contact Name', $data['fitterContact'], true);
						echo $formCtl->e64_add_text_field('Phone Number', $data['fitterPhone'], true);
						echo $formCtl->e64_add_text_field('Email Address', $data['fitterEmail'], true);
						
					?>
				</ul>
				
				<ul class='e64-linear'>
					<li><h2>Fitter Address</h2></li>
					
					<?php
						
						echo $formCtl->e64_add_text_field('House Name / No', $data['fitterAddrLine1'], true);
						echo $formCtl->e64_add_text_field('Street / Road', $data['fitterAddrLine2'], true);
						echo $formCtl->e64_add_text_field('Town', $data['fitterTown']);
						echo $formCtl->e64_add_text_field('City', $data['fitterCity'], true);
						echo $formCtl->e64_add_text_field('Postcode', $data['fitterPostcode']);
						echo $formCtl->e64_add_dropdown('Country', $countries, $data['fitterCountry'], true);
						
					?>
				</ul>
				
				
				<ul class='e64-linear'>
					<li><h2>Fitter Services</h2></li>
					<?php
						echo $formCtl->e64_add_dropdown('Fitter Type', explode("\r\n", $fitterTypes), $data['fitterType'], true);				
						echo $formCtl->e64_add_dropdown('Radius Covered', explode("\r\n", $radiusCovered), $data['radiusCovered'], true);
					?>
					<li><label class='toggle-5-star'>5 Star Fitter: </label>
						<a href='#' style='background-image:url(<?php echo e64_faf['plugin-url'] ?>admin/assets/five-star-fitter-opts.png);' class='five-star-rating <?php if($data['fiveStarFitter']=='Y') echo 'is-five-star' ?>'></a>
					</li>
				</ul>
			</div>
			
			<div class='e64-admin-row'>
				<ul class='e64-linear'>
					<li><a href='#' id='faf_find_fitter' class='page-title-action'>Locate Fitter on Map</a></li>
				</ul>
			</div>
			
			<div class="<?php echo $initHideClass; ?>">
				
				<div class='e64-admin-row' id='findFitterGuide'>
					<ul class='e64-linear'>
						<li><h3>If the marker is not exactly in the right place, please drag it over this fitter's exact location.</h3></li>
					</ul>
				</div>
				
				<div class='e64-admin-row'>
					<div id='locMap'></div>
				</div>
				
				<div class='e64-admin-row'>
					<ul class='e64-linear'>
						<li>
							<?php
								wp_nonce_field( 'faf-create-new', 'faf-save-fitter' );
								submit_button();
							?>
						</li>
					</ul>
				</div>
				
			</div>
			
		</form>
 		<?php
	}
	?>
</div><!-- .wrap -->
