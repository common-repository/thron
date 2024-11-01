<?php
/**
 * REST API: THRON_WP_REST_Attachmnent_Controller class
 *
 * @package THRON_WP_REST_Attachments_Controller
 * @subpackage ADMIN_CLASS
 * @since 1.1.0
 */

/**
 * THRON PLUGIN class to access attachments via WP_REST_Attachments_Controller extension.
 *
 * @since 1.1.0
 *
 * @see WP_REST_Attachments_Controller
 */

class THRON_WP_REST_Attachments_Controller extends WP_REST_Attachments_Controller {

	function add_custom_media_edit_api(){
        register_rest_route( 'wp/v2', '/media/(?P<id>[\d]+)/edit', array(
            'methods' => 'POST',
            'callback' => array($this, 'edit_media_item'),
        ), true);
    }


    function prepare_meta($attach_id, $new_url, $full_url, $thron_id, $qs) {

        $thron_options = get_option( 'thron_option_api_page' );
    
        $clientId = $thron_options['thron_clientId'];
        $appId    = $thron_options['thron_appId'];
        $appKey   = $thron_options['thron_appKey'];

        $pkey     = get_option( 'thron_pkey' );
    
        $thron_api = new ThronAPI( $appId, $clientId, $appKey );
        $detail = $thron_api->get_content_detail( $thron_id );    
       
        
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
    
        $file_name     = sanitize_title( $detail->content->locales[0]->name ) . '.' . thron_mime2ext( $mime );
        $width_default = ( array_key_exists( 'thron_maxImageWidth', $thron_options ) ) ? $thron_options['thron_maxImageWidth'] : '0';
        
        $attached_file_full = 'https://' . $clientId . '-cdn.thron.com/delivery/public/image/' . $clientId . '/' . $thron_id . '/' . $pkey . '/std/' . strval( $width_default ) . 'x0/' . $file_name . $qs;
        $size = getimagesize( $attached_file_full );
    
        $attachment_metadata = array(
            'width'  => $size[0],
            'height' => $size[1],
            'file'   => $attached_file_full
        );
    
        $attachment_metadata['sizes']['full'] = array(
            'width'     => $size[0],
            'height'    => $size[1],
            'file'      => strval( $width_default ) . 'x0/' . $file_name. $qs,
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
                $file = '' . $width . 'x' . $height . '/' . $file_name. $qs;

                $attachment_metadata['sizes'][ $image_size ] = array(
                    'width'     => $width,
                    'height'    => $height,
                    'file'      => $file,
                    'crop'      => get_option( "{$image_size}_crop" ) ? get_option( "{$image_size}_crop" ) : false,
                    'mime-type' => $mime
                );
            }
        }
    
