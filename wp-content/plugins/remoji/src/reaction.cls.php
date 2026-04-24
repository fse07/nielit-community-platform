<?php
/**
 * Reaction class
 *
 * @since 1.0
 */
namespace remoji;
defined( 'WPINC' ) || exit;

class Reaction extends Instance {
	protected static $_instance;

	const TYPE_DEL = 'del';

	private $_tb;
	private $__data;

	protected function __construct()
	{
		$this->__data = Data::get_instance();
		$this->_tb = $this->__data->tb( 'history' );
	}

	/**
	 * Add Reaction
	 */
	public function add() {
		defined( 'debug' ) && debug2( 'add reaction' );

		if ( empty( $_POST[ 'emoji' ] ) || empty( $_POST[ 'related_id' ] ) || empty( $_POST[ 'related_type' ] ) ) {
			return REST::err( 'lack_of_param' );
		}

		$emoji = $_POST[ 'emoji' ];
		$related_id = (int) $_POST[ 'related_id' ];
		$related_type = $_POST[ 'related_type' ];

		// Security check
		if ( ! $emoji || ! ( $img = GUI::get_instance()->emoji( $emoji ) ) ) {
			return REST::err( 'invalid_emoji' );
		}

		if ( ! in_array( $related_type, array( 'post', 'comment' ), true ) ) {
			return REST::err( 'invalid_emoji_type' );
		}

		if ( $related_type == 'post' && ! Conf::val( 'post_emoji' ) ) {
			return REST::err( 'post_emoji_disabled' );
		}

		if ( $related_type == 'comment' && ! Conf::val( 'comment_emoji' ) ) {
			return REST::err( 'comment_emoji_disabled' );
		}

		// Check if is repeated react or not
		$if_reacted = $this->_can_react( $emoji, $related_id, $related_type );
		if ( $if_reacted !== 'ok' ) {
			return REST::err( $if_reacted );
		}
		// Add log
		$this->_log_reaction( $emoji, $related_id, $related_type );

		$emoji_list = $related_type == 'comment' ? get_comment_meta( $related_id, 'remoji', true ) : get_post_meta( $related_id, 'remoji', true );
		if ( ! $emoji_list ) {
			$emoji_list = array();
		}

		if ( empty( $emoji_list[ $emoji ] ) ) {
			$emoji_list[ $emoji ] = 0;
		}
		$emoji_list[ $emoji ]++;

		$related_type == 'comment' ? update_comment_meta( $related_id, 'remoji', $emoji_list ) : update_post_meta( $related_id, 'remoji', $emoji_list );

		do_action( 'remoji_reaction_add', $related_type, $related_id, $emoji );

		return REST::ok( array( 'src' => REMOJI_URL . 'data/emoji/' . $img . '.svg' ) );
	}

	/**
	 * Delete reaction
	 */
	private function _del() {
		global $wpdb;

		$id = empty( $_GET[ 'remoji_id' ] ) ? 0 : (int) $_GET[ 'remoji_id' ];
		if ( $id <= 0 ) {
			return;
		}

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `$this->_tb` WHERE id = %d", $id ) );
		if ( ! $row ) {
			return;
		}

		$emoji = $row->emoji;
		$related_id = $row->related_id;
		$related_type = $row->related_type;

		// Update related comment/post
		$emoji_list = $related_type == 'comment' ? get_comment_meta( $related_id, 'remoji', true ) : get_post_meta( $related_id, 'remoji', true );
		if ( ! empty( $emoji_list ) && ! empty( $emoji_list[ $emoji ] ) ) {
			$emoji_list[ $emoji ] --;
			if ( ! $emoji_list[ $emoji ] ) {
				unset( $emoji_list[ $emoji ] );
			}

			$related_type == 'comment' ? update_comment_meta( $related_id, 'remoji', $emoji_list ) : update_post_meta( $related_id, 'remoji', $emoji_list );
		}

		// Delete log
		$q = "DELETE FROM `$this->_tb` WHERE id = %d";
		$wpdb->query( $wpdb->prepare( $q, $id ) );

		do_action( 'remoji_reaction_del', $related_type, $related_id, $emoji );

		GUI::succeed( __( 'Delete emoji reaction record successfully!', 'remoji' ) );
	}

	/**
	 * Check if has reacted same emoji
	 */
	private function _can_react( $emoji, $related_id, $related_type ) {
		global $wpdb;

		if ( ! Conf::val( 'guest' ) ) {
			if ( ! is_user_logged_in() ) {
				return 'login_required';
			}
		}

		$ip = IP::me();
		if ( Conf::val( 'gdpr' ) ) {
			$ip = md5( $ip );
		}

		$q = "SELECT * FROM `$this->_tb` WHERE ip = %s AND emoji = %s AND related_id = %s AND related_type = %s ORDER BY id DESC LIMIT 1";
		$row = $wpdb->get_row( $wpdb->prepare( $q, array( $ip, $emoji, $related_id, $related_type ) ) );
		if ( $row ) {
			return 'duplicate_reaction';
		}

		$q = "SELECT count(*) FROM `$this->_tb` WHERE ip = %s AND related_id = %s AND related_type = %s";
		$count = $wpdb->get_var( $wpdb->prepare( $q, array( $ip, $related_id, $related_type ) ) );
		if ( $count >= Conf::val( 'max_emoji_per_ip' ) ) {
			return 'max_emoji_per_ip';
		}

		return 'ok';
	}

	/**
	 * Log the reaction
	 */
	private function _log_reaction( $emoji, $related_id, $related_type ) {
		global $wpdb;

		$ip = IP::me();

		// Parse Geo info
		$ip_geo_list = IP::geo( $ip );
		unset( $ip_geo_list[ 'ip' ] );
		$ip_geo = array();
		foreach ( $ip_geo_list as $k => $v ) {
			$ip_geo[] = $k . ':' . $v;
		}
		$ip_geo = implode( ', ', $ip_geo );

		defined( 'debug' ) && debug2( 'reacted', $ip_geo );

		// GDPR compliance
		if ( Conf::val( 'gdpr' ) ) {
			$ip = md5( $ip );
		}

		$q = "INSERT INTO `$this->_tb` SET ip = %s, ip_geo = %s, emoji = %s, related_id = %d, related_type = %s, dateline = %d";
		$wpdb->query( $wpdb->prepare( $q, array( $ip, $ip_geo, $emoji, $related_id, $related_type, time() ) ) );
	}

	/**
	 * Display log
	 *
	 * @since  1.2
	 * @access public
	 */
	public function history_list( $limit, $offset = false ) {
		global $wpdb;

		if ( $offset === false ) {
			$total = $this->count_list();
			$offset = Util::pagination( $total, $limit, true );
		}

		$q = "SELECT * FROM `$this->_tb` ORDER BY id DESC LIMIT %d, %d";
		return $wpdb->get_results( $wpdb->prepare( $q, $offset, $limit ) );
	}

	/**
	 * Count the log list
	 */
	public function count_list() {
		global $wpdb;

		if ( ! $this->__data->tb_exist( 'history' ) ) {
			return false;
		}

		$q = "SELECT COUNT(*) FROM `$this->_tb`";
		return $wpdb->get_var( $q );

	}

	/**
	 * Handler
	 *
	 * @since  1.4
	 */
	public static function handler() {
		$instance = self::get_instance();

		$type = Router::verify_type();

		switch ( $type ) {
			case self::TYPE_DEL:
				$instance->_del();
				break;

			default:
				break;
		}
	}
}
