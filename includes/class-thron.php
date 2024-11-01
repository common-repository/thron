<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.thron.com
 * @since      1.0.0
 *
 * @package    Thron
 * @subpackage Thron/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Thron
 * @subpackage Thron/includes
 * @author     THRON <integrations@thron.com>
 */
class Thron {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Thron_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'THRON_VERSION' ) ) {
			$this->version = THRON_VERSION;
		} else {
			$this->version = '1.3.3';
		}
		$this->plugin_name = 'thron';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Thron_Loader. Orchestrates the hooks of the plugin.
	 * - Thron_i18n. Defines internationalization functionality.
	 * - Thron_Admin. Defines all hooks for the admin area.
	 * - Thron_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-thron-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-thron-i18n.php';

		/**
		 * Framework per la creazione delle pagine delle opzioni
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/cmb2/init.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/cmb2-field-ajax-search/cmb2-field-ajax-search.php';

		/**
		 * Classe per automatizzare le chimamate alle API di Thron
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-thron-api.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-thron-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-thron-wp-rest-attachments-controller.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-thron-setting.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-thron-cron.php';



		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-thron-public.php';

		/**
		 * Utilità per il plugin
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/thron-helper.php';

		$this->loader = new Thron_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Thron_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Thron_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Thron_Admin( $this->get_plugin_name(), $this->get_version() );
		$plugin_wp_rest_attachments_controller = new THRON_WP_REST_Attachments_Controller('attachment');
		$plugin_setting = new Thron_Setting( );
		$plugin_cron = new Thron_Cron( );


		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		/**
		 * Update your files
		 */

		$this->loader->add_action( 'cron_schedules', $plugin_cron, 'thron_cron_schedules' );
		$this->loader->add_action( 'thron_update_file', $plugin_cron, 'thron_update_file' );
		// $this->loader->add_action( 'init', $plugin_cron, 'thron_update_file' );

		/**
		 * Configuration for the options page
		 */
		$this->loader->add_action( 'cmb2_admin_init', $plugin_setting, 'thron_option_page' );
		$this->loader->add_action( 'cmb2_admin_init', $plugin_setting, 'thron_option_page' );
		$this->loader->add_action( 'cmb2_save_options-page_fields_thron_option_api_page', $plugin_setting, 'after_save_thron_option_api_page' );
		$this->loader->add_action( 'cmb2_save_field', $plugin_setting, 'cmb2_save_field' );

		$this->loader->add_action( 'cmb2_render_password', $plugin_setting, 'cmb2_render_password', 10 , 5 );

		/**
		 * Adds the class for image tracking
		 */
		$this->loader->add_filter( 'image_send_to_editor', $plugin_admin, 'thron_image_send_to_editor', 10, 8 );


		//$this->loader->add_filter( 'media_send_to_editor', $plugin_admin, 'thron_add_tracking_class', 20, 3 );
		$this->loader->add_filter( 'wp_audio_shortcode_class', $plugin_admin, 'thron_add_tracking_class', 1, 1 );
		$this->loader->add_filter( 'wp_video_shortcode_class', $plugin_admin, 'thron_add_tracking_class', 1, 1 );

		/**
		 * Perform AJAX search in the media library
		 */
		if ( isset($_SERVER["HTTP_REFERER"]) && strpos($_SERVER["HTTP_REFERER"], 'upload.php') === false) {
			$this->loader->add_action( 'wp_ajax_query-attachments', $plugin_admin, 'thron_wp_ajax_query_attachments', 1  );
		} else {
			$this->loader->add_filter( 'ajax_query_attachments_args', $plugin_admin, 'thron_wp_ajax_query_attachments_upload', 1  );
		}


		/**
		 * Filter attachments to correct thumbnail URLs
		 */
		$this->loader->add_action( 'wp_prepare_attachment_for_js', $plugin_admin, 'wp_prepare_attachment_for_js', 1  );

		/**
		 * Custom metadata
		 *
		 * Add THRON ID
		 */
		$this->loader->add_filter( 'attachment_fields_to_edit', $plugin_admin, 'thron_add_attachment_field', 10, 2  );
		$this->loader->add_action( 'edit_attachment', $plugin_admin, 'thron_save_attachment', 10, 3  );
		
		/**
		 * Insert overriding media template and attachments operation
		 */
		$this->loader->add_action( 'wp_enqueue_media', $plugin_admin, 'ws_add_overrides', 10, 2 );
		$this->loader->add_action( 'rest_api_init', $plugin_wp_rest_attachments_controller, 'add_custom_media_edit_api', 10, 2);

		/**
		 * Upload translations for the media window
		 */
		$this->loader->add_action( 'media_view_strings', $plugin_admin, 'custom_media_string', 10, 2 );

		/**
		 * Create the endpoint for saving an attachment
		 */
		$this->loader->add_action( 'wp_ajax_thron_file_upload', $plugin_admin, 'wp_ajax_thron_file_upload', 10, 2 );

		/**
		 * Load the tag list
		 */
		$this->loader->add_action( 'wp_ajax_thron_list_tags', $plugin_admin, 'wp_ajax_thron_list_tags', 10, 2 );

		/**
		 * Correction of external resource URLs
		 */
		$this->loader->add_filter( 'wp_calculate_image_srcset', $plugin_admin, 'filter_wp_calculate_image_srcset', 10, 5 );

		/**
		 * Show alert message
		 */
		$this->loader->add_action( 'admin_notices', $plugin_setting, 'thron_admin_notices' );

		/**
		 * Block Gutenberg
		 */
		$this->loader->add_action( 'enqueue_block_editor_assets', $plugin_admin, 'THRONBlock' );
		$this->loader->add_action( 'init', $plugin_admin, 'register_block_embed' );


		$this->loader->add_filter( 'media_library_infinite_scrolling', $plugin_admin, 'infinite_scroll_true');

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Thron_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		/**
		 * Adds the shortcode to embed the player
		 */
		add_shortcode('thron', array($plugin_public, 'thron_shortcode'));

		$this->loader->add_filter( 'wp_get_attachment_image_src', $plugin_public, 'wp_get_attachment_image_src', 10, 4 );
		$this->loader->add_filter( 'wp_get_attachment_url', $plugin_public, 'wp_get_attachment_url', 10, 2 );

		$this->loader->add_filter( 'wp_get_attachment_image_attributes', $plugin_public, 'filter_wp_get_attachment_image_attributes', 10, 3 );


		$this->loader->add_filter( 'the_content' , $plugin_public, 'add_query_string_to_image_url', 10, 3 );

		/**
		 * Add class tci to image
		 */
		$this->loader->add_filter( 'get_image_tag_class', $plugin_public, 'get_image_tag_class', 10, 3 );
		$this->loader->add_filter( 'wp_get_attachment_image_attributes', $plugin_public, 'wp_get_attachment_image_attributes', 10, 3 );
		$this->loader->add_filter( 'render_block', $plugin_public, 'add_tci_class', 10, 2 );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Thron_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
