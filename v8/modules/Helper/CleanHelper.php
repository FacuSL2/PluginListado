<?php
namespace STM_Listing\Modules\Helper;

class CleanHelper {
	const MIN_KEY = 'min';
	const MAX_KEY = 'max';
	const BTW_KEY = 'between';

	const MIN_MAX_FOR_COMPARED = array(
		'>' => 'min',
		'<' => 'max',
	);

	/**
	 * Sanitize data
	 * @param array() $data
	 * return array()
	 */
	public static function clean_and_parse_request_data( $data ) {
		$result = array();

		foreach ( $data as $filter_name => $filter_value ) {
			$filter_name = sanitize_key( $filter_name );

			if ( 0 === strpos( $filter_name, self::MIN_KEY . '_' ) ) {
				$filter_name  = substr( $filter_name, strlen( self::MIN_KEY . '_' ) );
				$filter_value = array(
					self::MIN_KEY => $filter_value,
					self::BTW_KEY => 1,
				);
			}
			if ( 0 === strpos( $filter_name, self::MAX_KEY . '_' ) ) {
				$filter_name  = substr( $filter_name, strlen( self::MIN_KEY . '_' ) );
				$filter_value = array(
					self::MAX_KEY => $filter_value,
					self::BTW_KEY => 1,
				);
			}
			$category_option_data = OptionsHelper::get_category_option_by_slug( $filter_name );

			/** santize array values */
			if ( is_array( $filter_value ) ) {
				$filter_value = array_map( 'sanitize_text_field', $filter_value );
			} elseif ( array_key_exists( 'numeric', $category_option_data ) && $category_option_data['numeric'] ) {
				/** get range condition from string ( -| < | > ) by min, max and btw values */
				if ( false !== strpos( $filter_value, '-' ) ) {
					$value        = explode( '-', $filter_value );
					$filter_value = array(
						self::MIN_KEY => $value[0],
						self::MAX_KEY => $value[1],
						self::BTW_KEY => 1,
					);
				} elseif ( preg_match( '/>|</', $filter_value, $matches ) ) {
					$value        = str_replace( $matches[0], '', $filter_value );
					$filter_value = array(
						self::MIN_MAX_FOR_COMPARED[ $matches[0] ] => $value,
						self::BTW_KEY => 1,
					);
				}
			} else {
				$filter_value = sanitize_text_field( $filter_value );
				if ( filter_var( $filter_value, FILTER_VALIDATE_BOOLEAN ) ) {
					$filter_value = (bool) $filter_value;
				}
			}

			if ( array_key_exists( $filter_name, $result ) && is_array( $result[ $filter_name ] ) ) {
				$result[ $filter_name ] = array_merge( $result[ $filter_name ], $filter_value );
			} else {
				$result[ $filter_name ] = $filter_value;
			}
		}

		return $result;
	}

}
