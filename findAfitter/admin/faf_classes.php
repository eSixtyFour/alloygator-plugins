<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class e64_Fitters_List extends WP_List_Table {
	
	/** Class constructor */
	public function __construct() {
		
		parent::__construct( [
			'singular' => __( 'Fitter' ), //singular name of the listed records
			'plural'   => __( 'Fitters' ), //plural name of the listed records
			'ajax'     => false //does this table support ajax?
		] );

	}

	
	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	 
	// 'cb'				=> '<input type="checkbox" />', // to display the checkbox.
	
	public function get_columns() {
		$columns = array(
			'cb'      			=> '<input type="checkbox" />',
			'fitterCompany'		=>	__('Company Name'),
			'fitterContact'		=>	__('Contact Name'),
			'fitterCity'		=>	__('City'),
			'fitterPostcode'	=>	__('Postcode'),
			'fitterPhone'		=>	__('Phone'),
			'fitterEmail'		=>	__('Email'),
			'fiveStarFitter'	=>	__('5 star'),
			'fitterType'		=>	__('Type'),
			'radiusCovered'		=>	__('Radius'),
		);

		return $columns;
	}
	
	
	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'fitterCompany' => array('fitterCompany', false),
			'fitterCity' => array('fitterCity', false)
		);
		
		return $sortable_columns;
	}
	
	
	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {
		
		global $wpdb;
		
		$screen = get_current_screen();
		
		$table_name = $wpdb->prefix.'e64_faf_Fitters';
		
		$sql = "SELECT * FROM ".$table_name." ";
		
		
		if ( !empty( $_GET['searchFitters'] ) ) {
			$and = "WHERE ";
			
			if ( !empty( $_GET['fitterCompany'] ) ) {
				$sql .= $and . "fitterCompany like '%".esc_sql( $_GET['fitterCompany'] )."%' ";
				$and = "AND ";
			}
			if ( !empty( $_GET['fitterTownCity'] ) ) {
				$sql .= $and . "(fitterTown like '%".esc_sql( $_GET['fitterTownCity'] )."%' Or fitterCity like '%".esc_sql( $_GET['fitterTownCity'] )."%') ";
				$and = "AND ";
			}
			if ( !empty( $_GET['fitterPostcode'] ) ) {
				$sql .= $and . "fitterPostcode like '%".esc_sql( $_GET['fitterPostcode'] )."%' ";
				$and = "AND ";
			}
			
			if ( !empty( $_GET['fiveStarFitter'] ) ) {
				
				if ( $_GET['fiveStarFitter'] == "Y") {
					$sql .= $and . "fiveStarFitter = 'Y' ";
				}
				else {
					$sql .= $and . "fiveStarFitter <> 'Y' ";
				}
				
				$and = "AND ";
			}
		}
		
		if ( !empty( $_GET['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_GET['orderby'] );
			$sql .= !empty( $_GET['order'] ) ? ' ' . esc_sql( $_GET['order'] ) : ' ASC';
		}
		
		//Number of elements in your table?
		$totalitems = $wpdb->query($sql); //return the total number of affected rows
		
		//How many to display per page?
		$perpage = 20;
		
		//Which page is this?
		$paged = !empty($_GET["paged"]) ? esc_sql($_GET["paged"]) : 1;
		
		//Page Number
		if(empty($paged) || !is_numeric($paged) || $paged<=0 ){
			$paged=1;
		}
		
		//How many pages do we have in total?
		$totalpages = ceil($totalitems/$perpage);
		
		//adjust the query to take pagination into account
		if(!empty($paged) && !empty($perpage)) {
			$offset=($paged-1)*$perpage;
			$sql.=' LIMIT '.(int)$offset.','.(int)$perpage;
		}
		
		/* -- Register the pagination -- */
		$this->set_pagination_args( array(
			"total_items" => $totalitems,
			"total_pages" => $totalpages,
			"per_page" => $perpage,
		)
		);
		
		/* -- Register the Columns -- */
		$this->_column_headers = array( 
		$this->get_columns(), 
		array(),
		$this->get_sortable_columns()
		);
		
		/* -- Fetch the items -- */
		$this->items = $wpdb->get_results($sql);
		
		$this->process_bulk_action();
	}
	
	
	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'fitterCompany':
			case 'fitterContact':
			case 'fitterAddrLine1':
			case 'fitterAddrLine2':
			case 'fitterTown':
			case 'fitterCity':
			case 'fitterPostcode':
			case 'fitterPhone':
			case 'fitterEmail':
			case 'fiveStarFitter':
			case 'fitterType':
			case 'radiusCovered':
			case 'fitterLongitude':
			case 'fitterLatitude':
				return $item->$column_name;
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}
	
	/**
	 * Delete a fitter record.
	 *
	 * @param int $id fitter ID
	 */
	public static function delete_fitter( $id ) {
		global $wpdb;
		
		$table_name = $wpdb->prefix.'e64_faf_Fitters';

		$wpdb->delete(
			$table_name,
			array('id' => $id),
			array('%d')
		);
		
	}
	
	
	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item->id
		);
	}


	/**
	 * Method for fittercompany column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_fittercompany( $item ) {

		$action_nonce = wp_create_nonce( 'action_nonce' );

		$title = sprintf( '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s"><strong>%s</strong></a>', 'faf-new-fitter', 'edit', absint( $item->id ), $action_nonce, $item->fitterCompany);

		$actions = [
			'edit' => sprintf( '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">%s</a>', 'faf-new-fitter', 'edit', absint( $item->id ), $action_nonce, 'Edit' ),
			'delete' => sprintf( '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">%s</a>', esc_attr( $_GET['page'] ), 'delete', absint( $item->id ), $action_nonce, 'Delete' )
		];

		return $title . $this->row_actions( $actions );
	}

	function column_fitteremail( $item ) {

		$email = '<a href="mailto:'.$item->fitterEmail.'">'.$item->fitterEmail.'</a>';
		
		return $email;
	}


	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'bulk-delete' => 'Delete'
		);

		return $actions;
	}

	
	public function process_bulk_action() {
		
		//Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( !wp_verify_nonce( $nonce, 'action_nonce' ) ) {
				die( 'Must be loaded within WordPress' );
			}
			else {
				self::delete_fitter( absint( $_REQUEST['id'] ) );
				
				// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
				// add_query_arg() return the current url
				
				$url = admin_url('admin.php').'?page=alloygator-fitters';
				
				// Finally, redirect back to the admin page.
				wp_safe_redirect( urldecode( $url ) );
				exit;
			}

		}

		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
		     || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
		) {

			$delete_ids = esc_sql( $_POST['bulk-delete'] );

			// loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				self::delete_fitter( $id );
			}

			// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
			// add_query_arg() return the current url
			$url = admin_url('admin.php').'?page=alloygator-fitters';
			
			// Finally, redirect back to the admin page.
			wp_safe_redirect( urldecode( $url ) );
			exit;
		}
	}

}


class e64_formControl {
	
	private $newFitterData;
	
	function _construct() {
		$this->$newFitterData = array();
	}
	
	public function saveFitterInit() {
		add_action( 'admin_post', array( $this, 'saveNewFitter' ) );
	}
 	
	public function saveNewFitter() {
		global $wpdb;
		
		// First, validate the nonce and verify the user as permission to save.
		if ( ! ( $this->has_valid_nonce() && current_user_can( 'manage_options' ) ) ) {
			// TODO: Display an error message.
		}
		
		$this->$newFitterData = array();
		
		/*
		  
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  dateadded datetime NOT NULL,
		  fitterCompany varchar(200) NOT NULL,
		  fitterContact varchar(200) NOT NULL,
		  fitterAddrLine1 varchar(150) NOT NULL,
		  fitterAddrLine2 varchar(150),
		  fitterTown varchar(150),
		  fitterCity varchar(150) NOT NULL,
		  fitterPostcode varchar(150),
		  fitterCountry varchar(150) NOT NULL,
		  fitterPhone varchar(30) NOT NULL,
		  fitterEmail varchar(250) NOT NULL,
		  fiveStarFitter varchar(1),
		  fitterType varchar(1) NOT NULL,
		  radiusCovered smallint(2),
		  fitterLongitude float(9,6) NOT NULL,
		  fitterLatitude float(9,6) NOT NULL,
		  
		  
		*/
		
		$editing = $this->getField('faf_editing');
		
		// If the above are valid, sanitize and save the option.
		$this->saveField('Company Name', 'fitterCompany', 200);
		$this->saveField('Contact Name', 'fitterContact', 200);
		$this->saveField('Phone Number', 'fitterPhone', 30);
		$this->saveField('Email Address', 'fitterEmail', 250);
		
		$this->saveField('House Name / No', 'fitterAddrLine1', 150);
		$this->saveField('Street / Road', 'fitterAddrLine2', 150);
		$this->saveField('Town', 'fitterTown', 150);
		$this->saveField('City', 'fitterCity', 150);
		$this->saveField('Postcode', 'fitterPostcode', 150);
		$this->saveField('Country', 'fitterCountry', 150);

		$this->saveField('Five Star Rated', 'fiveStarFitter', 1);
		$this->saveField('Fitter Type', 'fitterType', 50);
		
		$this->saveField('Radius Covered', 'radiusCovered', 5, 0, true);
		$this->saveField('Latitude', 'fitterLatitude', 9, 6, true);
		$this->saveField('Longitude', 'fitterLongitude', 9, 6, true);
		
		$now = current_time('mysql', false);
		$this->$newFitterData['dateadded']=$now;
		
		$table_name = $wpdb->prefix . "e64_faf_Fitters"; 
		
		//print_r($this->$newFitterData);
		//exit;
		
		if ("Y"==$editing) {
			$id = $this->getField('faf_fitter_id');
			$wpdb->update($table_name, $this->$newFitterData, array('id' => $id));
		}
		else {
			$wpdb->insert($table_name, $this->$newFitterData);
		}
		
		$this->redirect();
	}
	
	public function e64_add_text_field($title, $value = '', $required = false, $dets = null) {
		
		$fld = $this->e64_getAdminId($title);
		
		$return = "<li>".$this->fieldLabel($title, $required);
		
		$return .= "<input type='text' name='$fld' id='$fld' value='$value'";
		
		if ($required) $return .= " class='required'";
		
		$return .= " />";
		
		$return .= "</li>";
		return $return;
	}
	
	
	public function e64_add_checbox($title, $checked = false, $required = false) {
		$fld = $this->e64_getAdminId($title);
		
		$return = "<li>".$this->fieldLabel($title, $required);
		
		$return .= "<input type='checkbox' name='$fld' id='$fld' value='Y'" ;
		
		if ($checked) {
			$return .= " checked='checked'";
		}
		
		if ($required) $return .= " class='required'";
		
		$return .= "/>";
		
		$return .= "</li>";
		return $return;
	}
	
	
	public function e64_add_dropdown($title, $options, $value='', $required = false) {
		$fld = $this->e64_getAdminId($title);
		
		$return = "<li>".$this->fieldLabel($title, $required);
		
		$return .= "<select name='$fld' id='$fld'";
		
		if ($required) $return .= " class='required'";
		
		$return .= "><option value=''> </option>";
		
		foreach ($options as &$option) {
			
			if (strlen($option)>=1 && substr($option, 0, 1) == "-") {
				$return .= "<option disabled>$option</option>";
			}
			else {
				$return .= "<option value='$option'";
				
				if ($option==$value) {
					$return .= " selected='selected'";
				}
				
				$return .= ">$option</option>";
			}
			
		}
		$return .= "</select>";
		
		$return .= "</li>";
		return $return;
	}
	
	
	public function e64_add_hidden_field($title, $value='') {
		
		$fld = $this->e64_getAdminId($title);
		$return = "<input type='hidden' name='$fld' id='$fld' value='$value' hidden />";
		
		return $return;
	}
	
	private function fieldLabel($title, $required) {
		$fld = $this->e64_getAdminId($title);
		
		$return = "<label for='$fld'>$title";
		
		if ($required) {
			$return .= "<span>*</span>";
		}
				
		$return .= "</label>";
		
		return $return;
	}
	
	public function supportedCountries() {
		return array(
			'United Kingdom',
			'Ireland',
			'--------------',
			'Austria',
			'Belgium',
			'Bulgaria',
			'Croatia',
			'Cyprus',
			'Czechia',
			'Denmark',
			'Estonia',
			'Finland',
			'France',
			'Germany',
			'Greece',
			'Hungary',
			'Italy',
			'Latvia',
			'Lithuania',
			'Luxembourg',
			'Malta',
			'Netherlands',
			'Poland',
			'Portugal',
			'Romania',
			'Slovakia',
			'Slovenia',
			'Spain',
			'Sweden'
		);
	}
	
	
	public function e64_getAdminID($name, $prefix='faf_')  {
		$return = $prefix.str_replace(' ', '-', strtolower($name));
		$return = str_replace('/', '_', $return);
		return $return;
	}
	
	public function e64_getAdminName($name, $prefix='faf_')  {
		$return = $prefix.str_replace(' ', '_', strtolower($name));
		$return = str_replace('/', '_', $return);
		return $return;
	}
	
	private function has_valid_nonce() {
		
		// If the field isn't even in the $_POST, then it's invalid.
		if ( ! isset( $_POST['faf-save-fitter'] ) ) { // Input var okay.
			return false;
		}
		
		$field  = wp_unslash( $_POST['faf-save-fitter'] );
		$action = 'faf-create-new';
		
		return wp_verify_nonce( $field, $action );
	
	}
	
	private function saveField($name, $dbfield, $size, $decimal=0, $float=false) {
		
		$fldName = $this->e64_getAdminID($name);
		
		$value = $this->getField($fldName);
		
		$comma = "";
		
		if ( '' !== $value ) {
			
			if ($float) {
				$floatval = round(floatval($value), $decimal);
				$this->$newFitterData[$dbfield]=$floatval;
			}
			else {
				$value = substr($value, 0, $size);
				$this->$newFitterData[$dbfield]=$value;
			}
			
			
		}
		
	}
	
	
	private function getField($name) {
		$value = '';
		if ( null !== wp_unslash( $_POST[$name] ) ) {
			
			$value = sanitize_text_field( $_POST[$name] );
						
		}
		return $value;
	}
	
	
	private function redirect() {
		
		$url = admin_url('admin.php').'?page=alloygator-fitters';
		
		// Finally, redirect back to the admin page.
		wp_safe_redirect( urldecode( $url ) );
		exit;
		
	}	
	
}

?>
