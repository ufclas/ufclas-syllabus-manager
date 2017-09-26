<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://it.clas.ufl.edu/
 * @since      0.0.0
 *
 * @package    Syllabus_Manager
 * @subpackage Syllabus_Manager/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Syllabus_Manager
 * @subpackage Syllabus_Manager/admin
 * @author     Priscilla Chapman (CLAS IT) <no-reply@clas.ufl.edu>
 */
class Syllabus_Manager_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	
	/**
	 * Names of plugin's admin dashboard pages
	 *
	 * @since    0.0.0
	 * @access   private
	 * @var      string    $plugin_pages    The current version of this plugin.
	 */
	private $plugin_pages;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		
		$this->plugin_pages = array(
			'toplevel_page_syllabus-manager',
			'syllabus-manager_page_syllabus-manager-import',
		);
	}

	/**
	 * Register the stylesheets for the admin area.
	 * 
	 * @param string $hook The current admin page.
	 * @since 0.0.0
	 */
	public function enqueue_styles( $hook ) {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Syllabus_Manager_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Syllabus_Manager_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		
		// Only add styles to the plugin main pages
		if ( in_array( $hook, $this->plugin_pages ) ){
			wp_enqueue_style( 'bootstrap', plugins_url('includes/bootstrap/css/bootstrap.min.css', dirname(__FILE__) ), array(), $this->version, 'screen' );
            wp_enqueue_style( $this->plugin_name, plugins_url('css/syllabus-manager-admin.css', __FILE__ ), array(), $this->version, 'all' );
        }
	}

	/**
	 * Register the JavaScript for the admin area.
	 * 
	 * @param string $hook The current admin page.
	 * @since    0.0.0
	 */
	public function enqueue_scripts( $hook ) {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Syllabus_Manager_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Syllabus_Manager_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		
		// Only add scripts to the plugin main pages
		if ( 'toplevel_page_syllabus-manager' == $hook ){
			
			// Bootstrap
			wp_enqueue_script( 'bootstrap', plugins_url('includes/bootstrap/js/bootstrap.min.js', dirname(__FILE__)), array( 'jquery' ), $this->version, true );
			
            // VueJS
			$js_version = (WP_DEBUG)? 'vue.js' : 'vue.min.js';
            wp_enqueue_script( 'vue-js', plugins_url('includes/vue/' . $js_version, dirname(__FILE__)), array(), null, true);
            
			// Add custom script
			wp_enqueue_script( $this->plugin_name, plugins_url('js/syllabus-manager-admin.js', __FILE__), array( 'vue-js' ), $this->version, true );
			wp_localize_script( $this->plugin_name, 'syllabus_manager_data', array(
				'panel_title' => __('Courses', 'syllabus_manager'),
				'courses' => Syllabus_Manager_Course::get_courses(),
                'ajax_nonce' => wp_create_nonce('syllabus-manager-add-syllabus')
			));
		}
		
		if ( 'syllabus-manager_page_syllabus-manager-import' == $hook ){
			wp_enqueue_script( 'bootstrap', plugins_url('includes/bootstrap/js/bootstrap.min.js', dirname(__FILE__)), array( 'jquery' ), $this->version, true );
			wp_enqueue_script( 'vue-js', plugins_url('includes/vue/vue.min.js', dirname(__FILE__)), array(), null, true);
			wp_enqueue_script( 'vue-import', plugins_url('js/syllabus-manager-admin-import.js', __FILE__), array( 'vue-js' ), $this->version, true );
		}
	}
	
	/**
	 * Adds Syllabus Manager menu items
	 * 
	 * @since 0.0.0
	 */
	public function add_menu(){
		add_menu_page('Syllabus Manager', 'Syllabus Manager', 'manage_options', 'syllabus-manager', array( $this, 'display_admin_page'), 'dashicons-book-alt');
		add_submenu_page('syllabus-manager', 'Import', 'Import', 'manage_options', 'syllabus-manager-import', array( $this, 'display_import_page'));
		add_submenu_page('syllabus-manager', 'Courses', 'Courses', 'manage_options', 'edit.php?post_type=syllabus_course');
		add_submenu_page('syllabus-manager', 'Semesters', 'Semesters', 'manage_options', 'edit-tags.php?post_type=syllabus_course&taxonomy=syllabus_semester');
		add_submenu_page('syllabus-manager', 'Departments', 'Departments', 'manage_options', 'edit-tags.php?post_type=syllabus_course&taxonomy=syllabus_department');
		add_submenu_page('syllabus-manager', 'Instructors', 'Instructors', 'manage_options', 'edit-tags.php?post_type=syllabus_course&taxonomy=syllabus_instructor');
		add_submenu_page('syllabus-manager', 'Program Levels', 'Program Levels', 'manage_options', 'edit-tags.php?post_type=syllabus_course&taxonomy=syllabus_level');
	}
	
	/**
	 * Moves menu highlight to the Syllabus Manager item
	 * @param  string $parent_file [[Description]]
	 * @return string Parent menu item
	 *                       
	 * @since 0.0.1
	 */
	public function menu_highlight( $parent_file ){ 
		global $post_type;
		
		if ('syllabus_course' == $post_type) { 
			$parent_file = 'syllabus-manager';  
		}
		return $parent_file; 
	} 
	
	/**
	 * Displays the main Syllabus Manager Admin page
	 * 
	 * @since 0.0.0
	 */
	public function display_admin_page(){
		include 'partials/syllabus-manager-admin-display.php';
	}
	
	/**
	 * Displays the main Syllabus Manager Admin page
	 * 
	 * @since 0.0.0
	 */
	public function display_import_page(){
		include 'partials/syllabus-manager-import-display.php';
	}
	
	/**
	 * Gets data for the Schedule of Courses DataTable
	 * 
	 * @since 0.0.1
	 */
	public function get_main_table_data(){
				
		$courses = Syllabus_Manager_Course::get_courses();
		
		foreach ( $courses as $course ){
			echo '<tr>';
			$count = count( $course );
			for ( $i=0; $i<$count; $i++ ){
				echo '<td>' . $course[$i] . '</td>';
			}
			echo '</tr>';
		}
	}
	
	/**
	 * Gets data for the Schedule of Courses DataTable
	 * 
	 * @since 0.0.0
	 */
	public function get_main_table_json_data(){
		// Verify the request to prevent preocessing external requests
		check_ajax_referer( 'syllabus-manager-get-main', 'syllabus-manager-main-nonce' );
		
		echo json_encode( Syllabus_Manager_Course::get_courses() );
		
		wp_die(); // Required to terminate immediately and return a proper response
	}
    
    /**
	 * Gets data for the Schedule of Courses DataTable
	 * 
	 * @since 0.0.0
	 */
	public function add_syllabus(){
		// Verify the request to prevent preocessing external requests
		check_ajax_referer( 'syllabus-manager-add-syllabus', 'ajax_nonce' );
		
		if ( !current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array('msg' => __('You do not have sufficient permissions to access this page.', 'syllabus_manager')) );
		}
		
		// Merge post values into one array
		$post_data = $_POST['course_data'];
		
		if ( false ) {error_log(print_r($post_data, true));}
		
		$section = new Syllabus_Manager_Section( 
			$post_data['code'],
			$post_data['title'],
			$post_data['section_number'],
			$post_data['semester'],
			array($post_data['department']),
			$post_data['level'],
			explode(', ', $post_data['instructors'])
		);
		
		// Insert the post into the database
		$post_id = wp_insert_post( $section->get_post_args() );
		
		if ( !is_wp_error( $post_id ) ){
			error_log( 'Successfully inserted post: ' . $post_id . ' for ' . $section->section_id );
			add_post_meta( $post_id, 'sm_course_code', $section->course_code );
			add_post_meta( $post_id, 'sm_course_title', $section->course_title );
			add_post_meta( $post_id, 'sm_section_number', $section->section_code );
			wp_send_json_success( array('msg' => 'Added course: '. $section->section_id) );	
		}
		else {
			error_log( 'Error inserting post: ' . print_r($post_id, true) );
			wp_send_json_error( array('msg' => $post_id->get_error_message()) );
		}
	}
	
	/**
	 * Removes document from course
	 * 
	 * @since 0.1.0
	 * @todo Implement removing selected attachment
	 */
	public function remove_syllabus(){
		// Verify the request to prevent preocessing external requests
		check_ajax_referer( 'syllabus-manager-add-syllabus', 'ajax_nonce' );
		
		if ( !current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array('msg' => __('You do not have sufficient permissions to access this page.', 'syllabus_manager')) );
		}
		
		wp_send_json_success( array('msg' => 'Removed syllabus') );
	}
	
	
	
	/**
	 * Get course array from external source
	 * 
	 * @param  array $query_args Query to get data from external source
	 * @return array|false JSON array of course objects
	 *                              
	 * @since 0.0.1
	 */
	public function fetch_courses( $query_args = array() ){
		$defaults = array(
			'category' => 'RES',
			'course-code' => '',
			'course-title' => '',
			'cred-srch' => '',
			'credits' => '',
			'day-f' => '',
			'day-m' => '',
			'day-r' => '',
			'day-s' => '',
			'day-t' => '',
			'day-w' => '',
			'days' => 'false',
			'dept' => '',
			'eep' => '',
			'fitsSchedule' => 'false',
			'ge' => '',
			'ge-b' => '',
			'ge-c' => '',
			'ge-h' => '',
			'ge-m' => '',
			'ge-n' => '',
			'ge-p' => '',
			'ge-s' => '',
			'instructor' => '',
			'last-row' => '0',
			'level-max' => '--',
			'level-min' => '--',
			'no-open-seats' => 'false',
			'online-a' => '',
			'online-c' => '',
			'online-h' => '',
			'online-p' => '',
			'online-b' => '',
			'online-e' => '',
			'prog-level' => '', 
			'term' => '',
			'var-cred' => 'true',
			'writing' => '',
		);
		$args = array_merge($defaults, $query_args);
		
		// Get external data
		$api_url = 'https://one.uf.edu/apix/soc/schedule/?';
		$request_url = $api_url . http_build_query( $args );
		if ( false ) { error_log( 'request_url: ' . $request_url ); }
								  
		$response = wp_remote_get( $request_url );
		
		// Valid response
		if ( ! is_wp_error($response) && is_array($response) ){
			$headers = $response['headers'];
			$body = $response['body'];
			$response_data = json_decode($body);
			
			return $response_data[0]->COURSES;
		}
		return false;
	}
	
	/**
	 * Handle forms on the Import screen depending on action
	 * 
	 * @since 0.1.0
	 */
	public function import_handler(){
		if ( !isset($_POST['action']) ){
			return;
		}
		
		error_log(print_r($_POST, true));
		
		switch ( $_POST['action'] ){
			case 'import_filters':
				$this->import_filters();
				break;
			case 'update':
				$this->update_courses();
				break;
			case 'create':
				$this->create_courses();
				break;
		}
	}
	
	/**
	 * Import taxonomy terms from downloaded filters.json data
	 * 
	 * @since 0.1.0
	 */
	public function import_filters(){
		if ( empty($_FILES) ){
			return;
		}
		
		// Test whether the request includes a valid nonce
		check_admin_referer('sm_import_filters', 'sm_import_filters_nonce');
		
		$filter_name = sanitize_text_field( $_POST['import-name'] );
		$uploaded_file = $_FILES['import-filter-file'];
		
		/** Include admin functions to get access to wp_handle_upload() */
    	require_once ABSPATH . 'wp-admin/includes/admin.php';
		
		$file = wp_handle_upload( $uploaded_file, array('test_form' => false,'mimes' => array('json' => 'application/json')));
		
		if ( isset($file['error']) ){
			return new WP_Error( 'import_filter_upload_error', __( 'File upload error.' ), array( 'status' => 400 ) );
		}
		
		/**
		 * Save the uploaded file to the media library temporarily
		 */
		$file_args = array(
			'post_title' => sanitize_file_name($file['file']),
			'post_content' => $file['url'],
			'post_mime_type' => $file['type'],
			'guid' => $file['url'],
			'context' => 'import',
			'post_status' => 'private'
		);
		$file_id = wp_insert_attachment( $file_args, $file['file'] );
		
		// Process the selected array and insert terms
		if ( false !== ( $json = file_get_contents( $file['file'] ) ) ){
			$filter_data = json_decode( $json );
			$filter_data = $filter_data->{$filter_name};
			
			$taxonomies = array(
				'terms' => 'syllabus_semester',
				'departments' => 'syllabus_department',
				'progLevels' => 'syllabus_level',
			);
			
			foreach ( $filter_data as $data ){
				$slug = $data->CODE;
				$term = $desc = $data->DESC;
				
				// Change Department names to title case
				if ( 'departments' == $filter_name ){
					$ugly_terms = explode( '-', $term );
					$pretty_terms = array();
					foreach( $ugly_terms as $ugly_title ){
						//$pretty_terms[] = ucwords(strtolower(trim($ugly_title)), " -\t\r\n\f\v/");
						$pretty_terms[] = ucwords(strtolower(trim($ugly_title)));
					}
					$term = implode('-', $pretty_terms);
					$term = str_replace('Languages Lit/culture', 'Languages, Literatures, & Cultures', $term);
				}
				
				wp_insert_term( $term, $taxonomies[$filter_name], array('slug' => $slug, 'description' => $desc ));
			}
		}
		
		// Clean up files
		wp_delete_attachment( $file_id );
	}
	
	/**
	 * Updates WP course post attributes from the external source data
	 * 
	 * @since 0.1.0
	 */
	public function update_courses(){
		// Test whether the request includes a valid nonce
		check_admin_referer('sm_update_courses', 'sm_update_courses_nonce');
		
		if ( WP_DEBUG ) {error_log('Updating courses...'); }
		
		$semester = $_POST['semester'];
		$department = $_POST['department'];
		$level = $_POST['level'];
		
		// Get the courses
		$course_data = Syllabus_Manager_Course::get_courses( $semester, $department, $level );
		
		$matched_courses = $this->get_matched_courses( $semester, $department, $level, $course_data );
		if ( WP_DEBUG ){ error_log( print_r($matched_courses, true) ); }
		
		foreach ( $matched_courses as $section_id => $post_id ){
			
			$section = new Syllabus_Manager_Section( 
				$course_data[$section_id]['code'],
				$course_data[$section_id]['title'],
				$course_data[$section_id]['section_number'],
				$semester,
				array($department),
				$level,
				explode(', ', $course_data[$section_id]['instructors']),
				$post_id
			);
						
			$post_id = wp_update_post( $section->get_post_args() );
			
			if ( !is_wp_error($post_id) ){
				error_log( 'Successfully updated post: ' . $post_id . ' for ' . $section_id );
				update_post_meta( $post_id, 'sm_course_code', $course_data[$section_id]['code'] );
				update_post_meta( $post_id, 'sm_course_title', $course_data[$section_id]['title'] );
				update_post_meta( $post_id, 'sm_section_number', $course_data[$section_id]['section_number'] );
			}
			
		}
	}
	
	/**
	 * Compares WP course posts with courses in the data tables
	 * 
	 * @param  string $semester      
	 * @param  string $department     
	 * @param  string $level           
	 * @param  array $new_course_data Array of Syllabus_Manager_Course objects
	 * @return array Array of Syllabus_Manager_Courses that exist in the external table data
	 * @since 0.1.0
	 */
	public function get_matched_courses( $semester, $department, $level, $new_course_data ){
		$current_courses = array();
		
		// Get target course ids
		$current_query = new WP_Query( array(
			'post_type' => 'syllabus_course',
			'posts_per_page' => -1,
			'tax_query' => array(
				array(
					'taxonomy' => 'syllabus_semester',
					'field' => 'slug',
					'terms' => $semester,
				),
				array(
					'taxonomy' => 'syllabus_department',
					'field' => 'slug',
					'terms' => $department,
				),
				array(
					'taxonomy' => 'syllabus_level',
					'field' => 'slug',
					'terms' => $level,
				)
			),
		));
		$current_course_posts = $current_query->get_posts();
		
		// Add meta values to the array
		foreach( $current_course_posts as $post ){
			$current_courses[$post->sm_section_id] = $post->ID;
		}
		
		// Get the list of course posts that exist in the course data
		return array_intersect_key($current_courses, $new_course_data);
	}
	
	/**
	 * Inserts a new WP course post into the database
	 * 
	 * @since 0.1.0
	 */
	public function create_courses(){
		// Test whether the request includes a valid nonce
		check_admin_referer('sm_create_courses', 'sm_create_courses_nonce');
		
		if ( !current_user_can( 'manage_options' ) ) {
			wp_die( __('You do not have sufficient permissions to access this page.', 'syllabus_manager'));
		}
		
		if ( WP_DEBUG ) {error_log('Creating courses...'); }
		
		$semester = $_POST['semester'];
		$department = $_POST['department'];
		$level = $_POST['level'];
		
		// Get the courses
		$course_data = Syllabus_Manager_Course::get_courses( $semester, $department, $level );
		
		foreach ( $course_data as $section_id => $course_section ){
			$section = $course_section->sections[0];
			
			//error_log( print_r($section->get_post_args(), true) );
			
			// Insert the post into the database
			
			$post_id = wp_insert_post( $section->get_post_args() );
			
			if ( !is_wp_error( $post_id ) ){
				error_log( 'Successfully inserted post: ' . $post_id . ' for ' . $section_id );
				add_post_meta( $post_id, 'sm_course_code', $section->course_code );
				add_post_meta( $post_id, 'sm_course_title', $section->course_title );
				add_post_meta( $post_id, 'sm_section_number', $section->section_code );
			}
			else {
				error_log( 'Error inserting post: ' . print_r($post_id, true) );
			}
		}
	}
	
	
	/**
	 * Add support for uploading file formats
	 * 
	 * @param array $existing_mimes
	 * @return array $existing_mimes
	 * @since 1.0
	 */
	function custom_upload_mimes( $existing_mimes = array() ) {
		// Add file extension 'extension' with mime type 'mime/type'
		$existing_mimes['svg'] = 'image/svg+xml';
		$existing_mimes['json'] = 'application/json'; 

		// and return the new full result
		return $existing_mimes;
	}
}
