<?php
/**
 * @package      CMB2\Field_Ajax_Search
 * @author       Tsunoa
 * @copyright    Copyright (c) Tsunoa
 *
 * License: GPLv2+
 */
// This plugin is based on CMB2 Field Type: Post Search Ajax (https://github.com/alexis-magina/cmb2-field-post-search-ajax)
// Special thanks to Magina (http://magina.fr/) for him awesome work
if ( ! class_exists( 'CMB2_Field_Ajax_Search' ) ) {
	/**
	 * Class CMB2_Field_Ajax_Search
	 */
	class CMB2_Field_Ajax_Search {
		/**
		 * Current version number
		 */
		const VERSION = '1.0.2';

		private $wp_language;

		/**
		 * Initialize the plugin by hooking into CMB2
		 */
		public function __construct() {

			$this->wp_language = strtoupper( substr(get_locale(), 0, 2) );

			add_action( 'admin_enqueue_scripts', array( $this, 'setup_admin_scripts' ) );

			// Render
			add_action( 'cmb2_render_thron_tags_ajax_search', array( $this, 'render' ), 10, 5 );
			add_action( 'cmb2_render_thron_folders_ajax_search', array( $this, 'render' ), 10, 5 );

			// Display
			add_filter( 'cmb2_pre_field_display_thron_tags_ajax_search', array( $this, 'display' ), 10, 3 );
			add_filter( 'cmb2_pre_field_display_thron_folders_ajax_search', array( $this, 'display' ), 10, 3 );

			// Sanitize
			add_action( 'cmb2_sanitize_thron_tags_ajax_search', array( $this, 'sanitize' ), 10, 4 );
			add_action( 'cmb2_sanitize_thron_folders_ajax_search', array( $this, 'sanitize' ), 10, 4 );

			// Ajax request
			add_action( 'wp_ajax_cmb_ajax_search_get_results', array( $this, 'get_results' ) );
		}

		/**
		 * Render field
		 */
		public function render( $field, $value, $object_id, $object_type, $field_type ) {

			$field_name = $field->_name();

			$default_limit = 1;
			// Current filter is cmb2_render_{$object_to_search}_ajax_search ( post, user or term )
			$object_to_search = str_replace( 'cmb2_render_', '', str_replace( '_ajax_search', '', current_filter() ) );

			if ( $field->args( 'multiple' ) == true ) {
				$default_limit = - 1; // 0 or -1 means unlimited
				?>
				<ul id="<?php echo $field_name; ?>_results"
				    class="cmb-ajax-search-results cmb-<?php echo $object_to_search; ?>-ajax-search-results"><?php
				if ( isset( $value ) && ! empty( $value ) ) {
					if ( ! is_array( $value ) ) {
						$value = array( $value );
					}
					foreach ( $value as $val ) :

						?>
						<li>
							<input type="hidden" name="<?php echo $field_name; ?>[]" value="<?php echo $val; ?>">
							<?php list( $classificationID, $tagID ) = explode( ';', $val ) ?>
							<?= $this->tagNameByID( $classificationID, $tagID ); ?>
							<a class="remover"><span class="dashicons dashicons-no"></span><span
									class="dashicons dashicons-dismiss"></span></a>
						</li>
					<?php
					endforeach;
				}
				?></ul><?php
				$input_value = '';
			} else {
				if ( is_array( $value ) ) {
					$value = $value[0];
				}

				/**
				 * Se non è salvata nessuna folder utilizza la root folder
				 */
				if (! $value) {
				    update_option( 'thron_list_folder',  get_option( 'thron_rootCategoryId' ) );
					$value = get_option('thron_list_folder');
                }

				$input_value = $this->folderNameByID( $value );

				 echo $field_type->input( array(
					'type'  => 'hidden',
					'name'  => $field_name,
					'value' => $value,
					'desc'  => false
				) );
			}

			echo $field_type->input( array(
				'type'             => 'text',
				'name'             => $field_name . '_input',
				'id'               => $field_name . '_input',
				'class'            => 'cmb-ajax-search cmb-' . $object_to_search . '-ajax-search',
				'value'            => $input_value,
				'desc'             => false,
				'data-multiple'    => $field->args( 'multiple' ) ? $field->args( 'multiple' ) : '0',
				'data-limit'       => $field->args( 'limit' ) ? $field->args( 'limit' ) : $default_limit,
				'data-sortable'    => $field->args( 'sortable' ) ? $field->args( 'sortable' ) : '0',
				'data-object-type' => $object_to_search,
				'data-query-args'  => $field->args( 'query_args' ) ? htmlspecialchars( json_encode( $field->args( 'query_args' ) ), ENT_QUOTES, 'UTF-8' ) : ''
			) );
			echo '<img src="' . admin_url( 'images/spinner.gif' ) . '" class="cmb-ajax-search-spinner" />';
			$field_type->_desc( true, true );
		}

		/**
		 * Display field
		 */
		public function display( $pre_output, $field, $display ) {
			$object_type = str_replace( 'cmb2_pre_field_display_', '', str_replace( '_ajax_search', '', current_filter() ) );
			ob_start();
			$field->peform_param_callback( 'before_display_wrap' );
			printf( "<div class=\"cmb-column %s\" data-fieldtype=\"%s\">\n", $field->row_classes( 'display' ), $field->type() );
			$field->peform_param_callback( 'before_display' );
			if ( is_array( $field->value ) ) : ?>
				<?php foreach ( $field->value as $value ) : ?>
					<?php list( $classificationID, $tagID ) = explode( ';', $value ) ?>
					<?php echo $this->tagNameByID( $classificationID, $tagID ); ?>
				<?php endforeach; ?>
			<?php else : ?>
				<?php list( $classificationID, $tagID ) = explode( ';', $field->value ) ?>
				<?php echo $this->tagNameByID( $classificationID, $tagID ); ?>
			<?php endif;
			$field->peform_param_callback( 'after_display' );
			echo "\n</div>";
			$field->peform_param_callback( 'after_display_wrap' );
			$pre_output = ob_get_clean();

			return $pre_output;
		}

		/**
		 * Optionally save the latitude/longitude values into two custom fields
		 */
		public function sanitize( $override_value, $value, $object_id, $field_args ) {

			return $value;
		}

		/**
		 * Enqueue scripts and styles
		 */
		public function setup_admin_scripts() {
			wp_register_script( 'jquery-autocomplete-ajax-search', plugins_url( 'js/jquery.autocomplete.min.js', __FILE__ ), array( 'jquery' ), self::VERSION, true );
			wp_register_script( 'cmb-ajax-search', plugins_url( 'js/ajax-search.js', __FILE__ ), array(
				'jquery',
				'jquery-autocomplete-ajax-search',
				'jquery-ui-sortable'
			), self::VERSION, true );
			wp_localize_script( 'cmb-ajax-search', 'cmb_ajax_search', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'cmb_ajax_search_get_results' ),
				'options' => apply_filters( 'cmb_field_ajax_search_autocomplete_options', array() )
			) );
			wp_enqueue_script( 'cmb-ajax-search' );
			wp_enqueue_style( 'cmb-ajax-search', plugins_url( 'css/ajax-search.css', __FILE__ ), array(), self::VERSION );
		}

		/**
		 * Ajax request : get results
		 */
		public function get_results() {
			$nonce = $_POST['nonce'];
			if ( ! wp_verify_nonce( $nonce, 'cmb_ajax_search_get_results' ) ) {
				// Wrong nonce
				die( json_encode( array(
					'error' => __( 'Error : Unauthorized action' )
				) ) );
			} else if ( ( ! isset( $_POST['field_id'] ) || empty( $_POST['field_id'] ) )
			            || ( ! isset( $_POST['object_type'] ) || empty( $_POST['object_type'] ) ) ) {
				// Wrong request parameters (field_id and object_type are mandatory)
				die( json_encode( array(
					'error' => __( 'Error : Wrong request parameters' )
				) ) );
			} else {
				$query_args = json_decode( stripslashes( htmlspecialchars_decode( $_POST['query_args'] ) ), true );
				$data       = array();
				$results    = array();

				$thron_options = get_option( 'thron_option_api_page' );

				if (!is_array($thron_options ))
					return;

				$clientId = $thron_options['thron_clientId'];
				$appId    = $thron_options['thron_appId'];
				$appKey   = $thron_options['thron_appKey'];

				$thron_api = new ThronAPI( $appId, $clientId, $appKey );

				switch ( $_POST['object_type'] ) {
					case 'thron_tags':
						$search = sanitize_text_field($_POST['query']);

						/**
						 * Carico i TAG da Thron
						 */
						$results = $thron_api->getListItag( $search );

						foreach ( $results as $classification => $tags ) {
							list( $name, $id ) = explode( ';', $classification );

							foreach ( $tags as $tag ) {

								/**
								 * Se la trova seleziona la lingua di default dell'utente
								 */
								$language = $tag->names[0];
								foreach ($tag->names as $locale ) {
									if ('EN' == $locale->lang)
										$language = $locale;
								}
								foreach ($tag->names as $locale ) {
									if ($this->wp_language == $locale->lang)
										$language = $locale;
								}

								$data[] = array(
									'id'    => $id . ';' . $tag->id,
									'value' => '[' . $name . '] ' . $language->label . ' (' . $tag->subNodes . ')'
								);

							}
						}

						break;
					case 'thron_folders':
						$search = sanitize_text_field($_POST['query']);

						/**
						 * Carico i TAG da Thron
						 */
						$results = $thron_api->get_folder( 0, $search );

						foreach ( $results->categories as $category ) {

							/**
							 * Se la trova seleziona la lingua di default dell'utente
							 */
							//print_r($category->category->locales);

							$language = $category->category->locales[0];
							foreach ($category->category->locales as $locale ) {
								if ('EN' == $locale->locale)
									$language = $locale;
							}
							foreach ($category->category->locales as $locale ) {
								if ($this->wp_language == $locale->locale)
									$language = $locale;
							}

							$data[] = array(
								'id'    => $category->category->id,
								'value' => $language->name,
                                'lang' => $language->locale
							);
						}

						break;
				}

				wp_send_json( $data );
				exit;
			}
		}

		private function tagNameByID( $classificationID, $tagID ) {
			/**
			 * Carico i TAG da Thron
			 */
			$thron_options = get_option( 'thron_option_api_page' );

			$clientId = $thron_options['thron_clientId'];
			$appId    = $thron_options['thron_appId'];
			$appKey   = $thron_options['thron_appKey'];

			$thron_api = new ThronAPI( $appId, $clientId, $appKey );

			$results = $thron_api->getTagByID( $classificationID, $tagID );

			/**
			 * Se la trova seleziona la lingua di default dell'utente
			 */
			$language = $results->names[0];
			foreach ($results->names as $locale ) {
				if ('EN' == $locale->lang)
					$language = $locale;
			}	
			foreach ($results->names as $locale ) {
				if ($this->wp_language == $locale->lang)
					$language = $locale;
			}

			return $language->label . ' (' . $results->subNodes . ')';
		}

		private function folderNameByID( $folderID ) {
			/**
			 * Carico i TAG da Thron
			 */

			$thron_options = get_option( 'thron_option_api_page' );

			if (!is_array($thron_options ))
				return;

			$clientId = $thron_options['thron_clientId'];
			$appId    = $thron_options['thron_appId'];
			$appKey   = $thron_options['thron_appKey'];

			$thron_api = new ThronAPI( $appId, $clientId, $appKey );

			$results = $thron_api->folderNameByID( $folderID );

			/**
			 * Se la trova seleziona la lingua di default dell'utente
			 */
			$language = $results->locales[0];
			foreach ($results->locales as $locale ) {
				if ('EN' == $locale->locale)
					$language = $locale;
			}
			foreach ($results->locales as $locale ) {
				if ($this->wp_language == $locale->locale)
					$language = $locale;
			}

			return $language->name;
		}

	}

	$cmb2_field_ajax_search = new CMB2_Field_Ajax_Search();
}