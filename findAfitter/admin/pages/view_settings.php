<div class="wrap">
	<div id="icon-tools" class="icon32"></div>
	<h2>Find a Fitter Settings</h2>
</div>


<div class="wrap">
	<?php settings_errors();?>
	<form method="POST" action="options.php" class="e64-settings-form">
		<?php settings_fields('faf-settings');?>
		<?php do_settings_sections('faf-settings')?>
		<?php submit_button();?>
	</form>
</div>
