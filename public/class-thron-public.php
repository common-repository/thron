<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.thron.com
 * @since      1.0.0
 *
 * @package    Thron
 * @subpackage Thron/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Thron
 * @subpackage Thron/public
 * @author     THRON <integrations@thron.com>
 */
class Thron_Public {

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

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/thron-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		$thron_options = get_option( 'thron_option_api_page' );

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/thron-public.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'thron-player', 'https://' . $thron_options['thron_clientId'] . '-cdn.thron.com/shared/ce/bootstrap/1/scripts/embeds-min.js', array(), $this->version, false );
		wp_enqueue_script( 'thron-bootstrapper', 'https://' . $thron_options['thron_clientId'] . '-cdn.thron.com/shared/plugins/tracking/current/bootstrapper-min.js', array(), $this->version, false );
	}

	/**
	 * Aggiunge lo shortcode per l'embed del player
	 */
	public function thron_shortcode( $atts ) {

		$thron_options = get_option( 'thron_option_api_page' );

		$contentID   = $atts['contentid'];
		$embedCodeId = $atts['embedcodeid'];
		$scalemode = $atts['scalemode'];
		$quality = $atts['quality'];
		$brightness = $atts['brightness'];
		$contrast = $atts['contrast'];
		$sharpness = $atts['sharpness'];
		$color = $atts['color'];

		if($scalemode == 'manual'){
			$cropx = $atts['cropx'];
			$cropy = $atts['cropy'];
			$cropw = $atts['cropw'];
			$croph = $atts['croph'];
		}

		$aspectRatio      = $atts['aspectratio'] ? $atts['aspectratio'] : 75;
		$embedType        = $atts['embedtype'];
		$height           = $atts['height'];
		$clientId         = $thron_options['thron_clientId'];
		$sessId           = get_option( 'thron_pkey' );
		$tracking_context = get_option( 'thron_tracking_context' );

		$width = ( ( 'responsive' == $embedType ) and ( $atts['width'] == '' ) ) ? 100 : $atts['width'];

		$uniqID = uniqid();

		ob_start();
		include plugin_dir_path( __FILE__ ) . 'partials/player.php';
		$string = ob_get_clean();

		return $string;
	}

	public function rest_api_media() {
		register_rest_route( 'wp/v2', '/media/(?P<id>[a-zA-Z0-9-]+)', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'media' ),
			'args'     => array(
				'id' => array(
					'validate_callback' => function ( $param, $request, $key ) {
						return true;
					}
				),
			),
		) );
	}

	function wp_get_attachment_image_src( $image, $attachment_id, $size, $icon ) {

		$thron_id = get_post_meta( $attachment_id, 'thron_id', true );

		if ( ! $thron_id ) {
			return $image;
		}

		$thron_options = get_option( 'thron_option_api_page' );
		$width_default = ( array_key_exists( 'thron_maxImageWidth', $thron_options ) ) ? $thron_options['thron_maxImageWidth'] : '0';

		if ( $image ) {
			// $path = pathinfo( $image[0] );
			$path = pathinfo( get_post_meta( $attachment_id, '_wp_attached_file', true ) );

			$sub = explode( "/", $path['dirname'] );

			if ( end( $sub ) == 'std' ) {
				$width  = 0;
				$height = 0;

				if ( is_array( $size ) ) {
					$width  = $size[0];
					$height = $size[1];
				} else {
					$width  = intval( get_option( "{$size}_size_w" ) );
					$height = intval( get_option( "{$size}_size_h" ) );
				}

				$width    = $width == 0 ? $width_default : $width;
				$image[0] = $path['dirname'] . '/' . $width . 'x' . $height . '/' . $path['basename'];
			}

		}

		return $image;
	}

	function wp_get_attachment_url( $url, $attachment_id ) {

		$thron_options = get_option( 'thron_option_api_page' );
		$width_default = ( array_key_exists( 'thron_maxImageWidth', $thron_options ) ) ? $thron_options['thron_maxImageWidth'] : '0';


		$post = get_post( $attachment_id );

		$thron_id = get_post_meta( $attachment_id, 'thron_id', true );

		if ( ! $thron_id ) {
			return $url;
		}

		$url = get_post_meta( $attachment_id, '_wp_attached_file', true );

		if ( strpos($post->post_mime_type, 'image') === false ) {
			return $url;
		}

		$filename = basename( $url );

		$path = str_replace( $filename, '', $url );

		return $path . $width_default . 'x0/' . $filename;;
	}

	public function add_tci_class( $block_content, $block ) {

		if ( 'core/image' === $block['blockName'] 
				|| 'core/gallery' === $block['blockName']
				|| 'core/media-text' === $block['blockName']
				|| 'core/cover' === $block['blockName']) {
			$block_content = str_replace('wp-image-', 'tci wp-image-', $block_content);
			return $block_content;
		}

		if ( 'core/video' === $block['blockName'] ) {
			$block_content = preg_replace_callback( '/(<video) (.*video>)/i', 
				function ( $matches ) {
					return  $matches[1] .  ' class="tci" ' . $matches[2] ;
				},
				$block_content );

			return $block_content;
		}

		if ( 'core/audio' === $block['blockName'] ) {
			$block_content = preg_replace_callback( '/(<audio) (.*audio>)/i', 
				function ( $matches ) {
					return  $matches[1] .  ' class="tci" ' . $matches[2] ;
				},
				$block_content );

			return $block_content;
		}

		return $block_content;
	}

	public function filter_wp_get_attachment_image_attributes( $attr, $attachment, $size ) {

		$thron_id = get_post_meta( $attachment->ID, 'thron_id', true );

		if ( ! $thron_id ) {
			return $attr;
		}


		$attachment_metadata = wp_get_attachment_metadata( $attachment->ID );
		$attached_file       = dirname( get_post_meta( $attachment->ID, '_wp_attached_file', true ) );

		if ( !is_array($attachment_metadata) or ! array_key_exists( 'sizes', $attachment_metadata ) ) {
			return $attr;
		}

		$size_array             = $attachment_metadata['sizes'];
		$max_srcset_image_width = apply_filters( 'max_srcset_image_width', 2048, $size_array );

		$srcset = '';

		foreach ( $size_array as $size ) {
			if ( $size['width'] and $size['width'] < $max_srcset_image_width ) {
				$srcset .= str_replace( ' ', '%20', $attached_file . '/' . $size['file'] ) . ' ' . $size['width'] . 'w, ';
			}
		}

		$attr['srcset'] = rtrim( $srcset, ', ' );

		return $attr;
	}

	/**
	 * Add class tci to image
	 */

	public function wp_get_attachment_image_attributes( $attr, $attachment ) {

		$attr['class'] .= ' tci';

		return $attr;
	}

	public function get_image_tag_class($class) {
		return strpos($class, 'tci') !== false ? $class . ' tci' : $class;
	}
	
	function add_query_string_to_image_url($content) {

		$thron_options = get_option( 'thron_option_api_page' );
	
		// Cerco le immagini nel contenuto
		preg_match_all('/<img [^>]+>/i', $content, $matches);
	
		// Se ci sono immagini proseguo e ciclo le immagini
		if ($matches) {
			foreach ($matches[0] as $img_tag) {
				// prendo la url dell'immagine
				preg_match('/src=["\'](.*?)["\']/i', $img_tag, $src_match);
	
				if ($src_match) {
					$img_url = $src_match[1];
	
					// Aggiungo la query string all'immagine
					$new_query_string = '&format=auto&quality=' . $thron_options['thron_quality'];
	
					// Verifica se la URL ha già una query string
					if (strpos($img_url, '?') !== false) {
						// Se sì, aggiungi il nuovo parametro con "&"
						$new_img_url = $img_url . $new_query_string;
					} else {
						// Altrimenti, aggiungi il nuovo parametro con "?"
						$new_img_url = $img_url . '?' . substr($new_query_string, 1);
					}
	
					// Sostituisco la url con quella modificata sopra
					$content = str_replace($img_url, $new_img_url, $content);
				}
			}
		}
	
		return $content;
	}	
	
}