        return $attachment_metadata;
    
    }


    function edit_media_item($request) {
        $attachment_id = $request['id'];
        $thron_id = get_post_meta( $attachment_id, 'thron_id', true );

        if ( ! $thron_id ) {

            return parent::edit_media_item($request);

        } else {

            // Se immagine THRON e ci sono i dati del crop
            if ( isset( $request['x'], $request['y'], $request['width'], $request['height'] ) ) {

                $thron_options = get_option( 'thron_option_api_page' );
    
                $clientId = $thron_options['thron_clientId'];
                $appId    = $thron_options['thron_appId'];
                $appKey   = $thron_options['thron_appKey'];
                $pkey     = get_option( 'thron_pkey' );  
        
                $thron_api = new ThronAPI( $appId, $clientId, $appKey );
                $detail = $thron_api->get_content_detail( $thron_id );  
                $file_name     = sanitize_title( $detail->content->locales[0]->name ) . '.' . thron_mime2ext( $mime );   

                // Costruisco l'URL croppato
				$saved_url = parse_url($request['src']);
				parse_str($saved_url["query"], $saved_crop);
                $full_url = 'https://' . $clientId . '-cdn.thron.com/delivery/public/image/' . $clientId . '/' . $thron_id . '/' . $pkey . '/std/' . $request['width'] . 'x' . $request['height'] . '/' . $file_name;
                $base_url = 'https://' . $clientId . '-cdn.thron.com/delivery/public/image/' . $clientId . '/' . $thron_id . '/' . $pkey . '/std/' . $file_name;

				$cropx = $request['x'];
				$cropy = $request['y'];
				$cropw = $request['width'];
				$croph = $request['height'];

				// Sto croppando un'immagine già croppata
				if ( isset( $saved_crop['cropx'], $saved_crop['cropy'], $saved_crop['cropw'], $saved_crop['croph'] ) ) {
					$cropx = ($saved_crop['cropw'] * $cropx) / 100 + $saved_crop['cropx'];
					$cropy = ($saved_crop['croph'] * $cropy) / 100 + $saved_crop['cropy'];
					$cropw = ($saved_crop['cropw'] * $cropw) / 100;
					$croph = ($saved_crop['croph'] * $croph) / 100;
				}

				$qs = "?cropx=" . round($cropx,2) . "&cropy=" . round($cropy,2) . "&cropw=" . round($cropw,2) ."&croph=" . round($croph,2);

				require_once ABSPATH . 'wp-admin/includes/image.php';

				$image_file = wp_get_original_image_path( $attachment_id );
				
				// Calculate the file name.
                $image_ext  = pathinfo( explode("?", $image_file)[0], PATHINFO_EXTENSION );
		
                $new_url = $base_url . $image_ext . $qs;

                // Copy post_content, post_excerpt, and post_title from the edited image's attachment post.
		        $attachment_post = get_post( $attachment_id );

				if ( $attachment_post ) {
					$new_attachment_post['post_content'] = $attachment_post->post_content;
					$new_attachment_post['post_excerpt'] = $attachment_post->post_excerpt;
					$new_attachment_post['post_title']   = $attachment_post->post_title;
                    $new_attachment_post['post_mime_type'] = $attachment_post->post_mime_type;
                    $new_attachment_post['post_parent'] = $attachment_id;
				}

				$new_attachment_id = wp_insert_attachment( wp_slash( $new_attachment_post ), $new_url, 0, true );

                update_post_meta( $new_attachment_id, 'thron_id', $thron_id);
		
				if ( is_wp_error( $new_attachment_id ) ) {
					if ( 'db_update_error' === $new_attachment_id->get_error_code() ) {
						$new_attachment_id->add_data( array( 'status' => 500 ) );
					} else {
						$new_attachment_id->add_data( array( 'status' => 400 ) );
					}
		
					return $new_attachment_id;
				}

				// Copy the image alt text from the edited image.
				$image_alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
		
				if ( ! empty( $image_alt ) ) {
					// update_post_meta() expects slashed.
					update_post_meta( $new_attachment_id, '_wp_attachment_image_alt', wp_slash( $image_alt ) );
				}

				if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
					// Set a custom header with the attachment_id.
					// Used by the browser/client to resume creating image sub-sizes after a PHP fatal error.
					header( 'X-WP-Upload-Attachment-ID: ' . $new_attachment_id );
				}
		
				// Generate image sub-sizes and meta.
                $new_image_meta = $this->prepare_meta($new_attachment_id, $new_url, $full_url, $thron_id, $qs);
		
				// Copy the EXIF metadata from the original attachment if not generated for the edited image.
				if ( isset( $image_meta['image_meta'] ) && isset( $new_image_meta['image_meta'] ) && is_array( $new_image_meta['image_meta'] ) ) {
					// Merge but skip empty values.
					foreach ( (array) $image_meta['image_meta'] as $key => $value ) {
						if ( empty( $new_image_meta['image_meta'][ $key ] ) && ! empty( $value ) ) {
							$new_image_meta['image_meta'][ $key ] = $value;
						}
					}
				}
		
				wp_update_attachment_metadata( $new_attachment_id, $new_image_meta );

				$response = parent::prepare_item_for_response( get_post( $new_attachment_id ), $request );
				$response->set_status( 201 );
				$response->header( 'Location', rest_url( sprintf( '%s/%s/%s', $this->namespace, $this->rest_base, $new_attachment_id ) ) );
				return $response;
                
            } else {
                // Se immagine THRON e ma non ci sono i dati del crop
                return new WP_Error(
                    'rest_image_not_edited',
                    __( 'The image was not edited. Edit the image before applying the changes.' ),
                    array( 'status' => 400 )
                );
            }    
        }
    }




}