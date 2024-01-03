<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://#
 * @since      1.0.0
 *
 * @package    Wp_Blog_Exporter
 * @subpackage Wp_Blog_Exporter/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Blog_Exporter
 * @subpackage Wp_Blog_Exporter/admin
 * @author     Izhan <Izhan47@gmail.com>
 */
class Wp_Blog_Exporter_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Blog_Exporter_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Blog_Exporter_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-blog-exporter-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Blog_Exporter_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Blog_Exporter_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-blog-exporter-admin.js', array( 'jquery' ), $this->version, false );

	}
	/**
	 * Create custom posttye
	 */
	public function wpbe_create_pharmacies_posttype () {
		register_post_type( 'pharmacies',
			array(
				'labels' => array(
					'name' => __( 'Pharmacies' ),
					'singular_name' => __( 'Pharmacies' )
				),
				'public' => true,
				'has_archive' => true,
				'rewrite' => array('slug' => 'Pharmacy'),
				'show_in_rest' => true,
	  
			)
		);
	}

	/**
	 * Adds a submenu page under a custom post type parent.
	 */
	public function wpbe_api_logs_register_ref_page() {
		$hook = add_submenu_page(
			'edit.php?post_type=pharmacies',
			__( 'API Logs', 'wp-blog-exporter' ),
			__( 'API Logs', 'wp-blog-exporter' ),
			'manage_options',
			'wpbe-api-logs',
			array( $this, 'wpbe_api_logs_page_callback' )
		);
		// add_action( "load-$hook", $this->plugin_name , 'add_options' );
	}
	
	/**
	 * Display callback for the submenu page.
	 */
	public function wpbe_api_logs_page_callback() { 

		/**
		* Wp list table class
		*/
		$myListTable = include plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-blog-importer-extend-wp-list-tables.php';
		?>
		<div class="wrap">
			<h2>API Request Log</h2>
			<?php 
				global $wpdb;
				$table_name = $wpdb->prefix.'wpbe_api_log';
				
				if ( isset($_POST['action']) && $_POST['action'] == 'delete' && !empty($_POST['element']) ) :
					$record_id = $_POST['element'];
					$wpdb->delete( $table_name, array( 'id' => $id ) );
				endif;
			?>
			<form method="post">
			<?php 
				// Prepare table
				$myListTable->prepare_items();
				// Display table
				$myListTable->display();
			?>	
			</form>
		</div>
		<?php
	}
	/**
	 * Insert data in API log table
	 * wp_wpbe_api_log
	 */ 
	public function wpbe_insert_data_to_api_log_table( $post_id, $pharmacy_id, $description, $status ) {
		global $wpdb;
		$table_name = $wpdb->prefix.'wpbe_api_log';
		$url = get_site_url().'/wp-json/wp-blog-exporter/v1/copy-post-to/'.$post_id.'/'.$pharmacy_id.'/';
		$sql = $wpdb->prepare( "INSERT INTO ".$table_name." (name, status, description ) VALUES ( %s, %s, %s )", $url, $status, $description );
		$wpdb->query($sql);
		$id = $wpdb->insert_id;
		return $id;
	}

	/**
	 * Get data based on ID's
	 */
	public function wpbe_get_data_based_on_id( $obj_id, $obj_type  ) {

		if( $obj_type == 'pharmacy' ) {
			$pharmacy = get_posts(array(
				'post_type'     => 'pharmacies',
				'meta_key'      => 'application_id',
				'meta_value'    => $obj_id
			));
	
			if ( empty( $pharmacy ) ) {
				return new WP_Error( 'invalid_pharmacy', 'Invalid pharmacy.', array('status' => 404) );
			}
	
			$response = new WP_REST_Response($pharmacy);
			$response->set_status(200);

			return $response;
		}else{

			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => get_site_url().'/wp-json/wp/v2/posts/'.$obj_id,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'GET',
			));

			$response = curl_exec($curl);

			curl_close($curl);
		
			return $response;
		}
	}
	/**
	 * Publish featured image to the pharmacy
	 */
	public function wpbe_api_publish_featured_image_to_pharmacy ( $app_url, $basic_auth, $img_obj_id ) {

		$featured_media_arc = wp_get_attachment_url( $img_obj_id );
		$featured_file_contents = file_get_contents( $featured_media_arc );
		$featured_mime_type = wp_get_image_mime( $featured_media_arc );
		$filename = basename( get_attached_file( $img_obj_id ) );

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $app_url.'/wp-json/wp/v2/media',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => $featured_file_contents,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: '.$featured_mime_type,
				'Content-Disposition: attachment; filename='.$filename,
				'Authorization: Basic '.$basic_auth
			),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		$response = json_decode($response);

		return $response->id;
	}
	public function wpbe_api_publish_post_to_pharmacy( $post_id, $pharmacy_id ) {

		$pharmacy_response = $this->wpbe_get_data_based_on_id( $pharmacy_id, 'pharmacy' );
		$post_response = $this->wpbe_get_data_based_on_id( $post_id, 'post' );
		$post_data = json_decode($post_response);

		if( $pharmacy_response->errors ) {

			$status = $pharmacy_response->error_data['invalid_pharmacy']['status'];
			$description = $pharmacy_response->errors['invalid_pharmacy'][0];
			$this->wpbe_insert_data_to_api_log_table( $post_id, $pharmacy_id, $description, $status );
			echo json_encode($pharmacy_response);
			
		}else {
			$app_url = get_field( 'application_url', $pharmacy_response->data[0]->ID ); 
			$app_username = get_field( 'application_username', $pharmacy_response->data[0]->ID ); 
			$app_password = get_field( 'application_password', $pharmacy_response->data[0]->ID ); 
			$basic_auth = base64_encode( $app_username.':'.$app_password ); 
		}
		if( $post_data->code ) {

			$status = $post_data->data->status;
			$description = $post_data->message;
			$this->wpbe_insert_data_to_api_log_table( $post_id, $pharmacy_id, $description, $status );
			echo json_encode($post_data);

		}else {
			$post_title = $post_data->title->rendered;
			$post_content = $post_data->content->rendered;
			$featured_media = $post_data->featured_media;

			if( !empty( $basic_auth ) ):

				if( !empty( $featured_media ) ):
	
					$featured_media_id = $this->wpbe_api_publish_featured_image_to_pharmacy( $app_url, $basic_auth, $featured_media );
					$post_array = array('title' => $post_title, 'content' => $post_content, 'featured_media' => $featured_media_id,'status' => 'draft');
				else:
					$post_array = array('title' => $post_title, 'content' => $post_content,'status' => 'draft');	
				endif;
	
				$curl = curl_init();
	
				curl_setopt_array($curl, array(
					CURLOPT_URL => $app_url.'wp-json/wp/v2/posts',
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => '',
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => 0,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => 'POST',
					CURLOPT_POSTFIELDS => $post_array,
					CURLOPT_HTTPHEADER => array(
						'Authorization: Basic '.$basic_auth
					),
				));
	
				$response = curl_exec($curl);
	
				curl_close($curl);
				echo $response;
	
				$post_response = json_decode( $response );
	
				if( $post_response->code ) :
					$status = $post_response->data->status;
					$description = $post_response->message;
					$this->wpbe_insert_data_to_api_log_table( $post_id, $pharmacy_id, $description, $status );
				else:
					$status = '200';
					$description = 'Successfully Published.';
					$this->wpbe_insert_data_to_api_log_table( $post_id, $pharmacy_id, $description, $status );
				endif;
	
			else:
				return new WP_Error( 'invalid_credentials', 'Please check application credetials.', array('status' => 500) );
			endif;
		}
	}
	/**
	 * Register Custom Endpoint for post duplication
	 */
	public function wpbe_api_register_custom_endpoint () {
		register_rest_route( 'wp-blog-exporter/v1', '/copy-post-to/(?P<post_id>\d+)/(?P<pharmacy_id>\d+)', 
			array(
				'methods' => 'GET',
				'callback' => array( $this, 'wpbe_get_specific_post_data_endpoint' ),
				) 
		);
	}

	/**
	 * Custom endpoint callback
	 */
	public function wpbe_get_specific_post_data_endpoint( $data ) {

		$post_id = $data['post_id'];
		$pharmacy_id = $data['pharmacy_id'];

		if( !empty( $post_id ) || !empty( $pharmacy_id ) ):
			$published_post_data = $this->wpbe_api_publish_post_to_pharmacy( $post_id, $pharmacy_id );
		else:
			return new WP_Error( 'invalid_id', 'Please enter valid IDs.', array('status' => 404) );
		endif;

	  }

}
