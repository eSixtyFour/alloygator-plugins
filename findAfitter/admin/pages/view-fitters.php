<div class="wrap">
	
	<hr class='wp-header-end'>

	<div class='e64-admin-row mb-10'>
		<h1 class='wp_heading_inline'>AlloyGator Fitters</h1>
		<a href='<?php echo e64_faf['new-url']?>' class='page-title-action'>Add New</a>
	</div>

	<div class='e64-admin-row mb-10'>
		<h3>Search for a fitter</h3>
		<p>You can search for Fitters using all, or just part of their company name, town/city or postcode.</p>
		<form action="" method="GET" class="e64-search-form">
			<?php
				$search_nonce = wp_create_nonce( 'search_nonce' );
			?>
			<input type="hidden" name="page" value="alloygator-fitters" hidden />
			<input type="hidden" name="orderby" value="<?php echo $_GET["orderby"]; ?>" hidden />
			<input type="hidden" name="order" value="<?php echo $_GET["order"]; ?>" hidden />
			<input type="hidden" name="_wpnonce" value="<?php echo $search_nonce; ?>" hidden />
			
			<label>Fitter Company
				<input type="text" name="fitterCompany" value="<?php echo $_GET["fitterCompany"]; ?>" />
			</label>
			<label>Fitter Town/City
				<input type="text" name="fitterTownCity" value="<?php echo $_GET["fitterTownCity"]; ?>" />
			</label>
			<label>Fitter Postcode
				<input type="text" name="fitterPostcode" value="<?php echo $_GET["fitterPostcode"]; ?>" />
			</label>
			<label>Fitter Type
				<select name="fiveStarFitter">
					<option value="" <?php if ($_GET["fiveStarFitter"]=="") {echo 'selected="true"';} ?>>all</option>
					<option value="Y" <?php if ($_GET["fiveStarFitter"]=="Y") {echo 'selected="true"';} ?>>Five Star Only</option>
					<option value="N" <?php if ($_GET["fiveStarFitter"]=="N") {echo 'selected="true"';} ?>>Not Five Star</option>
				</select>
			</label>
			<input type="submit" name="searchFitters" value="Search Fitters" class="button action" />
		</form>
	</div>
			
	<?php
	
	$fittersTable = new e64_Fitters_List();
	
	$fittersTable->prepare_items(); 
	
	echo "<form method='post'>";
	$fittersTable->display(); 
	echo "</form>";
	
	?>
	
</div>

