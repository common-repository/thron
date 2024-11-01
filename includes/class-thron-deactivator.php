<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://www.thron.com
 * @since      1.0.0
 *
 * @package    Thron
 * @subpackage Thron/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Thron
 * @subpackage Thron/includes
 * @author     THRON <integrations@thron.com>
 */
class Thron_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook( 'thron_update_file' );

		// Delete token ID
		update_option( 'thron_token_id', null );

		// Delete token ID time
		update_option( 'thron_token_id_time', null );

		// Delete  pkey
		update_option( 'thron_pkey', null );

		// Delete tracking context
		update_option( 'thron_tracking_context', null );

		// Delete default player template
		update_option( 'thron_playerTemplates', null );

		// Delete root folder
		update_option( 'thron_rootCategoryId', null );

		// Delete THRON Configurtion
		update_option( 'thron_option_api_page', null );

	}

}
