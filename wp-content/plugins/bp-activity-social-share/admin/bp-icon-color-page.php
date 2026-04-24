<?php
wp_enqueue_script( 'wp-color-picker' );
wp_enqueue_style( 'wp-color-picker' );
$bpas_icon_color_settings = get_option( 'bpas_icon_color_settings' );
?>
<div class="wbcom-tab-content">
				<div class="wbcom-admin-title-section">
					<h3><?php esc_html_e( 'Icon Color Settings', 'buddypress-share' ); ?></h3>
				</div>
				<div class="wbcom-admin-option-wrap wb-activity-share-color-icons wbcom-admin-option-wrap-view">
					<div class="save-option-message"></div>
					<div class="option-not-save-message"></div>
					<form method="post" action="options.php">
						<?php
							settings_fields( 'bpas_icon_color_settings' );
							do_settings_sections( 'bpas_icon_color_settings' );
						?>
						 <div class="wbcom-settings-section-wrap">
							<div class="wbcom-settings-section-options-heading">
								<label for="wbcom-social-share">
									<?php esc_html_e( 'Sharing Icon Color', 'buddypress-share' ); ?>
								</label>
							</div>		
								<div class="wbcom-settings-section-options">
								<ul class="wb-social-icon-color-option">
									<li>	
										<label>
											<small><?php esc_html_e( 'Facebbok Icon Color', 'buddypress-share' ); ?></small>
										</label>					
										<input type="text"  class="bp_share_facebook" aria-expanded="false" name="bpas_icon_color_settings[bpas_facebook_bg_color]"  value="<?php echo isset( $bpas_icon_color_settings['bpas_facebook_bg_color'] ) ? esc_attr( $bpas_icon_color_settings['bpas_facebook_bg_color'] ) : ''; ?>">										
									</li>									
									<li>	
										<label>
											<small><?php esc_html_e( 'Twitter Icon Color', 'buddypress-share' ); ?></small>
										</label>					
										<input type="text"  class="bp_share_twitter" name="bpas_icon_color_settings[bpas_twitter_bg_color]"  value="<?php echo isset( $bpas_icon_color_settings['bpas_twitter_bg_color'] ) ? esc_attr( $bpas_icon_color_settings['bpas_twitter_bg_color'] ) : ''; ?>">		
									</li>
									<li>	
										<label>
											<small><?php esc_html_e( 'Pinterest Icon Color', 'buddypress-share' ); ?></small>
										</label>					
										<input type="text"  class="bp_share_pinterest" name="bpas_icon_color_settings[bpas_pinterest_bg_color]"  value="<?php echo isset( $bpas_icon_color_settings['bpas_pinterest_bg_color'] ) ? esc_attr( $bpas_icon_color_settings['bpas_pinterest_bg_color'] ) : ''; ?>">
									</li>
									<li>	
										<label>
											<small><?php esc_html_e( 'Linkedin Icon Color', 'buddypress-share' ); ?></small>
										</label>					
										<input type="text"  class="bp_share_linkedin" name="bpas_icon_color_settings[bpas_linkedin_bg_color]"  value="<?php echo isset( $bpas_icon_color_settings['bpas_linkedin_bg_color'] ) ? esc_attr( $bpas_icon_color_settings['bpas_linkedin_bg_color'] ) : ''; ?>">
									</li>
									<li>	
										<label>
											<small><?php esc_html_e( 'Raddit Icon Color', 'buddypress-share' ); ?></small>
										</label>					
										<input type="text"  class="bp_share_raddit" name="bpas_icon_color_settings[bpas_reddit_bg_color]"  value="<?php echo isset( $bpas_icon_color_settings['bpas_reddit_bg_color'] ) ? esc_attr( $bpas_icon_color_settings['bpas_reddit_bg_color'] ) : ''; ?>">
									</li>
									<li>
										<label>
											<small><?php esc_html_e( 'WordPress Icon Color', 'buddypress-share' ); ?></small>
										</label>						
										<input type="text"  class="bp_share_wordpress" name="bpas_icon_color_settings[bpas_wordpress_bg_color]"  value="<?php echo isset( $bpas_icon_color_settings['bpas_wordpress_bg_color'] ) ? esc_attr( $bpas_icon_color_settings['bpas_wordpress_bg_color'] ) : ''; ?>">
									</li>
									<li>	
									<label>
											<small><?php esc_html_e( 'Pocket Icon Color', 'buddypress-share' ); ?></small>
										</label>					
										<input type="text"  class="bp_share_pocket" name="bpas_icon_color_settings[bpas_pocket_bg_color]"  value="<?php echo isset( $bpas_icon_color_settings['bpas_pocket_bg_color'] ) ? esc_attr( $bpas_icon_color_settings['bpas_pocket_bg_color'] ) : ''; ?>">
									</li>
									<li>	
										<label>
											<small><?php esc_html_e( 'E-mail Icon Color', 'buddypress-share' ); ?></small>
										</label>					
										<input type="text"  class="bp_share_email" name="bpas_icon_color_settings[bpas_email_bg_color]"  value="<?php echo isset( $bpas_icon_color_settings['bpas_email_bg_color'] ) ? esc_attr( $bpas_icon_color_settings['bpas_email_bg_color'] ) : ''; ?>">
									</li>
									<li>	
										<label>
											<small><?php esc_html_e( 'Whatsapp Icon Color', 'buddypress-share' ); ?></small>
										</label>					
										<input type="text"  class="bp_share_wordpress" name="bpas_icon_color_settings[bpas_whatsapp_bg_color]"  value="<?php echo isset( $bpas_icon_color_settings['bpas_whatsapp_bg_color'] ) ? esc_attr( $bpas_icon_color_settings['bpas_whatsapp_bg_color'] ) : ''; ?>">
								</ul>
							</div>	
							<?php submit_button(); ?>			
						</form>
					</div>
				</div>


<script>
jQuery(document).ready(function( ) {
	jQuery(".bp_share_facebook").wpColorPicker();
	jQuery(".bp_share_twitter").wpColorPicker();
	jQuery(".bp_share_pinterest").wpColorPicker();
	jQuery(".bp_share_linkedin").wpColorPicker();
	jQuery(".bp_share_raddit").wpColorPicker();
	jQuery(".bp_share_wordpress").wpColorPicker();
	jQuery(".bp_share_pocket").wpColorPicker();
	jQuery(".bp_share_email").wpColorPicker();
	jQuery(".bp_share_wordpress").wpColorPicker();
});
</script>
