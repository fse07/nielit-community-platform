<?php
/**
 * IP class
 *
 * @since 1.0
 */
namespace remoji;
defined( 'WPINC' ) || exit;

class IP extends Instance {
	protected static $_instance;

	private $_visitor_geo_data = array();

	public static $PREFIX_SET = array(
		'continent',
		'continent_code',
		'country',
		'country_code',
		'subdivision',
		'subdivision_code',
		'city',
		'postal',
	);

	/**
	 * Get visitor's IP
	 *
	 * @since  1.0
	 * @access public
	 */
	public static function me() {
		$_ip = '';
		if ( function_exists( 'apache_request_headers' ) ) {
			$apache_headers = apache_request_headers();
			$_ip = ! empty( $apache_headers['True-Client-IP'] ) ? $apache_headers['True-Client-IP'] : false;
			if ( ! $_ip ) {
				$_ip = ! empty( $apache_headers['X-Forwarded-For'] ) ? $apache_headers['X-Forwarded-For'] : false;
				$_ip = explode( ',', $_ip );
				$_ip = $_ip[ 0 ];
			}

		}

		if ( ! $_ip ) {
			$_ip = ! empty( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : false;
		}

		if ( strpos( $_ip, ',' ) ) {
			$_ip = explode( ',', $_ip );
			$_ip = trim( $_ip[ 0 ] );
		}

		return preg_replace( '/^(\d+\.\d+\.\d+\.\d+):\d+$/', '\1', $_ip );
	}

	/**
	 * Get geolocation info of visitor IP
	 *
	 * @since 1.0
	 * @access public
	 */
	public static function geo( $ip = false ) {
		if ( ! $ip ) {
			$ip = self::me();
		}

		$response = wp_remote_get( "https://www.doapi.us/ip/$ip/json" );

		if ( is_wp_error( $response ) ) {
			return new \WP_Error( 'remote_get_fail', 'Failed to fetch geolocation info', array( 'status' => 404 ) );
		}

		$data = $response[ 'body' ];

		$data = json_decode( $data, true );

		// Build geo data
		$geo_list = array( 'ip' => $ip );
		foreach ( self::$PREFIX_SET as $tag ) {
			$geo_list[ $tag ] = ! empty( $data[ $tag ] ) ? trim( $data[ $tag ] ) : false;
		}

		return $geo_list;
	}

}