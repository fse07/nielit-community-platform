<?php
/**
 * Admin class
 *
 * @since 1.2
 */
namespace remoji;
defined( 'WPINC' ) || exit;

class Admin extends Instance {
	protected static $_instance;

	/**
	 * Init admin
	 *
	 * @since  1.2
	 * @access public
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_filter( 'plugin_action_links_remoji/remoji.php', array( $this, 'add_plugin_links' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		add_action( 'admin_enqueue_scripts', array( GUI::get_instance(), 'enqueue_admin' ) ) ;

	}

	/**
	 * Admin setting page
	 *
	 * @since  1.2
	 * @access public
	 */
	public function admin_menu() {
		add_options_page( 'Remoji', 'Remoji', 'manage_options', 'remoji', array( $this, 'setting_page' ) );
	}

	/**
	 * admin_init
	 *
	 * @since  1.2.2
	 * @access public
	 */
	public function admin_init() {
		if ( get_transient( 'remoji_activation_redirect' ) ) {
			delete_transient( 'remoji_activation_redirect' );
			if ( ! is_network_admin() && ! isset( $_GET['activate-multi'] ) ) {
				wp_safe_redirect( menu_page_url( 'remoji', 0 ) );
			}
		}

		add_action( 'admin_notices', array( GUI::get_instance(), 'display_msg' ) );

		add_filter( 'manage_edit-post_columns', array( $this, 'post_row_title' ) );
		add_action( 'manage_posts_custom_column' , array( $this, 'post_row_data' ) );
		add_filter( 'manage_edit-post_sortable_columns', array( $this, 'post_row_sortable' ) );
	}

	/**
	 * Post Admin Menu -> Postview Column Title
	 *
	 * @since 2.0
	 * @access public
	 */
	public function post_row_title( $posts_columns ) {
		$posts_columns[ 'postview' ] = __( 'Postview', 'remoji' );

		return $posts_columns;
	}

	/**
	 * Post Admin Menu -> Postview Column List
	 *
	 * @since 2.0
	 * @access public
	 */
	public function post_row_data( $column ) {
		if ( $column == 'postview' ) {
			global $post;
			echo Postview::get_instance()->get_num( $post->ID );
		}
	}

	/**
	 * Post Admin Menu -> Postview Column sortable
	 *
	 * @since 2.0
	 * @access public
	 */
	public function post_row_sortable( $columns ) {
		$columns[ 'postview' ] = __( 'Postview', 'remoji' );
		return $columns;
	}

	/**
	 * Plugin link
	 *
	 * @since  1.1
	 * @access public
	 */
	public function add_plugin_links( $links ) {
		$links[] = '<a href="' . menu_page_url( 'remoji', 0 ) . '">' . __( 'Settings', 'remoji' ) . '</a>';

		return $links;
	}

	/**
	 * Display and save options
	 *
	 * @since  1.2
	 * @access public
	 */
	public function setting_page() {
		Data::get_instance()->tb_create( 'history' );

		if ( ! empty( $_POST ) ) {
			check_admin_referer( 'remoji' );

			$raw_data = self::cleanup_text( $_POST );

			// Save options
			$list = array();

			foreach ( Conf::get_instance()->get_options() as $id => $v ) {
				if ( $id == '_ver' ) {
					continue;
				}

				$list[ $id ] = ! empty( $raw_data[ $id ] ) ? $raw_data[ $id ] : false;
			}

			foreach ( $list as $id => $v ) {
				Conf::update( $id, $v );
			}

			GUI::succeed( __( 'Options saved successfully!', 'remoji' ), true );
		}

		require_once REMOJI_DIR . 'tpl/entry.tpl.php';
	}

	/**
	 * Clean up the input string of any extra slashes/spaces.
	 *
	 * @access public
	 */
	public static function cleanup_text( $input )
	{
		if ( is_array( $input ) ) {
			return array_map( __CLASS__ . '::cleanup_text', $input );
		}

		return stripslashes( trim( $input ) );
	}

	/**
	 * Sanitize list
	 *
	 * @since  1.2
	 * @access public
	 */
	private function _sanitize_list( $list ) {
		if ( ! is_array( $list ) ) {
			$list = explode( "\n", trim( $list ) );
		}

		foreach ( $list as $k => $v ) {
			$list[ $k ] = implode( ', ', array_map( 'trim', explode( ',', $v ) ) );
		}

		return array_filter( $list );
	}

}