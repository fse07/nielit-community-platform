<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://wbcomdesigns.com
 * @since      1.0.0
 *
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/public
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 */
class Buddypress_Share_Public {


	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @access public
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Buddypress_Share_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Buddypress_Share_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if ( ! wp_style_is( 'wb-font-awesome', 'enqueued' ) ) {
			wp_enqueue_style( 'wb-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', array(), $this->version, 'all' );
		}
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/buddypress-share-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @access public
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Buddypress_Share_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Buddypress_Share_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( 'jquery-ui-tooltip' );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/buddypress-share-public.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Display share button in front page.
	 *
	 * @access public
	 * @since    1.0.0
	 */
	public function bp_activity_share_button_dis() {
		$all_services = get_site_option( 'bp_share_services' );
		if ( is_user_logged_in() && ! empty( $all_services ) ) {
			add_action( 'bp_activity_entry_meta', array( $this, 'bp_share_activity_filter' ) );
		}
	}

	/**
	 * BP Share activity filter
	 *
	 * @access public
	 * @since    1.0.0
	 */
	public function bp_share_activity_filter() {
		global $activities_template;
		$social_service = get_site_option( 'bp_share_services' );
		$extra_options  = get_site_option( 'bp_share_services_extra' );
		$activity_type  = bp_get_activity_type();
		$activity_link  = bp_get_activity_thread_permalink();
		$activity_title = bp_get_activity_feed_item_title(); // use for description : bp_get_activity_feed_item_description().
		$plugin_path    = plugins_url();
		$mail_subject 	= strip_tags( $activities_template->activity->action );

		$updated_text = apply_filters( 'bpas_share_button_text_override', 'Share' );
		if ( isset( $updated_text ) ) {
			$share_button_text = $updated_text;
		}
		?>
		<div class="bp-share-btn generic-button">
			<a class="button item-button bp-secondary-action bp-share-button" rel="nofollow"><span><?php esc_html_e( 'Share', 'buddypress-share' ); ?></span></a>
		</div>
		
		<div class="service-buttons <?php echo esc_html( $activity_type ); ?>" style="display: none;">
			<?php
			if ( ! empty( $social_service ) ) {
				if ( isset( $social_service ) && ! empty( $social_service['Facebook'] ) ) {
					echo '<a href="https://www.facebook.com/sharer.php?u=' . esc_url( $activity_link ) . '" class="bp-share" id="bp_facebook_share"><span class="dashicons dashicons-facebook-alt"></span></a>';
				}
				if ( isset( $social_service ) && ! empty( $social_service['Twitter'] ) ) {
					$twitter_title = urlencode( html_entity_decode( get_the_title(), ENT_COMPAT, 'UTF-8' ) );
					echo '<a class="bp-share" id="bp_twitter_share"  href="https://twitter.com/share?url=' . esc_url( $activity_link ) . '&text=' . esc_html( $activity_title ) . '"><span class="dashicons dashicons-twitter"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M8 2H1L9.26086 13.0145L1.44995 21.9999H4.09998L10.4883 14.651L16 22H23L14.3917 10.5223L21.8001 2H19.1501L13.1643 8.88578L8 2ZM17 20L5 4H7L19 20H17Z"></path></svg></span></a>';
				}
				if ( isset( $social_service ) && ! empty( $social_service['Pinterest'] ) ) {
					$media = '';
					$video = '';
					echo '<a class="bp-share" id="bp_pinterest_share"  href="https://pinterest.com/pin/create/bookmarklet/?media=' . esc_url( $media ) . '&url=' . esc_url( $activity_link ) . '&is_video=' . esc_url( $video ) . '&description=' . esc_html( $activity_title ) . '"><span class="dashicons dashicons-pinterest
				"></span></a>';
				}
				if ( isset( $social_service ) && ! empty( $social_service['Reddit'] ) ) {
					echo '<a class="bp-share" id="bp_reddit_share"  href="http://reddit.com/submit?url=' . esc_url( $activity_link ) . '&title=' . esc_html( $activity_title ) . '"><span class="dashicons dashicons-reddit"></span></a>';
				}
				if ( isset( $social_service ) && ! empty( $social_service['WordPress'] ) ) {
					$description = '';
					$img         = '';
					echo '<a class="bp-share" id="bp_wordpress_share"  href="https://wordpress.com/wp-admin/press-this.php?u=' . esc_url( $activity_link ) . '&t=' . esc_html( $activity_title ) . '&s=' . esc_url( $description ) . '&i= ' . esc_url( $img ) . ' "><span class="dashicons dashicons-wordpress"></span></a>';
				}
				if ( isset( $social_service ) && ! empty( $social_service['Pocket'] ) ) {
					$description = '';
					$img         = '';
					echo '<a class="bp-share" id="bp_pocket_share"  href="https://getpocket.com/save?url=' . esc_url( $activity_link ) . '&title=' . esc_html( $activity_title ) . '"><span class="dashicons dashicons-arrow-down-alt2"></span></a>';
				}
				if ( isset( $social_service ) && ! empty( $social_service['Linkedin'] ) ) {
					echo '<a class="bp-share" id="bp_linkedin_share" href="http://www.linkedin.com/shareArticle?mini=true&url=' . esc_url( $activity_link ) . '&text=' . esc_html( $activity_title ) . '"><span class="dashicons dashicons-linkedin"></span></a>';
				}
				if ( isset( $social_service ) && ! empty( $social_service['Whatsapp'] ) ) {
					$whatsapp_share_link = 'https://wa.me/?text=' . rawurlencode( $activity_link );
					echo '<a class="bp-share" id="bp_whatsapp_share" href="' . esc_url( $whatsapp_share_link ) . '"><span class="dashicons dashicons-whatsapp"></span></a>';
				}				
				if ( isset( $social_service ) && ! empty( $social_service['E-mail'] ) ) {
					// Get the site title and URL dynamically
					$site_title = get_bloginfo( 'name' ); // Fetch the site title
					$site_url   = home_url();            // Fetch the site URL
				
					// Customize the email subject and body
					$email_subject = 'New Activity on ' . esc_html( $site_title ) . ': ' . esc_html( $mail_subject ); // Dynamic subject
					$email_body = "Hi,\n\nI wanted to share this activity with you from " . esc_html( $site_title ) . ":\n\n" . esc_url( $activity_link ) . "\n\nYou can explore more activities here: " . esc_url( $site_url ) . "\n\nBest regards,\nThe " . esc_html( $site_title ) . " Team"; // Dynamic body
				
					// Create the mailto link
					$email = 'mailto:?subject=' . rawurlencode( $email_subject ) . '&body=' . rawurlencode( $email_body );
				
					// Output the email share button
					echo '<a class="bp-share" id="bp_email_share" href="' . esc_url( $email ) . '"><span class="dashicons dashicons-email"></span></a>';
				}
				
			} else {
				esc_html_e( 'Please enable share services!', 'buddypress-share' );
			}
			do_action( 'bp_share_user_services', $social_service = array(), $activity_link, $activity_title );
			?>
		</div>

		<script>
			jQuery(document).ready(function() {
				var pop_active = '<?php echo isset( $extra_options['bp_share_services_open'] ) ? esc_html( $extra_options['bp_share_services_open'] ) : ''; ?>';
				if (pop_active == 1) {
					// Add popup class to all share links except WhatsApp and Email
					jQuery('.bp-share').not('#bp_whatsapp_share, #bp_email_share').addClass('has-popup');
				}
			});
		</script>
		<?php
	}

