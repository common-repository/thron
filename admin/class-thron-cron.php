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
class Thron_Cron {

	private $wp_language;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->wp_language = strtoupper( substr( get_locale(), 0, 2 ) );
	}

	function thron_cron_schedules($schedules){
		if(!isset($schedules["minute"])){
			$schedules["minute"] = array(
				'interval' => 60,
				'display' => __('Once every 1 minutes'));
		}
		return $schedules;
	}

	public function thron_update_file() {
		$thron_options = get_option( 'thron_option_api_page' );
		$pkey          = get_option( 'thron_pkey' );

		$start = get_option( 'thron_last_sync' );

		if ( is_array( $thron_options ) ) {
			$clientId = $thron_options['thron_clientId'];
			$appId    = $thron_options['thron_appId'];
			$appKey   = $thron_options['thron_appKey'];

			$thron_api = new ThronAPI( $appId, $clientId, $appKey );

			$start = strtotime( "-10 days" );

			$details = array();

			$nextPage = null;
			do {
				$sync_list = $thron_api->sync_list( $start, $nextPage);

				if (count($sync_list->items) > 0) {
					foreach ($sync_list->items as $item) {
						array_push($details, $item) ;
					}
				}

				$nextPage = property_exists($sync_list, 'nextPage') ? $sync_list->nextPage : null;

			} while ($nextPage != null);

			if ( count( $details ) > 0 ) {
				foreach ( $details as $detail ) {

					$args  = array(
						'post_type'   => 'attachment',
						'post_status' => 'inherit',
						'meta_query'  => array(
							array(
								'key'     => 'thron_id',
								'value'   => $detail->content->id,
								'compare' => '=',
							)
						)
					);
					$query = new WP_Query( $args );

					$posts = $query->posts;

					if ( count( $query->posts ) > 0 ) {
						foreach ( $posts as $post ) {

							/**
							 * Se la trova seleziona la lingua di default dell'utente
							 */
							$language = $detail->content->locales[0];

							foreach ( $detail->content->locales as $locale ) {
								if ( 'EN' == $locale->locale ) {
									$language = $locale;
								}
							}
							foreach ( $detail->content->locales as $locale ) {
								if ( $this->wp_language == $locale->locale ) {
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
											$mime = sanitize_mime_type($metadata->value);

											break;
									}
								}
							}

							list ( $type, $submime ) = explode( '/', $mime );

							$specific_post = array();
							$attachment    = array(
								'ID'             => $post->ID,
								'alt'            => $language->name,
								'guid'           => sanitize_title($detail->content->id),
								'post_title'     => sanitize_text_field($language->name),
								'post_content'   => $language->description ? $language->description : $language->name,
								'caption'        => "",
								'type'           => $type,
								'post_mime_type' => $mime,
								'subtype'        => $submime,
								'editLink'       => false,
								'status'         => 'inherit'
							);

							$channels = array_column( $detail->content->weebo->weeboChannels, 'channelType' );

							$file_name = sanitize_title( $language->name ) . '.' . thron_mime2ext( $mime );

							switch ( $detail->content->contentType ) {

								case 'IMAGE':

									$thron_options = get_option( 'thron_option_api_page' );
									$width_default = (array_key_exists('thron_maxImageWidth', $thron_options)) ? $thron_options['thron_maxImageWidth'] : '0';

									$thumbs = 'https://' . str_replace( '//', '', strtok( $detail->thumbUrl, '?' ) );
									/**
									 * URL of the high resolution image.
									 */
									$attached_file      = 'https://' . $clientId . '-cdn.thron.com/delivery/public/image/' . $clientId . '/' . $detail->content->id . '/' . $pkey . '/std/' . $file_name;
									$attached_file_full = 'https://' . $clientId . '-cdn.thron.com/delivery/public/image/' . $clientId . '/' . $detail->content->id . '/' . $pkey . '/std/' . strval($width_default) . 'x0/' . $file_name;

									$file_name = sanitize_title( $language->name ) . '.' . thron_mime2ext( $mime );

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
										'file'      => strval($width_default) . 'x0/' . $file_name,
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

									$thumbs = 'https://' . str_replace( '//', '', strtok( $detail->thumbUrl, '?' ) );

									$attached_file = 'https://' . $clientId . '-cdn.thron.com/delivery/public/video/' . $clientId . '/' . $detail->content->id . '/' . $pkey . '/' . $channelType . '/' . $file_name;

									$file_name = sanitize_title( $language->name ) . '.' . thron_mime2ext( $mime );

									$specific_post = array(
										'filename' => $file_name,
										'url'      => $attached_file,
										'link'     => $attached_file,
										'icon'     => $thumbs
									);


									break;
								case 'AUDIO':

									$channelType = thron_get_channel( $channels );

									$thumbs = 'https://' . str_replace( '//', '', strtok( $detail->thumbUrl, '?' ) );

									$attached_file = 'https://' . $clientId . '-cdn.thron.com/delivery/public/audio/' . $clientId . '/' . $detail->content->id . '/' . $pkey . '/' . $channelType . '/' . $file_name;

									$specific_post = array(
										'filename'                => $file_name,
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

									$thumbs = 'https://' . str_replace( '//', '', strtok( $detail->thumbUrl, '?' ) );

									$attached_file = 'https://' . $clientId . '-cdn.thron.com/delivery/public/document/' . $clientId . '/' . $detail->content->id . '/' . $pkey . '/' . $channelType . '/' . $file_name;

									$specific_post = array(
										'url'      => $attached_file,
										'link'     => $attached_file,
										'icon'     => $thumbs,
										'filename' => $file_name,
									);

									break;
							}

							foreach ( $specific_post as $key => $val ) {
								$attachment[ $key ] = $val;
							}

							if ( $attachment ) {

								wp_update_post( $attachment );

								wp_update_attachment_metadata( $post->ID, $attachment_metadata );

								if ( $attached_file ) {
									/**
									 * Il valore _wp_attached_file viene sottratto all'url del file
									 */
									update_post_meta( $post->ID, '_wp_attached_file', $attached_file );
								}
							}
						}
					}
				}
			}
		}

		update_option( 'thron_last_sync', time() );
	}

}