<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.thron.com
 * @since      1.0.0
 *
 * @package    Thron
 * @subpackage Thron/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Thron
 * @subpackage Thron/includes
 * @author     THRON <integrations@thron.com>
 */
class Thron_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		if ( ! wp_next_scheduled( 'thron_update_file' ) ) {
			// wp_schedule_event( time(), 'hourly',  'thron_update_file');
			wp_schedule_event( time(), 'minute',  'thron_update_file');
		}
	}
}