	/**
	 * Displays the language attributes for the ‘html’ tag.
	 *
	 * @param  string $output The type of HTML document. Accepts 'xhtml' or 'html'.
	 */
	public function bp_share_doctype_opengraph( $output ) {
		return $output . '
    xmlns:og="http://opengraphprotocol.org/schema/"
    xmlns:fb="http://www.facebook.com/2008/fbml"';
	}

	/**
	 * Share activity with og meta values
	 */
	public function bp_share_opengraph() {
		global $bp, $post;
		if ( ( bp_is_active( 'activity' ) && bp_is_current_component( 'activity' ) && ! empty( $bp->current_action ) && is_numeric( $bp->current_action ) && bp_is_single_activity() ) ) {
			$activity_img       = null;
			$activity_assets    = array();
			$activity_content   = null;
			$first_img_src      = null;
			$title              = null;
			$og_image           = null;
			$activity_permalink = null;
			$activity_obj       = new BP_Activity_Activity( $bp->current_action );
			$activity_link      = bp_activity_get_permalink( $bp->current_action );
			preg_match_all( '/(src|width|height)=("[^"]*")/', $activity_obj->content, $result );

			if ( isset( $result[2] ) && ! empty( $result[2] ) ) {
				$result_new = array_map(
					function ( $i ) {
						return trim( $i, '"' );
					},
					$result[2]
				);
				foreach ( $result[1] as $key => $result_key ) {
					$activity_assets[ $result_key ] = $result_new[ $key ];
				}
			}
			if ( ! empty( $activity_obj->action ) ) {
				$content = $activity_obj->action;
			} else {
				$content = $activity_obj->content;
			}

			$content = explode( '<span', $content );
			$title   = wp_strip_all_tags( ent2ncr( trim( convert_chars( $content[0] ) ) ) );

			if ( ':' === substr( $title, -1 ) ) {
				$title = substr( $title, 0, -1 );
			}

			$activity_content = preg_replace( '#<ul class="rtmedia-list(.*?)</ul>#', ' ', $activity_obj->content );

			if ( ! empty( $activity_assets['src'] ) ) {
				$activity_content = explode( '<span>', $activity_content );
				$activity_content = wp_strip_all_tags( ent2ncr( trim( convert_chars( $activity_content[1] ) ) ) );
			} else {
				$activity_content = $activity_obj->content;
			}

			preg_match_all( '/<img.*?src\s*=.*?>/', $activity_obj->content, $matches );
			if ( isset( $matches[0][0] ) ) {
				preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $matches[0][0], $matches2 );
				if ( isset( $matches2[1][0] ) ) {
					$first_img_src = $matches2[1][0];
				}
			}

			$og_image = '';

			if ( class_exists( 'BP_Media' ) ) {
				$media_ids = bp_activity_get_meta( $activity_obj->id, 'bp_media_ids', true );
				$media_ids = explode( ',', $media_ids );

				if ( ! empty( $media_ids[0] ) ) {
					$media_data = new BP_Media( $media_ids[0] );
					$og_image   = esc_attr( wp_get_attachment_image_url( $media_data->attachment_id, 'full' ) );
				}
			}

			// Youzer media support.
			if ( class_exists( 'Youzer' ) || class_exists( 'Youzify' ) ) {
				$media_ids = ! empty( bp_activity_get_meta( $activity_obj->id, 'yz_attachments', true ) ) ? bp_activity_get_meta( $activity_obj->id, 'yz_attachments', true ) : bp_activity_get_meta( $activity_obj->id, 'youzify_attachments', true );
				if ( ! empty( $media_ids ) ) {
					$media_id = array_key_first( $media_ids );
					$og_image = esc_attr( wp_get_attachment_image_url( $media_id, 'full' ) );
				}
			}

			$activity_content   = wp_strip_all_tags( $activity_content );
			$activity_content   = stripslashes( $activity_content );
			$extra_options      = get_site_option( 'bp_share_services_extra' );
			$enable_user_avatar = false;

			if ( ! empty( $first_img_src ) ) {
				$og_image = $first_img_src;
			}
			?>
				<!-- Existing Open Graph Meta Tags for Facebook -->
				<meta property="og:type" content="article" />
				<meta property="og:url" content="<?php echo esc_url( $activity_link ); ?>" />
				<meta property="og:title" content="<?php echo esc_html( $title ); ?>" />
				<meta property="og:description" content="<?php echo esc_html( $activity_content ); ?>" />
				<meta property="og:image" content="<?php echo esc_url( $og_image ); ?>" />
				<meta property="og:image:secure_url" content="<?php echo esc_url( $og_image ); ?>" />
				<meta property="og:image:width" content="400" />
				<meta property="og:image:height" content="300" />

				<!-- Additional Meta Tags for Twitter Card -->
				<meta name="twitter:card" content="<?php echo esc_url( $og_image ); ?>" /> <!-- Use 'summary_large_image' for large image with title and description, or 'summary' for smaller image -->
				<meta name="twitter:url" content="<?php echo esc_url( $activity_link ); ?>" />
				<meta name="twitter:title" content="<?php echo esc_html( $title ); ?>" />
				<meta name="twitter:description" content="<?php echo esc_html( $activity_content ); ?>" />
				<meta name="twitter:image" content="<?php echo esc_url( $og_image ); ?>" />

			<?php
		} else {
			return;
		}
	}

	public function bp_share_icon_custom_color() {
		$bpas_icon_color_settings = get_option( 'bpas_icon_color_settings' );
		$bpas_facebook_bg_color   = isset( $bpas_icon_color_settings['bpas_facebook_bg_color'] ) ? $bpas_icon_color_settings['bpas_facebook_bg_color'] : '';
		$bpas_twitter_bg_color    = isset( $bpas_icon_color_settings['bpas_twitter_bg_color'] ) ? $bpas_icon_color_settings['bpas_twitter_bg_color'] : '';
		$bpas_pinterest_bg_color  = isset( $bpas_icon_color_settings['bpas_pinterest_bg_color'] ) ? $bpas_icon_color_settings['bpas_pinterest_bg_color'] : '';
		$bpas_linkedin_bg_color   = isset( $bpas_icon_color_settings['bpas_linkedin_bg_color'] ) ? $bpas_icon_color_settings['bpas_linkedin_bg_color'] : '';
		$bpas_reddit_bg_color     = isset( $bpas_icon_color_settings['bpas_reddit_bg_color'] ) ? $bpas_icon_color_settings['bpas_reddit_bg_color'] : '';
		$bpas_wordpress_bg_color  = isset( $bpas_icon_color_settings['bpas_wordpress_bg_color'] ) ? $bpas_icon_color_settings['bpas_wordpress_bg_color'] : '';
		$bpas_pocket_bg_color     = isset( $bpas_icon_color_settings['bpas_pocket_bg_color'] ) ? $bpas_icon_color_settings['bpas_pocket_bg_color'] : '';
		$bpas_email_bg_color      = isset( $bpas_icon_color_settings['bpas_email_bg_color'] ) ? $bpas_icon_color_settings['bpas_email_bg_color'] : '';
		$bpas_whatsapp_bg_color   = isset( $bpas_icon_color_settings['bpas_whatsapp_bg_color'] ) ? $bpas_icon_color_settings['bpas_whatsapp_bg_color'] : '';
		?>
			<style>
				#bp_facebook_share span {
				<?php

				if ( ! empty( $bpas_facebook_bg_color ) ) {
					echo sprintf( 'color:%s', esc_html( $bpas_facebook_bg_color ) ) . ';';
				}
				?>
				}

				#bp_twitter_share span {
				<?php
				if ( ! empty( $bpas_twitter_bg_color ) ) {
					echo sprintf( 'color:%s', esc_html( $bpas_twitter_bg_color ) ) . ';';
				}
				?>
				}

				#bp_pinterest_share span {
				<?php
				if ( ! empty( $bpas_pinterest_bg_color ) ) {
					echo sprintf( 'color:%s', esc_html( $bpas_pinterest_bg_color ) ) . ';';
				}
				?>
				}

				#bp_linkedin_share span {
				<?php
				if ( ! empty( $bpas_linkedin_bg_color ) ) {
					echo sprintf( 'color:%s', esc_html( $bpas_linkedin_bg_color ) ) . ';';
				}
				?>
				}

				#bp_reddit_share span {
				<?php
				if ( ! empty( $bpas_reddit_bg_color ) ) {
					echo sprintf( 'color:%s', esc_html( $bpas_reddit_bg_color ) ) . ';';
				}
				?>
				}

				#bp_wordpress_share span {
				<?php
				if ( ! empty( $bpas_wordpress_bg_color ) ) {
					echo sprintf( 'color:%s', esc_html( $bpas_wordpress_bg_color ) ) . ';';
				}
				?>
				}

				#bp_pocket_share span {
				<?php
				if ( ! empty( $bpas_pocket_bg_color ) ) {
					echo sprintf( 'color:%s', esc_html( $bpas_pocket_bg_color ) ) . ';';
				}
				?>
				}

				#bp_email_share span {
				<?php
				if ( ! empty( $bpas_email_bg_color ) ) {
					echo sprintf( 'color:%s', esc_html( $bpas_email_bg_color ) ) . ';';
				}
				?>
				}

				#bp_whatsapp_share .dashicons-whatsapp {
				<?php
				if ( ! empty( $bpas_whatsapp_bg_color ) ) {
					echo sprintf( 'color:%s', esc_html( $bpas_whatsapp_bg_color ) ) . ';';
				}
				?>
				}
			</style>
		<?php
	}
}
