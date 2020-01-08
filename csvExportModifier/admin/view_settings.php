<div class="wrap">
	<div id="icon-tools" class="icon32"></div>
	<h2>AlloyGator CSV Export Settings</h2>
</div>


<div class="wrap">
	<?php settings_errors();?>
	<form method="POST" action="options.php" class="e64-csv-settings-form">
		<?php settings_fields('e64-csvexp-settings');?>
		<?php do_settings_sections('e64-csvexp-settings')?>
		<?php submit_button();?>
	</form>
</div>
