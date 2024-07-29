<?php


namespace STM_Listing\Modules\Users;

use STM_Listing\Modules\Users\UserMetaModel as UserMetaModel;

class UserMetaController {
	public static function get_usermeta( $user_id ) {

		$model = new UserMetaModel();
		$meta  = $model->get_data( $user_id );

		return self::prepare_meta( $meta );
	}

	protected static function prepare_meta( $meta ) {
		if ( ! empty( $meta['stm_dealer_location'] ) && ! empty( $meta['stm_dealer_location_lat'] ) && ! empty( $meta['stm_dealer_location_lng'] ) ) {
			return (object) array_merge( (array) $meta[0], self::prepare_location_data( $meta['stm_dealer_location'], $meta['stm_dealer_location_lat'], $meta['stm_dealer_location_lng'] ) );
		}

		return ( ! empty( $meta ) ) ? $meta[0] : '';
	}

	private static function prepare_location_data( $location, $lat, $lng ) {
		$location_data = array(
			'distance'      => '',
			'user_location' => '',
		);

		if ( ! empty( $_GET['ca_location'] ) && ! empty( $_GET['stm_lng'] ) && ! empty( $_GET['stm_lat'] ) && ! empty( $lat ) && ! empty( $lng ) ) {//phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$distance                       = apply_filters( 'stm_calculate_distance_between_two_points', '', floatval( $_GET['stm_lat'] ), floatval( $_GET['stm_lng'] ), $lat, $lng );//phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$location_data['distance']      = $distance;
			$current_location               = explode( ',', sanitize_text_field( $_GET['ca_location'] ) );//phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$current_location               = $current_location[0];
			$location_data['user_location'] = $current_location;
		}

		return $location_data;
	}

	public static function get_dealer_display_name( $meta ) {
		$dealer_name = $meta->user_login;
		if ( ! empty( $meta->first_name ) ) {
			$dealer_name  = $meta->first_name;
			$dealer_name .= ( ! empty( $meta->last_name ) ) ? ' ' . $meta->last_name : '';
		}
		$dealer_name = ( ! empty( $meta->stm_company_name ) ) ? $meta->stm_company_name : $dealer_name;

		return apply_filters( 'get_dialer_display_name', $dealer_name );
	}
}
