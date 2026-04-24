<?php
namespace remoji;
defined( 'WPINC' ) || exit;

$__gui = GUI::get_instance();

?>
<form method="post" action="<?php menu_page_url( 'remoji' ); ?>" class="remoji-relative">
<?php wp_nonce_field( 'remoji' ); ?>

<h3 class="remoji-title-short"><?php echo __( 'Emoji Reaction Settings', 'remoji' ); ?></h3>

<table class="wp-list-table striped remoji-table"><tbody>
	<tr>
		<th><?php echo __( 'Emoji Reaction on Posts', 'remoji' ); ?></th>
		<td>
			<?php $__gui->build_switch( 'post_emoji' ); ?>
			<div class="remoji-desc">
				<?php echo __( 'Allow emoji reactions to posts.', 'remoji' ); ?>
			</div>
		</td>
	</tr>

	<tr>
		<th><?php echo __( 'Emoji Reaction on Comments', 'remoji' ); ?></th>
		<td>
			<?php $__gui->build_switch( 'comment_emoji' ); ?>
			<div class="remoji-desc">
				<?php echo __( 'Allow emoji reactions to comments.', 'remoji' ); ?>
			</div>
		</td>
	</tr>

	<tr>
		<th><?php echo __( 'Guest Reaction', 'remoji' ); ?></th>
		<td>
			<?php $__gui->build_switch( 'guest' ); ?>
			<div class="remoji-desc">
				<?php echo __( 'Allow guest visitors to send the reactions. If turned OFF, only logged-in users can react.', 'remoji' ); ?>
			</div>
		</td>
	</tr>

	<tr>
		<th><?php echo __( 'GDPR Compliance', 'remoji' ); ?></th>
		<td>
			<?php $__gui->build_switch( 'gdpr' ); ?>
			<div class="remoji-desc">
				<?php echo __( 'With this feature turned on, all logged IPs get obfuscated (md5-hashed).', 'remoji' ); ?>
			</div>
		</td>
	</tr>

	<tr>
		<th><?php echo __( 'Max Emojis Per IP', 'remoji' ); ?></th>
		<td>
			<p><?php $__gui->build_input( 'max_emoji_per_ip', 'remoji-input-short' ); ?></p>

			<div class="remoji-desc">
				<?php echo __( 'Only allow these amount of emojis per IP per post.', 'remoji' ); ?>
			</div>
		</td>
	</tr>
</tbody></table>

<h3 class="remoji-title-short"><?php echo __( 'Post View Settings', 'remoji' ); ?></h3>

<table class="wp-list-table striped remoji-table"><tbody>
	<tr>
		<th><?php echo __( 'Post View Count', 'remoji' ); ?></th>
		<td>
			<?php $__gui->build_switch( 'postview' ); ?>
			<div class="remoji-desc">
				<?php echo __( 'Enable post view count and show the number in posts.', 'remoji' ); ?>
				<?php echo __( 'Compatible with all cache plugins!', 'remoji' ); ?>
				<br><font class="remoji-success">
					<strong><?php echo __( 'API Supported', 'remoji' ); ?>:</strong>
					<?php echo __( 'Shortcode', 'remoji' ); ?>: <code>[views]</code> or <code>[views id="2"]</code>.
					<?php echo __( 'Hooks', 'remoji' ); ?>: <code>do_action( 'remoji_postview' );</code> or <code>do_action( 'remoji_postview', $any_post_id_to_quote );</code>.
				</font>
			</div>
		</td>
	</tr>

	<tr>
		<th><?php echo __( 'Delayed Count', 'remoji' ); ?></th>
		<td>
			<p><?php $__gui->build_input( 'postview_delay', 'remoji-input-short' ); ?> <?php echo __( 'Second(s)', 'remoji' ); ?></p>

			<div class="remoji-desc">
				<?php echo __( 'Only count the visit when the visitor stayed for longer than the above time.', 'remoji' ); ?>
				<?php echo __( 'This can avoid counting search engine bots and other invalid visitors.', 'remoji' ); ?>
				<?php echo __( 'Set to 0 to count right away.', 'remoji' ); ?>
			</div>
		</td>
	</tr>

	<tr>
		<th><?php echo __( 'Post View Template', 'remoji' ); ?></th>
		<td>
			<?php $__gui->build_input( 'postview_tpl', 'remoji-input-long' ); ?>

			<div class="remoji-desc">
				<?php echo sprintf( __( 'You can replace %s to other WordPress icons.', 'remoji' ), '<code>dashicons-visibility</code>' ); ?>
				<?php echo __( 'For more icons please visit ', 'remoji' ); ?><a href="https://developer.wordpress.org/resource/dashicons/#visibility" target="_blank">https://developer.wordpress.org/resource/dashicons/#visibility</a>
			</div>
			<div class="remoji-desc">
				<?php echo __( 'The default template is', 'remoji' ); ?>: <code><?php echo htmlspecialchars( Conf::$_default_options[ 'postview_tpl' ] ); ?></code>
			</div>
		</td>
	</tr>

	<tr>
		<th><?php echo __( 'Append Post View To Content', 'remoji' ); ?></th>
		<td>
			<?php $__gui->build_switch( 'postview_show_in_content' ); ?>
			<div class="remoji-desc">
				<?php echo __( 'This option can show the post view number in the post content bottom.', 'remoji' ); ?>
			</div>
		</td>
	</tr>

	<tr>
		<th><?php echo __( 'Append Post View To Theme', 'remoji' ); ?></th>
		<td>
			<?php $__gui->build_switch( 'postview_show_in_themebar' ); ?>
			<div class="remoji-desc">
				<?php echo __( 'If you are using default WordPress theme, this option can append the views automatically after the comment count.', 'remoji' ); ?>
			</div>
			<div class="remoji-desc">
				<?php echo __( 'NOTE: Currently only support the following themes', 'remoji' ); ?>: <code>Twentytwenty</code>
			</div>
		</td>
	</tr>
</tbody></table>

<h3 class="remoji-title-short"><?php echo __( 'Comment Settings', 'remoji' ); ?></h3>

<table class="wp-list-table striped remoji-table"><tbody>
	<tr>
		<th><?php echo __( 'Disable Comment', 'remoji' ); ?></th>
		<td>
			<div class="remoji-desc">
				<?php echo __( 'Select the post types to disable comments on', 'remoji' ); ?>:
			</div>
			<div class="remoji-tick-list">
				<?php foreach ( get_post_types() as $type ): ?>
					<?php $__gui->build_checkbox( 'disable_comment[]', $type, in_array( $type, Conf::val( 'disable_comment' ) ), $type ); ?>
				<?php endforeach; ?>
			</div>
		</td>
	</tr>
</tbody></table>

<h3 class="remoji-title-short"><?php echo __( 'General Settings', 'remoji' ); ?></h3>

<table class="wp-list-table striped remoji-table"><tbody>
	<tr>
		<th><?php echo __( 'Auto Upgrade', 'remoji' ); ?></th>
		<td>
			<?php $__gui->build_switch( 'auto_upgrade' ); ?>
			<div class="remoji-desc">
				<?php echo __( 'Enable this option to get the latest features at the first moment.', 'remoji' ); ?>
			</div>
		</td>
	</tr>
</tbody></table>

<div class='remoji-top20'></div>

<?php submit_button( __( 'Save Changes', 'remoji' ), 'primary', 'remoji-submit' ); ?>
<?php submit_button( __( 'Save Changes', 'remoji' ), 'primary remoji-float-submit', 'remoji-float-submit' ); ?>

</form>
