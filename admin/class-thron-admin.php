<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @link       https://www.thron.com/
 * @since      1.0.0
 *
 * @package    Thron
 * @subpackage Thron/admin
 * @author     THRON <integrations@thron.com>
 */
class Thron_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	private $wp_language;

	private $clientId;

	private $tagsFilter;
	private $folderList;
	private $templateList;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->wp_language = strtoupper( substr( get_locale(), 0, 2 ) );

		$this->thron_options = get_option( 'thron_option_api_page' );

		$this->thron_list_folder = get_option( 'thron_list_folder' );

		/**
		 * The parameters to connect to the Thron API are loaded
		 */
		$thron_options = get_option( 'thron_option_api_page' );

		if ( ! is_array( $thron_options ) ) {
			return;
		}

		$this->clientId = $thron_options['thron_clientId'];

		$clientId = (isset($thron_options['thron_clientId'])) ? $thron_options['thron_clientId'] : '';
		$appId    = (isset($thron_options['thron_appId'])) ? $thron_options['thron_appId'] : '';
		$appKey   = (isset($thron_options['thron_appKey'])) ? $thron_options['thron_appKey'] : '' ;

		$thron_api = new ThronAPI( $appId, $clientId, $appKey );

		$tagsFilter = array();

		if ( array_key_exists( 'thron_list_tags', $thron_options ) and count( $thron_options['thron_list_tags'] ) > 0 and ( 'on' == $thron_options['thron_enable_features_search'] ) ) {

			foreach ( $thron_options['thron_list_tags'] as $tag ) {

				$tags = array();

				list( $classificationID, $tagID ) = explode( ';', $tag );

				$detail = $thron_api->getTagByID( $classificationID, $tagID );

				if ( is_object( $detail ) and $detail->subNodes > 0 ) {
					$i    = 0;
					$step = 40;

					do {

						$listSubTags = '';
						$subNodeIds  = $detail->subNodeIds;

						for ( $con = $i; $con < $i + min( $step, ( $detail->subNodes - $i ) ); $con ++ ) {
							$listSubTags .= $subNodeIds[ $con ] . ",";
						}
						$listSubTags = trim( $listSubTags, ',' );

						$getListItag = $thron_api->getListItag( '', $classificationID, $listSubTags );

						foreach ( $getListItag as $subTag ) {

							/**
							 * Se la trova seleziona la lingua di default dell'utente
							 */
							$language = $subTag->names[0];
							foreach ( $subTag->names as $locale ) {
								if ( 'EN' == $locale->lang ) {
									$language = $locale;
								}
							}
							foreach ( $subTag->names as $locale ) {
								if ( $this->wp_language == $locale->lang ) {
									$language = $locale;
								}
							}

							$tags[] = array(
								'name' => $language->label,
								'id'   => $subTag->id
							);
						}
						$i = $i + $step;
					} while ( $i < $detail->subNodes );

					$tagsFilter[] = array(
						'id'   => $tag,
						'name' => $detail->names[0]->label,
						'list' => $tags
					);
				}
			}
		}

		$this->tagsFilter = $tagsFilter;

		/**
		 * Vengono caricate tutte le cartelle
		 *
		 * Per evitare che il numero di cartelle da caricare sia troppo elevato
		 * e che questo rallenti tutto il sistema, è obbligtorio impostare una directory root
		 * nelle pzioni del plugin
		 */
		$folders = array();

		if ( isset($thron_options['thron_appKey']) && array_key_exists( 'thron_list_folder', $thron_options ) && $thron_options['thron_list_folder'] ) {
			$i = 0;

			do {
				$foldersList = $thron_api->get_folder( $i, '', $thron_options['thron_list_folder'] );

				$i = $i + 50;

				if ( isset($foldersList->categories) ) {
					$folders = array_merge( $folders, $foldersList->categories );
				}

			} while ( $i < $foldersList->totalResults );

			$new = array();
			if ( is_array( $folders ) ) {
				foreach ( $folders as $a ) {
					$id           = $a->category->upCategoryId ? $a->category->upCategoryId : 0;
					$new[ $id ][] = $a->category;
				}
			}

			/**
			 * Riempie la variabile $this->folderList con la gerarchia delle cartelle
			 */
			if ( array_key_exists( $thron_options['thron_list_folder'], $new ) ) {
				$this->createTree( $new, $new[ $thron_options['thron_list_folder'] ] );
			}
		}

		$i     = 0;
		$limit = 20;

		if(isset($thron_options['thron_appKey'])){

			do {
				$getTemplate = $thron_api->getTemplateList( $limit, $i );


				foreach ( $getTemplate->items as $templte ) {

					$this->templateList[] = $templte;
				}
				$i = $i + $limit;
			} while ( $i < $getTemplate->totalResults );

			$this->templateList[] = array(
				'createdBy' => 'thronPlugin',
				'createdDate'=> date(DATE_ISO8601),
				'name' => 'THRON Customer Experience 1.x',
				'values' => [],
				'id' => ''
			);
		}

	}


	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/thron-admin.css', array(), null, 'all' );
		wp_enqueue_style( 'thron-block', plugin_dir_url( __FILE__ ) . 'css/thron-block.css', array(), null, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 'wp-api' );
		wp_enqueue_media();

		/**
		 * The parameters to connect to the Thron API are loaded
		 */
		$thron_options = get_option( 'thron_option_api_page' );

		if ( ! is_array( $thron_options ) ) {
			return;
		}

		$clientId = (isset($thron_options['thron_clientId'])) ? $thron_options['thron_clientId'] : '';
		$appId    = (isset($thron_options['thron_appId'])) ? $thron_options['thron_appId'] : '';
		$appKey   = (isset($thron_options['thron_appKey'])) ? $thron_options['thron_appKey'] : '' ;

		wp_enqueue_script( 'thron-js', 'https://' . $thron_options['thron_clientId'] . '-cdn.thron.com/shared/lib/common/sdk/0.5.2/thron.js', array(), $this->version, true );


		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/thron-admin.js', array(
			'jquery',
			'media-views'
		), null, false );



		wp_enqueue_script(
			'thron-image-override',
			plugin_dir_url( __FILE__ ) . 'js/image-override.js',
			array(),
			true
		);



		/**
		 * Script che si occupa di modificare lo stato INSERT del media frame
		 */

		wp_register_script( 'wp-media-library', plugin_dir_url( __FILE__ ) . 'js/wp.media.library.js', array(
			'jquery',
			'media-views'
		), null, false );
		wp_localize_script( 'wp-media-library', 'myAjax',
			array(
				'ajaxurl'              => admin_url( 'admin-ajax.php' ),
				'thron_clientId'       => $clientId,
				'thron_appId'          => $appId,
				'thron_appKey'         => $appKey,
				'thron_pkey'           => get_option( 'thron_pkey' ),
				'thron_rootCategoryId' => get_option( 'thron_rootCategoryId' )
			) );
		wp_enqueue_script( 'wp-media-library' );

		$tagsFilter = $this->tagsFilter;

		/**
		 * Vengono aggiunti tanti filtri quanti sono i tag impostati nella pagina delle opzioni
		 */

		wp_enqueue_script( 'media-library-taxonomy-filter', plugin_dir_url( __FILE__ ) . '/js/collection-filter.js', array(
			'media-editor',
			'media-views'
		) );


		wp_localize_script( 'media-library-taxonomy-filter', 'ThronTagsList', array(
			'tags'    => $tagsFilter,
			'folders' => $this->folderList,
			'lang'    => array(
				'all'        => __( 'All', 'thron' ),
				'allFolders' => __( 'All folders', 'thron' )
			)
		) );


		/**
		 * Script che si occupa di aggiungere la scheda Thron nella media frame
		 */
		wp_enqueue_script( 'wp-media-thron', plugin_dir_url( __FILE__ ) . 'js/wp.media.thron-view.js', array(
			'jquery',
			'media-views'
		), false, true );

		wp_localize_script( 'wp-media-thron', 'myAjax',
			array(
				'ajaxurl'                      => admin_url( 'admin-ajax.php' ),
				'thron_clientId'               => (isset($thron_options['thron_clientId'])) ? $thron_options['thron_clientId'] : '',
				'thron_appId'                  => (isset($thron_options['thron_appId'])) ? $thron_options['thron_appId'] : '',
				'thron_appKey'                 => (isset($thron_options['thron_appKey'])) ? $thron_options['thron_appKey'] : '',
				'thron_pkey'                   => get_option( 'thron_pkey' ),
				'thron_playerTemplates'        => get_option( 'thron_playerTemplates' ),
				'thron_rootCategoryId'         => get_option( 'thron_rootCategoryId' ),
				'thron_enable_features_search' => ( array_key_exists( 'thron_enable_features_search', $thron_options )
				                                    and $thron_options['thron_enable_features_search'] ) == 'on' ? 'on' : 'off',
				'tags'                         => $tagsFilter,
				'folders'                      => $this->folderList,
				'allfolders'                   => __( 'All folders', 'thron' ),
				'allcontent'                   => __( 'All content', 'thron' ),
				'contentdetails'               => __( 'Content details', 'thron' ),
				'playertemplate'               => __( 'Player template', 'thron' ),
				'selecttemplate'               => __( '--Please select a template--', 'thron' ),
				'embedtype'                    => __( 'Embed type', 'thron' ),
				'fixedsize'                    => __( 'Fixed size', 'thron' ),
				'widthpx'                      => __( 'Width (px)', 'thron' ),
				'heightpx'                     => __( 'Height (px)', 'thron' ),
				'videos'						=> __( 'Videos', 'thron' ),
				'audio'							=> __( 'Audio', 'thron' ),
				'images'						=> __( 'Images', 'thron' ),
				'documents'						=> __( 'Document (Other)', 'thron' ),
				'playlist'						=> __( 'Playlist', 'thron' ),
				'url'							=> __( 'URL', 'thron' ),
				'pagelet'						=> __( 'Pagelet', 'thron' ),
			) );
		wp_enqueue_script( 'wp-media-thron' );
	}

	private function createTree( &$list, $parent, $sep = '' ) {
		$tree = array();
		if ( is_array( $parent ) ) {
			foreach ( $parent as $k => $l ) {

				/**
				 * Se la trova seleziona la lingua di default dell'utente
				 */
				$language = $l->locales[0];
				foreach ( $l->locales as $locale ) {
					if ( 'EN' == $locale->locale ) {
						$language = $locale;
					}
				}
				foreach ( $l->locales as $locale ) {
					if ( $this->wp_language == $locale->locale ) {
						$language = $locale;
					}
				}

				$this->folderList[] = array(
					'name' => $sep . ' ' . $language->name,
					'id'   => $l->id
				);

				if ( isset( $list[ $l->id ] ) ) {
					$sep         = $sep . '-';
					$l->children = $this->createTree( $list, $list[ $l->id ], $sep );
				}
				$tree[] = $l;
			}
		}

		return $tree;
	}

	function thron_wp_ajax_query_attachments_upload( $query ) {

		$request      = isset( $_REQUEST['query'] ) ? (array) $_REQUEST['query'] : array();
		$thron_source = isset( $request['thron_source'] ) ? $request['thron_source'] : '' ;

		if ( 'local' == $thron_source ) {
			$query['meta_query'] = array(
				array(
					'key'     => 'thron_id',
					'compare' => 'NOT EXISTS'
				)
			);
		} else {
			$query['meta_query'] = array(
				array(
					'key'     => 'thron_id',
					'compare' => 'EXISTS'
				)
			);
		}

		return $query;
	}

	/**
	 * Carica i risultati nella finestra dei media
	 */
	function thron_wp_ajax_query_attachments( $response ) {

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error();
		}

		/**
		 * Esco se il post__in fa riferimento ad un post in particolare
		 */
		$post_in = isset( $_REQUEST['query']['post__in'] ) ? $_REQUEST['query']['post__in'] : array() ;

		if (  count( (array)$post_in ) > 0 ) {
			return;
		}

		$query = isset( $_REQUEST['query'] ) ? (array) $_REQUEST['query'] : array();


		$source = isset( $query['thron_source'] ) ? sanitize_text_field( $query['thron_source'] ) : null ;

		if ( 'local' == $source ) {

			$query = isset( $_REQUEST['query'] ) ? (array) $_REQUEST['query'] : array();
			$keys  = array(
				's',
				'order',
				'orderby',
				'posts_per_page',
				'paged',
				'post_mime_type',
				'post_parent',
				'author',
				'post__in',
				'post__not_in',
				'year',
				'monthnum',
				'meta_query'
			);


			foreach ( get_taxonomies_for_attachments( 'objects' ) as $t ) {
				if ( $t->query_var && isset( $query[ $t->query_var ] ) ) {
					$keys[] = $t->query_var;
				}
			}

			$query              = array_intersect_key( $query, array_flip( $keys ) );
			$query['post_type'] = 'attachment';

			if (
				MEDIA_TRASH &&
				! empty( $_REQUEST['query']['post_status'] ) &&
				'trash' === $_REQUEST['query']['post_status']
			) {
				$query['post_status'] = 'trash';
			} else {
				$query['post_status'] = 'inherit';
			}

			if ( current_user_can( get_post_type_object( 'attachment' )->cap->read_private_posts ) ) {
				$query['post_status'] .= ',private';
			}

			// Filter query clauses to include filenames.
			if ( isset( $query['s'] ) ) {
				add_filter( 'posts_clauses', '_filter_query_attachment_filenames' );
			}

			$query['meta_query'] = array(
				array(
					'key'     => 'thron_id',
					'compare' => 'NOT EXISTS'
				)
			);

			/**
			 * Filters the arguments passed to WP_Query during an Ajax
			 * call for querying attachments.
			 *
			 * @param array $query An array of query variables.
			 *
			 * @see WP_Query::parse_query()
			 *
			 * @since 3.7.0
			 *
			 */
			$query = apply_filters( 'ajax_query_attachments_args', $query );

			$query = new WP_Query( $query );

			$posts = array_map( 'wp_prepare_attachment_for_js', $query->posts );
			$posts = array_filter( $posts );

			wp_send_json_success( $posts );
			die;
		}

		/**
		 * Aggiorno i post Attachment
		 */
		$term       = isset( $query['s'] ) ? sanitize_text_field( $query['s'] ) : null ;
		$categories = isset( $query['thron_categories'] ) ? sanitize_text_field( $query['thron_categories'] ) : null ;
		$mime_type  = isset( $query['post_mime_type'] ) ? sanitize_text_field( $query['post_mime_type'] ) : null ;
		$tags       = null;
		$per_page   = sanitize_text_field( $query['posts_per_page'] );
		$paged      = sanitize_text_field( $query['paged'] );

		$mime_type = is_array( $mime_type ) ? $mime_type[0] : $mime_type;

		foreach ( $_REQUEST['query'] as $key => $value ) {
			
			if ( strpos( $key, 'thron_tags' ) !== false ) {
				list( $classification, $id ) = explode( ';', str_replace( "thron_tags_", "", $key ) );
				$tags .= $classification . ';' . sanitize_text_field( $value ) . ',';
			}
		}

		$tags = trim( $tags, ',' );

		$list = $this->thron_list_file( $term, $categories, $tags, $mime_type, $per_page, $paged );

		$posts = array_filter( $list );

		wp_send_json_success( $posts );
		die;
	}

	function infinite_scroll_true( $infinite ){
		
		$infinite = true;
		return $infinite;
	}

	/**
	 * Filter attachments to correct thumbnail URLs
	 */
	public function wp_prepare_attachment_for_js( $attachment ) {
		if ( ! is_array( $attachment ) ) {
			return $attachment;
		}

		$thron_id = get_post_meta( $attachment['id'], 'thron_id', true );

		if ( ! $thron_id ) {
			return $attachment;
		}

		if ( strpos( $attachment['mime'], 'image' ) === false ) {
			return $attachment;
		}

		$url      = get_post_meta( $attachment['id'], '_wp_attached_file', true );
		$filename = basename( $url );
		$path     = str_replace( $filename, '', $url );

		foreach ( $attachment['sizes'] as $size => $item ) {
			if ( 'full' != $size ) {
				$width  = $attachment['sizes'][ $size ]['width'];
				$height = $attachment['sizes'][ $size ]['height'];

				$attachment['sizes'][ $size ]['url'] = $path . $width . 'x' . $height . '/' . $filename;
			}
		}

		return $attachment;
	}

	/**
	 * Visualizza i metadata
	 *
	 * @param $form_fields
	 * @param $post
	 *
	 * @return mixed
	 */
	function thron_add_attachment_field( $form_fields, $post ) {

		$field_value = get_post_meta( $post->ID, 'thron_id', true );

		$form_fields['thron_id'] = array(
			'value' => $field_value ? $field_value : '',
			'label' => __( 'Content Identifier' ),
			'helps' => __( '' )
		);

		$screen = get_current_screen();

		if ( isset($screen->parent_base) && $screen->parent_base == 'upload' ) {
			$t_id = get_post_meta(get_the_ID(),'thron_id', true);
			if($t_id) {
				?>
				<script>
					jQuery(function(){

					var th_img = jQuery('.misc-pub-filename > strong').html().split('?')
					
					var th_info = th_img[0].split('.')
					jQuery('.misc-pub-filename > strong').html(th_img[0])
					jQuery('.misc-pub-filetype > strong').html(th_info[1].toUpperCase())


					jQuery('#imgedit-open-btn-<?=$post->ID?>').remove()
					jQuery('p span.spinner').remove()
					jQuery('#media-head-<?=$post->ID?>').append('<p><a type="button" href="https://<?=$this->thron_options['thron_clientId']?>.thron.com/#/contents/content/<?=$t_id?>" target="_blank" class="button button-hero thron-upload-button"><?=__( 'Edit image on THRON', 'thron' )?></a></p><p> <span class="spinner"></span></p>')
				})
				</script>
				<?php

			}
		}

		return $form_fields;
	}

	/**
	 * Save metadata fields
	 *
	 * @param $attachment_id
	 */
	function thron_save_attachment( $attachment_id ) {
		if ( isset( $_REQUEST['attachments'][ $attachment_id ]['thron_id'] ) ) {
			$thron_id = sanitize_text_field( $_REQUEST['attachments'][ $attachment_id ]['thron_id'] );
			update_post_meta( $attachment_id, 'thron_id', $thron_id );
		}
	}

	/**
	 * Restituisce la lista dei file che corrispondo alla ricerca
	 * e l visualizza nel media frame
	 *
	 * @param $term
	 * @param $categories
	 * @param $tag
	 * @param $mime_type
	 *
	 * @return mixed
	 */
	private function thron_list_file( $term, $categories, $tag, $mime_type, $per_page, $paged ) {

		global $wp_session;
		session_start();

		/**
		 * Carico i file da Thron
		 */
		$thron_options = get_option( 'thron_option_api_page' );

		$clientId = $thron_options['thron_clientId'];
		$appId    = $thron_options['thron_appId'];
		$appKey   = $thron_options['thron_appKey'];
		$pkey     = get_option( 'thron_pkey' );

		$pageToken = ( ( $paged > 1 ) and ( array_key_exists( 'pageToken', $_SESSION ) ) ) ? $_SESSION['pageToken'] : null;

		$list = array();

		$thron_api = new ThronAPI( $appId, $clientId, $appKey );

		$list_files = $thron_api->search( $term, $categories, $tag, $mime_type, $per_page, $pageToken );
	

		$_SESSION['pageToken'] = isset( $list_files->nextPageToken ) ? $list_files->nextPageToken : '' ;

		if ( is_array( $list_files->items ) and count( $list_files->items ) ) {
			foreach ( $list_files->items as $file ) {

				/**
				 * Se la trova seleziona la lingua di default dell'utente
				 */
				$language = $file->details->locales[0];

				foreach ( $file->details->locales as $locale ) {
					if ( isset($locale) && 'EN' == $locale->lang ) {
						$language = $locale;
					}
				}
				foreach ( $file->details->locales as $locale ) {
					if ( isset($locale) && $this->wp_language == $locale->lang ) {
						$language = $locale;
					}
				}

				$mime = thron_mime2ext( $file->details->source->extension, false );
				list ( $type, $submime ) = explode( '/', $mime );

				$thumbs = 'https://' . str_replace( '//', '', strtok( $file->thumbs[0]->url, '?' ) );

				$description = isset( $language->description ) ? $language->description : '' ;

				$generic_post = array(
					'id'          => $file->id,
					'author'      => get_current_user_id(),
					'alt'         => sanitize_text_field( $description ),
					'description' => $description,
					'guid'        => sanitize_title( $file->details->source->fileName ),
					'title'       => sanitize_text_field( $language->name ),
					'filename'    => $file->details->source->fileName,
					'mime'        => $mime,
					'subtype'     => $submime,
					'nonces'      => array(
						'update' => false,
						'delete' => false,
					),
					'editLink'    => false,
					'type'        => $type,
					'status'      => 'inherit',
					'icon'        => $thumbs
				);

				$specific_post = array();

				switch ( $file->contentType ) {
					case 'IMAGE':

						$thron_options = get_option( 'thron_option_api_page' );
						$width_default = ( array_key_exists( 'thron_maxImageWidth', $thron_options ) ) ? $thron_options['thron_maxImageWidth'] : '0';

						$file_url = 'https://' . $clientId . '-cdn.thron.com/delivery/public/image/' . $clientId . '/' . $file->id . '/' . $pkey . '/std/' . strval( $width_default ) . 'x0/' .  $file->details->source->fileName;

						$attachment_metadata          = array();
						$attachment_metadata ['full'] = array(
							'width'     => null,
							'height'    => null,
							'url'       => $file_url,
							'crop'      => false,
							'mime-type' => $mime
						);

						/**
						 * $image_sizes Lista di tutti i formati suportati dal tema e dai vari plugin installati
						 */
						$image_sizes = get_intermediate_image_sizes();

						/**
						 * Per ogni formato supportto gli faccio calcolare l'url dell'immagine
						 */
						foreach ( $image_sizes as $image_size ) {

							$width  = intval( get_option( "{$image_size}_size_w" ) );
							$height = intval( get_option( "{$image_size}_size_h" ) );

							if ( 'post-thumbnail' == $image_size ) {
								$width  = null;
								$height = null;
							}
							//print_r( $file->details);
							//die();
							//print_r( $file->detail->locales[0]->name);
							$url = 'https://' . $clientId . '-cdn.thron.com/delivery/public/image/' . $clientId . '/' . $file->id . '/' . $pkey . '/std/' . $width . 'x' . $height . '/' . $file->details->source->fileName;
							//$url = 'https://' . $clientId . '-cdn.thron.com/delivery/public/image/' . $clientId . '/' . $file->id . '/' . $pkey . '/std/' . $width . 'x' . $height . '/' . $file->details->locales[0]->name;

							//$url = 'https://' . $clientId . '-cdn.thron.com/api/xcontents/resources/delivery/getThumbnail/' . $clientId . '/' . $width . 'x' . $height . '/' . $file->id;

							$attachment_metadata[ $image_size ] = array(
								'width'     => $width,
								'height'    => $height,
								'url'       => $url,
								'crop'      => get_option( "{$image_size}_crop" ) ? get_option( "{$image_size}_crop" ) : false,
								'mime-type' => $mime
							);
						}

						$specific_post = array(
							'url'    => $file_url,
							'link'   => $file_url,
							'sizes'  => $attachment_metadata,
							'height' => null,
							'width'  => null,
						);
						break;

					case 'VIDEO':

						$channelType = thron_get_channel( $file->details->availableChannels );

						$file_url = 'https://' . $clientId . '-cdn.thron.com/delivery/public/video/' . $clientId . '/' . $file->id . '/' . $pkey . '/' . $channelType . '/' . $file->details->source->fileName;

						$specific_post = array(
							'url'      => $file_url,
							'link'     => $file_url,
							'filename' => $file->details->source->fileName
						);

						break;

					case 'AUDIO':

						$channelType = thron_get_channel( $file->details->availableChannels );

						$fileSRC = 'https://' . $clientId . '-cdn.thron.com/delivery/public/audio/' . $clientId . '/' . $file->id . '/' . $pkey . '/' . $channelType . '/' . $file->details->source->fileName;

						$specific_post = array(
							'fileLength'              => null,
							'fileLengthHumanReadable' => null,
							'filesizeHumanReadable'   => null,
							'meta'                    => array(
								'album'        => false,
								'artist'       => false,
								'bitrate'      => false,
								'bitrate_mode' => "cbr"
							),
							'filesizeInBytes'         => null,
							'caption'                 => "",
							'url'                     => $fileSRC,
							'link'                    => $fileSRC,
							'artist'                  => '',
							'filename'                => $file->details->source->fileName
						);
						break;

					default:

						$channelType = thron_get_channel( $file->details->availableChannels );

						$file_url = 'https://' . $clientId . '-cdn.thron.com/delivery/public/document/' . $clientId . '/' . $file->id . '/' . $pkey . '/' . $channelType . '/' . $file->details->source->fileName ;

						$specific_post = array(
							'url'  => $file_url,
							'link' => $file_url
						);

						break;
				}

				foreach ( $specific_post as $key => $val ) {
					$generic_post[ $key ] = $val;
				}

				$list[] = $generic_post;
			}
		}

		return $list;
	}

	function custom_media_string( $strings, $post ) {
		$strings['THRONMenuTitle']    = __( 'THRON Universal Player', 'thron' );
		$strings['THRONActionButton'] = __( 'Insert', 'thron' );

		return $strings;
	}

	/**
	 * Crica una lista di tag
	 */
	public function wp_ajax_thron_list_tags() {
		$results = array();

		/**
		 * Carico i TAG da Thron
		 */
		$thron_options = get_option( 'thron_option_api_page' );

		$clientId = $thron_options['thron_clientId'];
		$appId    = $thron_options['thron_appId'];
		$appKey   = $thron_options['thron_appKey'];
		$pkey     = $thron_options['thron_pkey'];

		$thron_api = new ThronAPI( $appId, $clientId, $appKey );

		$classifications = $thron_api->getListItag( sanitize_text_field( $_REQUEST['search'] ) );

		foreach ( $classifications as $classification => $tags ) {

			foreach ( $tags as $tag ) {

				$results['results'][] = array(
					'id'   => $tag->id,
					'text' => $classification . ' => ' . $tag->names[0]->label
				);
			}
		}

		wp_send_json( $results );
	}

	/**
	 * Save the reference of the Thron file in WordPress Media
	 */
	public function wp_ajax_thron_file_upload() {
		$thron_id = sanitize_text_field( $_REQUEST['thron_id'] );

		if ( is_numeric( $thron_id ) ) {
			$post = get_post( $thron_id );

			$post->url = wp_get_attachment_url( $post->ID );
			wp_send_json_success( $post );
			die;
		}

		$args  = array(
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'post_parent' => 0,
			'meta_query'  => array(
				array(
					'key'     => 'thron_id',
					'value'   => $thron_id,
					'compare' => '=',
				)				
			)
		);

		$query = new WP_Query( $args );
		$posts = $query->posts;


		if ( count( $query->posts ) > 0 ) {
			foreach ( $posts as $post ) {
				$post->url = wp_get_attachment_url( $post->ID );
				wp_send_json_success( $post );
				die;
			}
		}

		/**
		 * The detail of the file is loaded from Thron
		 */
		$thron_options = get_option( 'thron_option_api_page' );

		$clientId = $thron_options['thron_clientId'];
		$appId    = $thron_options['thron_appId'];
		$appKey   = $thron_options['thron_appKey'];
		$pkey     = get_option( 'thron_pkey' );

		$thron_api = new ThronAPI( $appId, $clientId, $appKey );

		$detail = $thron_api->get_content_detail( $thron_id );

		/**
		 * Se la trova seleziona la lingua di default dell'utente
		 */
		$language = $detail->content->locales[0];
		foreach ( $detail->content->locales as $locale ) {
			if ( isset($locale) && 'EN' == $locale->lang ) {
				$language = $locale;
			}
		}
		foreach ( $detail->content->locales as $locale ) {
			if ( isset($locale) && $this->wp_language == $locale->lang ) {
				$language = $locale;
			}
		}

		$attachment_metadata = null;
		$attached_file       = null;

		$mime = null;

		if ( is_array( $detail->content->metadatas ) ) {
			foreach ( $detail->content->metadatas as $metadata ) {
				switch ( $metadata->name ) {
					case '_SOURCE_MIMETYPE_':
						$mime = sanitize_mime_type( $metadata->value );

						break;
				}
			}
		}

		list ( $type, $submime ) = explode( '/', $mime );

		$file_name     = sanitize_title( $detail->content->locales[0]->name ) . '.' . thron_mime2ext( $mime );
		$specific_post = array();
		$attachment    = array(
			'alt'            => sanitize_text_field( $language->name ),
			'guid'           => sanitize_title( $thron_id ),
			'post_title'     => sanitize_text_field( $language->name ),
			'post_content'   => isset($language->description) ? $language->description : '',
			'filename'       => sanitize_file_name( $file_name ),
			'caption'        => "",
			'type'           => $type,
			'post_mime_type' => $mime,
			'subtype'        => $submime,
			'editLink'       => false,
			'status'         => 'inherit'
		);

		$channels = array_column( $detail->content->weebo->weeboChannels, 'channelType' );

		switch ( $detail->content->contentType ) {

			case 'IMAGE':

				$thron_options = get_option( 'thron_option_api_page' );
				$width_default = ( array_key_exists( 'thron_maxImageWidth', $thron_options ) ) ? $thron_options['thron_maxImageWidth'] : '0';

				$thumbs = 'https://' . str_replace( '//', '', strtok( $detail->thumbUrls[0], '?' ) );
				/**
				 * URL of the high resolution image.
				 */
				$attached_file      = 'https://' . $clientId . '-cdn.thron.com/delivery/public/image/' . $clientId . '/' . $thron_id . '/' . $pkey . '/std/' . $file_name;
				$attached_file_full = 'https://' . $clientId . '-cdn.thron.com/delivery/public/image/' . $clientId . '/' . $thron_id . '/' . $pkey . '/std/' . strval( $width_default ) . 'x0/' . $file_name;

				$size = getimagesize( $attached_file_full );


				list ( $type, $submime ) = explode( '/', $mime );

				$attachment_metadata = array(
					'width'  => $size[0],
					'height' => $size[1],
					'file'   => $attached_file_full
				);

				$attachment_metadata['sizes']['full'] = array(
					'width'     => $size[0],
					'height'    => $size[1],
					'file'      => strval( $width_default ) . 'x0/' . $file_name,
					'crop'      => false,
					'mime-type' => $mime
				);

				/**
				 * $image_sizes The url of the image is created for each supported format
				 */
				$image_sizes = get_intermediate_image_sizes();

				/**
				 * The url of the image is created for each supported format
				 */
				foreach ( $image_sizes as $image_size ) {
					$width  = intval( get_option( "{$image_size}_size_w" ) );
					$height = intval( get_option( "{$image_size}_size_h" ) );

					if ( $width or $height ) {
						if ( 'post-thumbnail' == $image_size ) {
							$width  = $size[0];
							$height = $size[1];
						}

						$file = '' . $width . 'x' . $height . '/' . $file_name;

						$attachment_metadata['sizes'][ $image_size ] = array(
							'width'     => $width,
							'height'    => $height,
							'file'      => $file,
							'crop'      => get_option( "{$image_size}_crop" ) ? get_option( "{$image_size}_crop" ) : false,
							'mime-type' => $mime
						);
					}
				}

				$specific_post = array(
					'url'            => $attached_file_full,
					'link'           => $attached_file_full,
					'filename'       => $file_name,
					'height'         => $size[1],
					'width'          => $size[0],
					'icon'           => $thumbs,
					'type'           => $type,
					'post_mime_type' => $mime,
					'subtype'        => $submime,
				);
				break;

			case 'VIDEO':

				$channelType = thron_get_channel( $channels );

				$thumbs = 'https://' . str_replace( '//', '', strtok( $detail->thumbs[0]->url, '?' ) );

				$attached_file = 'https://' . $clientId . '-cdn.thron.com/delivery/public/video/' . $clientId . '/' . $thron_id . '/' . $pkey . '/' . $channelType . '/' . $file_name;

				$specific_post = array(
					'url'  => $attached_file,
					'link' => $attached_file,
					'icon' => $thumbs
				);

				break;
			case 'AUDIO':

				$channelType = thron_get_channel( $channels );

				$thumbs = 'https://' . str_replace( '//', '', strtok( $detail->thumbs[0]->url, '?' ) );

				$attached_file = 'https://' . $clientId . '-cdn.thron.com/delivery/public/audio/' . $clientId . '/' . $thron_id . '/' . $pkey . '/' . $channelType . '/' . $file_name;

				$specific_post = array(
					'fileLength'              => null,
					'fileLengthHumanReadable' => null,
					'filesizeHumanReadable'   => null,
					'filesizeInBytes'         => null,
					'caption'                 => "",
					'url'                     => $attached_file,
					'link'                    => $attached_file,
					'icon'                    => $thumbs
				);

				$attachment_metadata = array(
					'album'        => false,
					'artist'       => false,
					'bitrate'      => false,
					'bitrate_mode' => "cbr"
				);

				break;
			case 'OTHER':

				$channelType = thron_get_channel( $channels );

				$thumbs = 'https://' . str_replace( '//', '', strtok( $detail->thumbs[0]->url, '?' ) );

				$attached_file = 'https://' . $clientId . '-cdn.thron.com/delivery/public/document/' . $clientId . '/' . $thron_id . '/' . $pkey . '/' . $channelType . '/' . $file_name;

				$specific_post = array(
					'url'  => $attached_file,
					'link' => $attached_file,
					'icon' => $thumbs
				);


				break;
		}

		foreach ( $specific_post as $key => $val ) {
			$attachment[ $key ] = $val;
		}

		if ( $attachment ) {

			$attach_id        = wp_insert_attachment( $attachment );
			$attachment['ID'] = $attach_id;

			wp_update_attachment_metadata( $attach_id, $attachment_metadata );

			if ( $attached_file ) {
				/**
				 * Il valore _wp_attached_file viene sottratto all'url del file
				 */
				update_post_meta( $attach_id, '_wp_attached_file', $attached_file );
			}

			update_post_meta( $attach_id, 'thron_id', $thron_id );

			wp_send_json_success( $attachment );
			die;
		}

		wp_send_json_error();
		die;
	}

	/**
	 * Corregge gli url
	 *
	 * @param $sources
	 * @param $size_array
	 * @param $image_src
	 * @param $image_meta
	 * @param $attachment_id
	 *
	 * @return array
	 */
	function filter_wp_calculate_image_srcset( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {

		$thron_id = get_post_meta( $attachment_id, 'thron_id', true );

		if ( ! $thron_id ) {
			return $sources;
		}

		$result = array();
		if ( is_array( $sources ) ) {
			foreach ( $sources as $source ) {
				$source['url'] = str_replace( site_url( 'wp-content/uploads/' ), '', $source['url'] );

				$result[] = $source;
			}
		}

		return $result;
	}

	/**
	 * THRON Block for Gutenberg
	 */
	function THRONBlock() {

		$templateList = $this->templateList;

		wp_enqueue_script( '', 'https://' . $this->clientId . '-cdn.thron.com/shared/ce/bootstrap/1/scripts/embeds-min.js' );

		wp_enqueue_script(
			'thron-block',
			plugin_dir_url( __FILE__ ) . 'js/thron-block.js',
			array( 'wp-blocks', 'wp-editor' ),
			true
		);

		wp_set_script_translations( 'thron-block', 'thron', THRON_PLUGIN_PATH . '/languages' );

		$template_default = count( $templateList ) > 0 ? $templateList[0]->id : null;

		wp_localize_script( 'thron-block', 'args',
			array(
				'thron_plugin_url'      => THRON_PLUGIN_URL,
				'clientId'              => $this->clientId,
				'thron_playerTemplates' => get_option( 'thron_playerTemplates' ) ? get_option( 'thron_playerTemplates' ) : $template_default,
				'pkey'                  => get_option( 'thron_pkey' ),
				'templateList'          => $templateList,
				'folders'               => $this->folderList,
				'tagsFilter'            => $this->tagsFilter,
				'icon'                  => plugin_dir_url( __FILE__ ) . 'img/icon.svg',
				'wp_language'           => $this->wp_language
			) );

	}


	function register_block_embed() {

		register_block_type(
			'thron/embed',
			array(
				'attributes'      => array(
					'contentID' => array(
						'type'    => 'string',
						'default' => false,
					),
					'embedCode' => array(
						'type'    => 'string',
						'default' => false,
					),
				),
				'render_callback' => array( $this, 'render_block_embed' ),
			)
		);
	}

	function thron_add_tracking_class( $class ){
		$class .= ' tci';
		return $class;
	}


	function thron_image_send_to_editor( $html, $id, $caption, $title, $align, $url, $size, $alt ) {
		$thron_id = get_post_meta( $id, 'thron_id', true );
		
		if ( ! $thron_id ) {
			return $html;
		}

		$html = preg_replace_callback( '/(<img.*class="([^"]+)"[^>]*>)/i', [ $this, 'process_image_class' ], $html );

		$dom = new DOMDocument();
		$dom->loadHTML( $html );
		$imgs = $dom->getElementsByTagName( "img" );

		foreach ( $imgs as $img ) {

			$thron_options = get_option( 'thron_option_api_page' );
			$width_default = ( array_key_exists( 'thron_maxImageWidth', $thron_options ) ) ? $thron_options['thron_maxImageWidth'] : '0';

			if ( is_array( $size ) ) {
				$width  = $size[0];
				$height = $size[1];
			} else {
				$width  = intval( get_option( "{$size}_size_w" ) );
				$height = intval( get_option( "{$size}_size_h" ) );
			}

			$width = $width == 0 ? $width_default : $width;

			$file     = get_post_meta( $id, '_wp_attached_file', true );
			$basename = basename( $file );
			$path     = str_replace( $basename, '', $file );
			$src      = $path . $width . 'x' . $height . '/' . $basename;

			$img->setAttribute( 'src', $src );

			$title = substr($basename, 0, strrpos($basename, "."));

			$img->setAttribute( 'title', $title );

			$html = $dom->saveHTML( $img );

			
		}
		
		return $html;
	}


	private function process_image_class( $matches ) {
		$matches[1] = str_replace( $matches[2], $matches[2] . " tci", $matches[1] );

		return $matches[1];
	}


	function render_block_embed( $attr ) {
		return do_shortcode( '[thron contentID="' . $attr['contentID'] . 
		'" scalemode= "' . $attr['scalemode'] . '"'.
		($attr['scalemode'] == 'manual' ? '" cropx= "' . $attr['cropx'] . '" cropy= "' . $attr['cropy'] . '" cropw= "' . $attr['cropw'] . '" croph= "' . $attr['croph'] . '"' : '').
		'" quality= "' . $attr['quality'] . '"'.
		'" brightness= "' . $attr['brightness'] . '"'.
		'" contrast= "' . $attr['contrast'] . '"'.
		'" sharpness= "' . $attr['sharpness'] . '"'.
		'" color= "' . $attr['color'] . '"'.
		'" embedCodeId= "' . $attr['embedCode'] . '"]' );
	}


	/**
	 * OVERRIDING TEMPLATE MEDIA MODAL
	 */
	public function ws_add_media_overrides($args) {
	?>
	<script type="text/html" id="tmpl-uploader-inline-custom">

		<# var messageClass = data.message ? 'has-upload-message' : 'no-upload-message'; #>
		<# if ( data.canClose ) { #>
		<button class="close dashicons dashicons-no"><span class="screen-reader-text"><?php _e( 'Close uploader' ); ?></span></button>
		<# } #>
		<div class="uploader-inline-content {{ messageClass }}">
		<# if ( data.message ) { #>
			<h2 class="upload-message">{{ data.message }}</h2>
		<# } #>
		<?php if ( ! _device_can_upload() ) : ?>
			<div class="upload-ui">
				<h2 class="upload-instructions"><?php _e( 'Your browser cannot upload files' ); ?></h2>
				<p>
				<?php
					printf(
						/* translators: %s: https://apps.wordpress.org/ */
						__( 'The web browser on your device cannot be used to upload files. You may be able to use the <a href="%s">native app for your device</a> instead.' ),
						'https://apps.wordpress.org/'
					);
				?>
				</p>
			</div>
		<?php elseif ( is_multisite() && ! is_upload_space_available() ) : ?>
			<div class="upload-ui">
				<h2 class="upload-instructions"><?php _e( 'Upload Limit Exceeded' ); ?></h2>
				<?php
				/** This action is documented in wp-admin/includes/media.php */
				do_action( 'upload_ui_over_quota' );
				?>
			</div>
		<?php else : ?>
			<div class="upload-ui">
				<h2 class="upload-instructions drop-instructions"><?php _e( 'Drop files to upload' ); ?></h2>
				<p class="upload-instructions drop-instructions"><?php _ex( 'or', 'Uploader: Drop files here - or - Select Files' ); ?></p>
				<button type="button" class="browser button button-hero"><?php _e( 'Select Files' ); ?></button>
			</div>

			<div class="upload-inline-status"></div>

			<div class="post-upload-ui">
				<?php
				/** This action is documented in wp-admin/includes/media.php */
				do_action( 'pre-upload-ui' ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
				/** This action is documented in wp-admin/includes/media.php */
				do_action( 'pre-plupload-upload-ui' ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

				if ( 10 === remove_action( 'post-plupload-upload-ui', 'media_upload_flash_bypass' ) ) {
					/** This action is documented in wp-admin/includes/media.php */
					do_action( 'post-plupload-upload-ui' ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
					add_action( 'post-plupload-upload-ui', 'media_upload_flash_bypass' );
				} else {
					/** This action is documented in wp-admin/includes/media.php */
					do_action( 'post-plupload-upload-ui' ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
				}

				$max_upload_size = wp_max_upload_size();
				if ( ! $max_upload_size ) {
					$max_upload_size = 0;
				}
				?>

				<p class="max-upload-size">
				<?php
					printf(
						/* translators: %s: Maximum allowed file size. */
						__( 'Maximum upload file size: %s.' ),
						esc_html( size_format( $max_upload_size ) )
					);
				?>
				</p>

				<br/>
				<p class="upload-instructions drop-instructions"><?php _ex( 'or', 'Uploader: Drop files here - or - Select Files' ); ?></p>
				<a type="button" href="https://<?=$this->thron_options['thron_clientId']?>.thron.com/#/contents/category/<?= $this->thron_options['thron_list_folder']; ?>" target="_blank" class="button button-hero thron-upload-button"><?=__( 'Upload the content in THRON' )?></a>

				<# if ( data.suggestedWidth && data.suggestedHeight ) { #>
					<p class="suggested-dimensions">
						<?php
							/* translators: 1: Suggested width number, 2: Suggested height number. */
							printf( __( 'Suggested image dimensions: %1$s by %2$s pixels.' ), '{{data.suggestedWidth}}', '{{data.suggestedHeight}}' );
						?>
					</p>
				<# } #>

				<?php
				/** This action is documented in wp-admin/includes/media.php */
				do_action( 'post-upload-ui' ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
				?>
			</div>
		<?php endif; ?>
		</div>
	</script>





	<script type="text/html" id="tmpl-attachment-details-two-column-custom">
		<div class="attachment-media-view {{ data.orientation }}">
			<h2 class="screen-reader-text"><?php _e( 'Attachment Preview' ); ?></h2>
			<div class="thumbnail thumbnail-{{ data.type }}">

				<?php //print_r($this->thron_options)
				
				/*print_r($args);	$thron_id =  get_post_meta($args['id'],'thron_id',true);*/ ?>
				
				<# var u = data.url.split('/')
				//console.log(u)
				if ( data.uploading ) { #>
					<div class="media-progress-bar"><div></div></div>
				<# } else if ( data.sizes && data.sizes.large ) { #>
					<img class="details-image" src="{{ data.sizes.large.url }}" draggable="false" alt="" />
				<# } else if ( data.sizes && data.sizes.full ) { #>
					<img class="details-image" src="{{ data.sizes.full.url }}" draggable="false" alt="" />
				<# } else if ( -1 === jQuery.inArray( data.type, [ 'audio', 'video' ] ) ) { #>
					<img class="details-image icon" src="{{ data.icon }}" draggable="false" alt="" />
				<# } #>

				<# if ( 'audio' === data.type ) { #>
				<div class="wp-media-wrapper wp-audio">
					<audio style="visibility: hidden" controls class="wp-audio-shortcode" width="100%" preload="none">
						<source type="{{ data.mime }}" src="{{ data.url }}"/>
					</audio>
				</div>
				<# } else if ( 'video' === data.type ) {
					var w_rule = '';
					if ( data.width ) {
						w_rule = 'width: ' + data.width + 'px;';
					} else if ( wp.media.view.settings.contentWidth ) {
						w_rule = 'width: ' + wp.media.view.settings.contentWidth + 'px;';
					}
				#>
				<div style="{{ w_rule }}" class="wp-media-wrapper wp-video">
					<video controls="controls" class="wp-video-shortcode" preload="metadata"
						<# if ( data.width ) { #>width="{{ data.width }}"<# } #>
						<# if ( data.height ) { #>height="{{ data.height }}"<# } #>
						<# if ( data.image && data.image.src !== data.icon ) { #>poster="{{ data.image.src }}"<# } #>>
						<source type="{{ data.mime }}" src="{{ data.url }}"/>
					</video>
				</div>
				<# } #>

				<div class="attachment-actions">
					<# if ( 'image' === data.type && ! data.uploading && data.sizes && data.can.save ) { #>
						<# var client_info = u[2].split('-');
						if(client_info[0] != '<?=$this->thron_options['thron_clientId']?>') { #>
					<button type="button" class="button edit-attachment"><?php _e( 'Edit Image' ); ?></button>
					<# }else{ #>
					<a type="button" href="https://<?=$this->thron_options['thron_clientId']?>.thron.com/#/contents/content/{{u[7]}}" target="_blank" class="button button-hero thron-upload-button"><?=__( 'Edit image on THRON', 'thron' )?></a>
					<# } #>
					<div>
						<?php 
						// print_r($this->thron_options);
						?>
					</div>
					<# } else if ( 'pdf' === data.subtype && data.sizes ) { #>
					<p><?php _e( 'Document Preview' ); ?></p>
					<# } #>
				</div>
			</div>
		</div>
		<div class="attachment-info">
			<span class="settings-save-status" role="status">
				<span class="spinner"></span>
				<span class="saved"><?php esc_html_e( 'Saved.' ); ?></span>
			</span>
			<div class="details">
				<h2 class="screen-reader-text"><?php _e( 'Details' ); ?></h2>
				<div class="uploaded"><strong><?php _e( 'Uploaded on:' ); ?></strong> {{ data.dateFormatted }}</div>
				<div class="uploaded-by">
					<strong><?php _e( 'Uploaded by:' ); ?></strong>
						<# if ( data.authorLink ) { #>
							<a href="{{ data.authorLink }}">{{ data.authorName }}</a>
						<# } else { #>
							{{ data.authorName }}
						<# } #>
				</div>
				<# if ( data.uploadedToTitle ) { #>
					<div class="uploaded-to">
						<strong><?php _e( 'Uploaded to:' ); ?></strong>
						<# if ( data.uploadedToLink ) { #>
							<a href="{{ data.uploadedToLink }}">{{ data.uploadedToTitle }}</a>
						<# } else { #>
							{{ data.uploadedToTitle }}
						<# } #>
					</div>
				<# } #>
				<div class="filename"><strong><?php _e( 'File name:' ); ?></strong> {{ data.filename }}</div>
				<div class="file-type"><strong><?php _e( 'File type:' ); ?></strong> {{ data.mime }}</div>
				<div class="file-size"><strong><?php _e( 'File size:' ); ?></strong> {{ data.filesizeHumanReadable }}</div>
				<# if ( 'image' === data.type && ! data.uploading ) { #>
					<# if ( data.width && data.height ) { #>
						<div class="dimensions"><strong><?php _e( 'Dimensions:' ); ?></strong>
							<?php
							/* translators: 1: A number of pixels wide, 2: A number of pixels tall. */
							printf( __( '%1$s by %2$s pixels' ), '{{ data.width }}', '{{ data.height }}' );
							?>
						</div>
					<# } #>

					<# if ( data.originalImageURL && data.originalImageName ) { #>
						<?php _e( 'Original image:' ); ?>
						<a href="{{ data.originalImageURL }}">{{data.originalImageName}}</a>
					<# } #>
				<# } #>

				<# if ( data.fileLength && data.fileLengthHumanReadable ) { #>
					<div class="file-length"><strong><?php _e( 'Length:' ); ?></strong>
						<span aria-hidden="true">{{ data.fileLength }}</span>
						<span class="screen-reader-text">{{ data.fileLengthHumanReadable }}</span>
					</div>
				<# } #>

				<# if ( 'audio' === data.type && data.meta.bitrate ) { #>
					<div class="bitrate">
						<strong><?php _e( 'Bitrate:' ); ?></strong> {{ Math.round( data.meta.bitrate / 1000 ) }}kb/s
						<# if ( data.meta.bitrate_mode ) { #>
						{{ ' ' + data.meta.bitrate_mode.toUpperCase() }}
						<# } #>
					</div>
				<# } #>

				<# if ( data.mediaStates ) { #>
					<div class="media-states"><strong><?php _e( 'Used as:' ); ?></strong> {{ data.mediaStates }}</div>
				<# } #>

				<div class="compat-meta">
					<# if ( data.compat && data.compat.meta ) { #>
						{{{ data.compat.meta }}}
					<# } #>
				</div>
			</div>

			<div class="settings">
				<# var maybeReadOnly = data.can.save || data.allowLocalEdits ? '' : 'readonly'; #>
				<# if ( 'image' === data.type ) { #>
					<span class="setting has-description" data-setting="alt">
						<label for="attachment-details-two-column-alt-text" class="name"><?php _e( 'Alternative Text' ); ?></label>
						<input type="text" id="attachment-details-two-column-alt-text" value="{{ data.alt }}" aria-describedby="alt-text-description" {{ maybeReadOnly }} />
					</span>
				<# } #>
				<?php if ( post_type_supports( 'attachment', 'title' ) ) : ?>
				<span class="setting" data-setting="title">
					<label for="attachment-details-two-column-title" class="name"><?php _e( 'Title' ); ?></label>
					<input type="text" id="attachment-details-two-column-title" value="{{ data.title }}" {{ maybeReadOnly }} />
				</span>
				<?php endif; ?>
				<# if ( 'audio' === data.type ) { #>
				<?php
				foreach ( array(
					'artist' => __( 'Artist' ),
					'album'  => __( 'Album' ),
				) as $key => $label ) :
					?>
				<span class="setting" data-setting="<?php echo esc_attr( $key ); ?>">
					<label for="attachment-details-two-column-<?php echo esc_attr( $key ); ?>" class="name"><?php echo $label; ?></label>
					<input type="text" id="attachment-details-two-column-<?php echo esc_attr( $key ); ?>" value="{{ data.<?php echo $key; ?> || data.meta.<?php echo $key; ?> || '' }}" />
				</span>
				<?php endforeach; ?>
				<# } #>
				<span class="setting" data-setting="caption">
					<label for="attachment-details-two-column-caption" class="name"><?php _e( 'Caption' ); ?></label>
					<textarea id="attachment-details-two-column-caption" {{ maybeReadOnly }}>{{ data.caption }}</textarea>
				</span>
				<span class="setting" data-setting="description">
					<label for="attachment-details-two-column-description" class="name"><?php _e( 'Description' ); ?></label>
					<textarea id="attachment-details-two-column-description" {{ maybeReadOnly }}>{{ data.description }}</textarea>
				</span>
				<span class="setting" data-setting="url">
					<label for="attachment-details-two-column-copy-link" class="name"><?php _e( 'File URL:' ); ?></label>
					<input type="text" class="attachment-details-copy-link" id="attachment-details-two-column-copy-link" value="{{ data.url }}" readonly />
					<span class="copy-to-clipboard-container">
						<button type="button" class="button button-small copy-attachment-url" data-clipboard-target="#attachment-details-two-column-copy-link"><?php _e( 'Copy URL to clipboard' ); ?></button>
						<span class="success hidden" aria-hidden="true"><?php _e( 'Copied!' ); ?></span>
					</span>
				</span>
				<div class="attachment-compat"></div>
			</div>

			<div class="actions">
				<# if ( data.link ) { #>
					<a class="view-attachment" href="{{ data.link }}"><?php _e( 'View attachment page' ); ?></a>
				<# } #>
				<# if ( data.can.save ) { #>
					<# if ( data.link ) { #>
						<span class="links-separator">|</span>
					<# } #>
					<a href="{{ data.editLink }}"><?php _e( 'Edit more details' ); ?></a>
				<# } #>
				<# if ( ! data.uploading && data.can.remove ) { #>
					<# if ( data.link || data.can.save ) { #>
						<span class="links-separator">|</span>
					<# } #>
					<?php if ( MEDIA_TRASH ) : ?>
						<# if ( 'trash' === data.status ) { #>
							<button type="button" class="button-link untrash-attachment"><?php _e( 'Restore from Trash' ); ?></button>
						<# } else { #>
							<button type="button" class="button-link trash-attachment"><?php _e( 'Move to Trash' ); ?></button>
						<# } #>
					<?php else : ?>
						<button type="button" class="button-link delete-attachment"><?php _e( 'Delete permanently' ); ?></button>
					<?php endif; ?>
				<# } #>
			</div>
		</div>
	</script>

    <script>
        jQuery(document).ready( function($) {

			/*
			* c.brualdi - 15.02
			*/

			var uploadUrl = "https://<?=$this->thron_options['thron_clientId']?>.thron.com/#/contents/category/<?= $this->thron_list_folder; ?>";

			// uploader in visualizzazione a griglia
			var inlineForm = jQuery('.uploader-inline-content.no-upload-message');
			if (inlineForm.length && !jQuery('#thron-upload').length) {
				inlineForm.after('<p id="thron-upload" class="upload-instructions drop-instructions"><?php _ex( 'or', 'Uploader: Drop files here - or - Select Files' ); ?></p><a type="button" href="' + uploadUrl + '" target="_blank" class="button button-hero thron-upload-button"><?=__( 'Upload the content in THRON', 'thron' )?></a><br/><br/><br/>');
			}
			
			// media-new.php
			var uploadForm = jQuery('#file-form');
			if (uploadForm.length) {
				uploadForm.after('<h2><?php _ex( 'or', 'Uploader: Drop files here - or - Select Files' ); ?> <a href="' + uploadUrl + '" target="_blank" class="thron-upload-button"><?=__( 'upload the content in THRON', 'thron' )?></a></h2>');
			}

			// Modale upload
            if( typeof wp.media.view.UploaderInline != 'undefined' ){
				wp.media.view.UploaderInline.prototype.template = wp.media.template( 'uploader-inline-custom' );
            }
			
			// Modale edit image
            if( typeof wp.media.view.Attachment.Details.TwoColumn != 'undefined' ){
				wp.media.view.Attachment.Details.TwoColumn.prototype.template = wp.media.template( 'attachment-details-two-column-custom' );
	        }			
			
        });
    </script>	

		<?php
	}

	function ws_add_overrides() {
			$args = array (
				'id'        =>  '', // id
			);

		add_action( 'admin_footer-post.php', [$this,'ws_add_media_overrides'] );
		add_action( 'admin_footer-media-new.php', [$this,'ws_add_media_overrides'] );
		add_action( 'admin_footer-edit.php', [$this,'ws_add_media_overrides'] );
		add_action( 'admin_footer-upload.php', function() use ( $args ) { 
			$this->ws_add_media_overrides( $args ); } );
	
		}

}