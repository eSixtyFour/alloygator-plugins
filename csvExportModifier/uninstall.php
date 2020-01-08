<?php
global $wpdb;

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
	die;
}

// get list of options to delete
$sql = "SELECT option_name FROM $wpdb->options WHERE option_name like 'e64\_csv\_%'";
$options = $wpdb->get_results($sql);

foreach($options as $opt) {
	delete_option($opt->option_name);	
}

?>