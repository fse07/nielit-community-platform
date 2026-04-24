<?php
/**
 * GUI class
 *
 * @since 1.0
 */
namespace remoji;
defined( 'WPINC' ) || exit;

class GUI extends Instance {
	protected static $_instance;

	const DB_MSG = 'remoji.msg';
	const NOTICE_BLUE = 'notice notice-info';
	const NOTICE_GREEN = 'notice notice-success';
	const NOTICE_RED = 'notice notice-error';
	const NOTICE_YELLOW = 'notice notice-warning';

	private $_emoji_list = array();

	private $_emoji_list_handy = array(
		'slightly_smiling_face',
		'thumbsup',
		'ok_hand',
		'laughing',
		'joy',
	);


	/**
	 * Init
	 *
	 * @since  1.0
	 * @access public
	 */
	public function init() {
		$this->_emoji_list = json_decode( file_get_contents( REMOJI_DIR . 'data/emoji.json' ), true );
		defined( 'debug' ) && debug2( 'Load emoji list', $this->_emoji_list );

		if ( is_admin() ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		if ( Conf::val( 'comment_emoji' ) ) {
			add_filter( 'comment_text', array( $this, 'reaction_bar' ), 10, 2 );
		}

		if ( Conf::val( 'post_emoji' ) ) {
			add_filter( 'the_content', array( $this, 'reaction_bar' ) );
		}

		if ( Conf::val( 'postview' ) ) {
			Postview::get_instance()->init();
		}
		// Load when compiling release
		// foreach ( $this->_emoji_list as $k => $v ) {
		// 	if ( file_exists( REMOJI_DIR . 'data/emoji/' . $v . '.svg' ) ) {
		// 		continue;
		// 	}
		// 	file_put_contents( REMOJI_DIR . 'data/emoji/' . $v . '.svg', file_get_contents( REMOJI_DIR . '../svg_emoji_all/' . $v . '.svg' ) );
		// }
	}

	/**
	 * Show reaction panel
	 *
	 * @since  1.0
	 * @access public
	 */
	public function show_reaction_panel() {
		ob_start();
		include REMOJI_DIR . 'tpl/reaction_popover.tpl.php';
		$content = ob_get_contents();
		ob_end_clean();

		$content = str_replace( array( "\n", "\t" ), '', $content );

		return $content;
	}

	/**
	 * Append emoji bar to comment
	 *
	 * @since  1.0
	 * @access public
	 */
	public function reaction_bar( $content, $comment = null ) {
		if ( $comment != null ) {
			$remoji_type = 'comment';
			$remoji_id = $comment->comment_ID;
			$emoji_list = get_comment_meta( $remoji_id, 'remoji', true );
		}
		else {
			$remoji_type = 'post';
			$remoji_id = get_the_ID();
			$emoji_list = get_post_meta( $remoji_id, 'remoji', true );
		}

		ob_start();
		include REMOJI_DIR . 'tpl/reaction_bar.tpl.php';
		$content .= str_replace( array( "\n", "\r", "\t" ), '', ob_get_contents() );
		ob_end_clean();

		return $content;
	}

	/**
	 * Return one or all emoji
	 *
	 * @since  1.0
	 * @access public
	 */
	public function emoji( $key = false ) {
		if ( ! $key ) {
			return $this->_emoji_list;
		}

		if ( empty( $this->_emoji_list[ $key ] ) ) {
			defined( 'debug' ) && debug( 'missing emoji [key] ' . $key );
			return null;
		}

		return $this->_emoji_list[ $key ];
	}

	/**
	 * Return handy emojis
	 *
	 * @since  1.0
	 * @access public
	 */
	public function emoji_handy() {
		return $this->_emoji_list_handy;
	}

	/**
	 * Enqueue js
	 *
	 * @since  1.0
	 * @access public
	 */
	public function enqueue_scripts() {
		$this->enqueue_style();

		wp_register_script( 'remoji-js', REMOJI_URL . 'assets/remoji.js', array( 'jquery' ), Core::VER, false );

		$localize_data = array();
		$localize_data[ 'show_reaction_panel_url' ] = get_rest_url( null, 'remoji/v1/show_reaction_panel' );
		$localize_data[ 'reaction_submit_url' ] = get_rest_url( null, 'remoji/v1/add' );
		$localize_data[ 'nonce' ] = wp_create_nonce( 'wp_rest' );

		if ( Conf::val( 'postview' ) && is_singular() && ! is_preview() ) {
			// Load ajax count
			$localize_data[ 'postview_url' ] = get_rest_url( null, 'remoji/v1/postview' );
			$localize_data[ 'postview_delay' ] = Conf::val( 'postview_delay' );
			$localize_data[ 'postview_id' ] = get_the_ID();
		}

		wp_localize_script( 'remoji-js', 'remoji', $localize_data );
		wp_enqueue_script( 'remoji-js' );
	}

	/**
	 * Load style
	 *
	 * @since 1.0
	 */
	public function enqueue_style() {
		wp_enqueue_style( 'remoji-css', REMOJI_URL . 'assets/css/remoji.css', array(), Core::VER, 'all' );
	}

	/**
	 * Load css/js for admin
	 *
	 * @since 1.2
	 */
	public function enqueue_admin() {
		// Only enqueue on plugin pages
		if( empty( $_GET[ 'page' ] ) || strpos( $_GET[ 'page' ], 'remoji' ) !== 0 ) {
			return;
		}

		$this->enqueue_style();

		wp_register_script( 'remoji_admin', REMOJI_URL . 'assets/remoji_admin.js', array( 'jquery' ), Core::VER, false );

		wp_enqueue_script( 'remoji_admin' );
	}

	/**
	 * Register this setting to save
	 *
	 * @since  1.2
	 * @access public
	 */
	public function enroll( $id ) {
		echo '<input type="hidden" name="_settings-enroll[]" value="' . $id . '" />';
	}

	/**
	 * Build a textarea
	 *
	 * @since 1.2
	 * @access public
	 */
	public function build_textarea( $id, $cols = false, $val = null ) {
		if ( $val === null ) {
			$val = Conf::val( $id );

			if ( is_array( $val ) ) {
				$val = implode( "\n", $val );
			}
		}

		if ( ! $cols ) {
			$cols = 80;
		}

		$this->enroll( $id );

		echo "<textarea name='$id' rows='9' cols='$cols'>" . esc_textarea( $val ) . "</textarea>";
	}

	/**
	 * Build a text input field
	 *
	 * @since 1.2
	 * @access public
	 */
	public function build_input( $id, $cls = null, $val = null, $type = 'text' ) {
		if ( $val === null ) {
			$val = Conf::val( $id );
		}

		$label_id = preg_replace( '|\W|', '', $id );

		if ( $type == 'text' ) {
			$cls = "regular-text $cls";
		}

		$this->enroll( $id );

		echo "<input type='$type' class='$cls' name='$id' value='" . esc_textarea( $val ) ."' id='input_$label_id' /> ";
	}

	/**
	 * Build a switch div html snippet
	 *
	 * @since 1.2
	 * @access public
	 */
	public function build_switch( $id, $title_list = false ) {
		$this->enroll( $id );

		echo '<div class="remoji-switch">';

		if ( ! $title_list ) {
			$title_list = array(
				__( 'OFF', 'remoji' ),
				__( 'ON', 'remoji' ),
			);
		}

		foreach ( $title_list as $k => $v ) {
			$this->_build_radio( $id, $k, $v );
		}

		echo '</div>';
	}

	/**
	 * Build a radio input html codes and output
	 *
	 * @since 1.2
	 * @access private
	 */
	private function _build_radio( $id, $val, $txt ) {
		$id_attr = 'input_radio_' . preg_replace( '|\W|', '', $id ) . '_' . $val;

		if ( ! is_string( Conf::$_default_options[ $id ] ) ) {
			$checked = (int) Conf::val( $id, true ) === (int) $val ? ' checked ' : '';
		}
		else {
			$checked = Conf::val( $id, true ) === $val ? ' checked ' : '';
		}

		echo "<input type='radio' autocomplete='off' name='$id' id='$id_attr' value='$val' $checked /> <label for='$id_attr'>$txt</label>";
	}

	/**
	 * Build a checkbox
	 *
	 * @access public
	 */
	public function build_checkbox( $id, $title, $checked = null, $value = 1 ) {
		if ( $checked === null && Conf::val( $id, true ) ) {
			$checked = true;
		}
		$checked = $checked ? ' checked ' : '';

		$label_id = preg_replace( '|\W|', '', $id );

		if ( $value !== 1 ) {
			$label_id .= '_' . $value;
		}

		$this->enroll( $id );

		echo "<div class='remoji-tick'>
			<input type='checkbox' name='$id' id='input_checkbox_$label_id' value='$value' $checked />
			<label for='input_checkbox_$label_id'>$title</label>
		</div>";
	}

	/**
	 * Builds a single msg.
	 *
	 * @access private
	 */
	private static function _build_msg( $color, $str ) {
		return '<div class="' . $color . ' is-dismissible"><p>'. $str . '</p></div>';
	}

	/**
	 * Display info notice
	 *
	 * @access public
	 */
	public static function info( $msg, $echo = false ) {
		self::_add_notice( self::NOTICE_BLUE, $msg, $echo );
	}

	/**
	 * Display note notice
	 *
	 * @access public
	 */
	public static function note( $msg, $echo = false ) {
		self::_add_notice( self::NOTICE_YELLOW, $msg, $echo );
	}

	/**
	 * Display success notice
	 *
	 * @access public
	 */
	public static function succeed( $msg, $echo = false ) {
		self::_add_notice( self::NOTICE_GREEN, $msg, $echo );
	}

	/**
	 * Display error notice
	 *
	 * @access public
	 */
	public static function error( $msg, $echo = false ) {
		self::_add_notice( self::NOTICE_RED, $msg, $echo );
	}

	/**
	 * Adds a notice to display on the admin page
	 *
	 * @access private
	 */
	private static function _add_notice( $color, $msg, $echo = false ) {
		// Bypass adding for CLI or cron
		if ( defined( 'DOING_CRON' ) ) {
			// WP CLI will show the info directly
			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				$msg = strip_tags( $msg );
				if ( $color == self::NOTICE_RED ) {
					\WP_CLI::error( $msg );
				}
				else {
					\WP_CLI::success( $msg );
				}
			}
			return;
		}

		if ( $echo ) {
			echo self::_build_msg( $color, $msg );
			return;
		}

		$messages = get_option( self::DB_MSG );

		if ( is_array( $msg ) ) {
			foreach ( $msg as $str ) {
				$messages[] = self::_build_msg( $color, $str );
			}
		}
		else {
			$messages[] = self::_build_msg( $color, $msg );
		}
		update_option( self::DB_MSG, $messages );
	}

	/**
	 * Display admin msg
	 *
	 * @access public
	 */
	public function display_msg() {
		// One time msg
		$messages = get_option( self::DB_MSG );
		if( is_array( $messages ) ) {
			$messages = array_unique( $messages );

			$added_thickbox = false;
			foreach ($messages as $msg) {
				// Added for popup links
				if ( strpos( $msg, 'TB_iframe' ) && ! $added_thickbox ) {
					add_thickbox();
					$added_thickbox = true;
				}
				echo $msg;
			}
		}
		delete_option( self::DB_MSG );

	}

}