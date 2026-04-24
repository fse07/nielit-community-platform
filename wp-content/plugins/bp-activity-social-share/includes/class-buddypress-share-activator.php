<?php
/**
 * Fired during plugin activation.
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 *
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/includes
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 */

/**
 * Fired during plugin activation.
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 *
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/includes
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 */
class Buddypress_Share_Activator {

	/**
	 * Short Description. (use period)
	 *  Long Description.
	 *
	 *  @since    1.0.0
	 */
	public static function activate() {
		if ( class_exists( 'BuddyPress' ) ) {
			if ( get_site_option( 'bp_share_services' ) == false ) {
				$bp_share_social_icon_default = array(
					'Facebook' => 'Facebook',
					'Twitter'  => 'Twitter',
					'Linkedin' => 'Linkedin',
					'Whatsapp' => 'Whatsapp',
				);
					update_site_option( 'bp_share_services', $bp_share_social_icon_default );
			}
			if ( get_site_option( 'bpas_icon_color_settings' ) == false ) {
				$bp_icon_color_default = array(
					'bpas_facebook_bg_color'  => '#3B5998',
					'bpas_twitter_bg_color'   => '#1DA1F2',
					'bpas_pinterest_bg_color' => '#000000',
					'bpas_linkedin_bg_color'  => '#007BB6',
					'bpas_reddit_bg_color'    => '#000000',
					'bpas_wordpress_bg_color' => '#000000',
					'bpas_pocket_bg_color'    => '#000000',
					'bpas_email_bg_color'     => '#AD0000',
					'bpas_whatsapp_bg_color'  => '#46bd00',
				);
					update_site_option( 'bpas_icon_color_settings', $bp_icon_color_default );
			}
		}

	}

}
