<?php
namespace STM_Listing\Modules\Helper;

class AuthorizationHelper {

	const KEY = 'c742159103b472M2QyMDY0OWQwYzNM' . STM_LISTINGS_V;

	public static function decode_key( $encoded ) {
		$system_str = 'qwertyuiopasdfghjklzxcvbnm1234567890QWERTYUIOPASDFGHJKLZXCVBNM=';
		$x          = 0;
		$len        = strlen( $system_str );
		while ( $x++ <= $len ) {
			$tmp    = md5( md5( static::KEY . $system_str[ $x - 1 ] ) . static::KEY );
			$result = str_replace( $tmp[3] . $tmp[6] . $tmp[1] . $tmp[2], $system_str[ $x - 1 ], $encoded );
		}

		return base64_decode( $result );//phpcs:ignore
	}

	public static function encode_key() {
		$result = '';
		$string = base64_encode( AUTH_KEY );//phpcs:ignore

		$array = array();
		$x     = 0;
		$len   = strlen( $string );
		while ( $x++ < $len ) {
			$array[ $x - 1 ] = md5( md5( static::KEY . $string[ $x - 1 ] ) . static::KEY );
			$result          = $result . $array[ $x - 1 ][3] . $array[ $x - 1 ][6] . $array[ $x - 1 ][1] . $array[ $x - 1 ][2];
		}

		return $result;
	}

	public static function check_user_auth() {}
}
