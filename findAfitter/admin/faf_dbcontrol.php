<?php

function e64_faf_install() {
	fitterDB::createTable();
}


class fitterDB {

	public function createTable() {
		global $wpdb;
		
		$table_name = $wpdb->prefix . "e64_faf_Fitters"; 
		
		$charset_collate = $wpdb->get_charset_collate();
		
		$sql = "CREATE TABLE $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  dateadded datetime NOT NULL,
		  fitterCompany varchar(200) NOT NULL,
		  fitterContact varchar(200),
		  fitterAddrLine1 varchar(150) NOT NULL,
		  fitterAddrLine2 varchar(150),
		  fitterTown varchar(150),
		  fitterCity varchar(150),
		  fitterPostcode varchar(150),
		  fitterCountry varchar(150) NOT NULL,
		  fitterPhone varchar(30),
		  fitterEmail varchar(250) NOT NULL,
		  fiveStarFitter varchar(1),
		  fitterType varchar(50) NOT NULL,
		  radiusCovered smallint(2),
		  fitterLongitude float(9,6) NOT NULL,
		  fitterLatitude float(9,6) NOT NULL,
		  PRIMARY KEY  (id)
		) $charset_collate;";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		dbDelta( $sql );
		
		if ( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name ) {
			add_option( "e64_faf_db_version", "1.0" );
		}
		
	}

}

?>