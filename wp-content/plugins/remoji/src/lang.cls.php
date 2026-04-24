<?php
/**
 * Language class
 *
 * @since 1.2
 */
namespace remoji;
defined( 'WPINC' ) || exit;

class Lang extends Instance {
	protected static $_instance;

	/**
	 * Init hook
	 * @since  1.2
	 */
	public function init() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
	}

	/**
	 * Plugin loaded hooks
	 * @since 1.2
	 */
	public function plugins_loaded() {
		load_plugin_textdomain( 'remoji', false, 'remoji/lang/' );
	}

	public static function msg( $tag ) {
		switch ( $tag ) {
			case strpos( $code, 'try_later ' ) === 0:
				$msg = sprintf( __( 'Please try again after %s.', 'remoji' ), '<code>' . Util::readable_time( substr( $code, strlen( 'try_later ' ) ), 3600, true ) . '</code>' );
				break;

			case 'login_required':
				$msg = __( 'You need to login to proceed this action.', 'remoji' );
				break;

			case 'max_emoji_per_ip':
				$msg = sprintf( __( 'You have reached maximum %s emojis for this post/comment.', 'remoji' ), '<code>' . Conf::val( 'max_emoji_per_ip' ) . '</code>' );
				break;

			case 'duplicate_reaction':
				$msg = __( 'You have reacted with this emoji.', 'remoji' );
				break;

			case 'post_emoji_disabled':
				$msg = __( 'Emoji reaction to Posts is disabled.', 'remoji' );
				break;

			case 'comment_emoji_disabled':
				$msg = __( 'Emoji reaction to Comments is disabled.', 'remoji' );
				break;

			default:
				$msg = 'unknown msg: ' . $tag;
				break;
		}

		return '<strong>Remoji</strong>: ' . $msg;
	}
}
