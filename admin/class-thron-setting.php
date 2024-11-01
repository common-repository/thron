<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @link       https://www.thron.com
 * @since      1.0.0
 *
 * @package    Thron
 * @subpackage Thron/admin
 * @author     THRON <integrations@thron.com>
 */
class Thron_Setting {


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

	}

	/**
	 * Configurazione della pagina delle opzioni
	 */
	function thron_option_page() {

		$main_options = new_cmb2_box( array(
			'id'           => 'thron_option_page',
			'title'        => esc_html__( 'THRON', 'thron' ),
			'object_types' => array( 'options-page' ),
			'option_key'   => 'thron_option_api_page',
			'icon_url'	   => plugin_dir_url( __FILE__ ) . 'img/icon.png',
		) );

		$main_options->add_field( array(
			'name' => __( 'App login', 'thron' ),
			'desc' => __( 'Log in with the THRON application logins', 'thron' ),
			'type' => 'title',
			'id'   => 'login_title'
		) );

		$main_options->add_field( array(
			'name' => __( 'Client ID', 'thron' ),
			'id'   => 'thron_clientId',
			'description' => __( 'Enter your domain', 'thron' ),
			'type' => 'text',
		) );

		$main_options->add_field( array(
			'name' => __( 'App ID', 'thron' ),
			'description' => __( 'Enter the connector identifier', 'thron' ),
			'id'   => 'thron_appId',
			'type' => 'text',
		) );

		$main_options->add_field( array(
			'name' => __( 'App Key', 'thron' ),
			'description' =>__( 'Enter the connector authentication key', 'thron' ),
			'id'   => 'thron_appKey',
			'type' => 'password',
		) );

		$main_options->add_field( array(
			'name' => __( 'Image settings', 'thron' ),
			'desc' => '',
			'type' => 'title',
			'id'   => 'image_title'
		) );

		$main_options->add_field( array(
			'name' => __( 'Default maximum width (px)', 'thron' ),
			'description' => __( 'Maximum default size for images imported from THRON', 'thron' ),
			'id'   => 'thron_maxImageWidth',
			'default' => 1920,
            'type' => 'text',
		) );

		$main_options->add_field( array(
			'name' => __( 'Quality', 'thron' ),
			'description' => __( 'Balance between image weight and resolution', 'thron' ),
			'id'   => 'thron_quality',
            'type' => 'select',
			'show_option_none' => true,
			'default'          => 'auto-high',
			'options'          => array(
				'auto-high' => __( 'Auto High', 'thron' ),
				'auto-medium'   => __( 'Auto Medium', 'thron' ),
				'auto-low'     => __( 'Auto Low', 'thron' ),
			),
		) );

		$pkey = get_option( 'thron_pkey' );

		/*
		$main_options->add_field( array(
			'name'       => __( 'pKey', 'thron' ),
			'id'         => 'thron_pKey',
			'type'       => 'text',
			'default'    => $pkey,
			'attributes' => array(
				'readonly' => 'readonly',
				'disabled' => 'disabled',
			)
		) );
		*/

		$attributes = array();

		/**
		 * The parameters to connect to the Thron API are loaded
		 */
		$thron_options = get_option( 'thron_option_api_page' );

		if (!is_array($thron_options ))
			return;

		$clientId = (isset($thron_options['thron_clientId'])) ? $thron_options['thron_clientId'] : '';
		$appId    = (isset($thron_options['thron_appId'])) ? $thron_options['thron_appId'] : '';
		$appKey   = (isset($thron_options['thron_appKey'])) ? $thron_options['thron_appKey'] : '' ;

		if ( (
			( $clientId == '' ) or
			( $appId == '' ) or
			( $appKey == '' )
		) or
		(get_option( 'thron_token_id' ) == null) ) {
			return;
		}

		$main_options->add_field( array(
			'name' => __( 'Search option', 'thron' ),
			'desc' => __( 'Choose whether to use tags as search filters and set the root folder of WordPress content', 'thron' ),
			'type' => 'title',
			'id'   => 'tag_and_folder_title'
		) );

		$main_options->add_field( array(
			'name'       => __( 'Enable filtering', 'thron' ),
			'id'         => 'thron_enable_features_search',
			'type'       => 'checkbox'
		) );

		$main_options->add_field( array(
			'name'       => __( 'Enable tag filtering', 'thron' ),
			'desc'       => __( 'Select the tags to be used for filtering', 'thron' ),
			'id'         => 'thron_list_tags',
			'type'       => 'thron_tags_ajax_search',
			'multiple'   => true,
			'limit'      => 10,
			'query_args' => array(
				'post_type'      => array( 'thron_tags' ),
				'posts_per_page' => - 1
			)
        ) );

		$main_options->add_field( array(
			'name'       => __( 'Select starting folder', 'thron' ),
			'desc'       => __( 'Select the starting folder for content selection (subfolders will be included)', 'thron' ),
			'id'         => 'thron_list_folder',
			'type'       => 'thron_folders_ajax_search',
			'multiple'   => false,
			'limit'      => 1,
			'query_args' => array(
				'post_type'      => array( 'thron_folder' ),
				'posts_per_page' => - 1
			)
        ) );

	}

	public function after_save_thron_option_api_page() {
		{
			$thron_options = get_option( 'thron_option_api_page' );


			$clientId = $thron_options['thron_clientId'];
			$appId    = $thron_options['thron_appId'];
			$appKey   = $thron_options['thron_appKey'];

			new ThronAPI( $appId, $clientId, $appKey );

		}
	}


	function thron_admin_notices() {

		$thron_options = get_option( 'thron_option_api_page' );

		if ( is_array( $thron_options ) ) {

			if (! array_key_exists('thron_maxImageWidth', $thron_options ) or $thron_options['thron_maxImageWidth'] == '' ) {
				$thron_options['thron_maxImageWidth'] = 1920;

				update_option( 'thron_option_api_page', $thron_options );
			}
			
		}

		$thron_options = get_option( 'thron_option_api_page' );

		if ( ! is_array( $thron_options ) ) {
			?>
            <div class="error notice">
                <p><?php _e( 'The connector has not been successfully configured!!', 'thron' ); ?></p>
            </div>
			<?php

			return;
		}


		$clientId = (isset($thron_options['thron_clientId'])) ? $thron_options['thron_clientId'] : '';
		$appId    = (isset($thron_options['thron_appId'])) ? $thron_options['thron_appId'] : '';
		$appKey   = (isset($thron_options['thron_appKey'])) ? $thron_options['thron_appKey'] : '' ;

		$pkey             = get_option( 'thron_pkey' );
		$tracking_context = get_option( 'tracking_context' );

		if (
			( $clientId == '' ) or
			( $appId == '' ) or
			( $appKey == '' )
		) {
			?>
            <div class="error notice">
                <p><?php _e( 'THRON has not been configured!', 'thron' ); ?></p>
            </div>
			<?php
		} else if ( get_option( 'thron_token_id' ) == null ) {
			?>
            <div class="error notice">
                <p><?php _e( 'THRON login failed!', 'thron' ); ?></p>
            </div>
			<?php
		} else if ( $pkey == '' ) {
			?>
            <div class="error notice">
                <p><?php _e( 'The application has not been configured correctly. Please contact technical support.', 'thron' ); ?></p>
            </div>
			<?php
		}

	}

	function cmb2_save_field() {
		update_option( 'thron_token_id_time', null );

	}

	function cmb2_render_password( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {

		echo '<input type="password" class="regular-text" name="' . $field->args['id'] . '" id="' . $field->args['id'] . '" value="' . $escaped_value . '" />
		<p class="cmb2-metabox-description">'.$field->args['description'].'</p>';
	}


}