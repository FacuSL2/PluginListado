<?php


namespace STM_Listing\Modules\Users;

use STM_Listing\Modules\Users\UserListingsModel as UserListingsModel;

class UserListingsController {

	public static function get_listings_count( $user_id ) {
		$model = new UserListingsModel();
		return $model->get_total( $user_id );
	}

	public static function get_listings( $user_id ) {
		$model    = new UserListingsModel();
		$listings = $model->get_data( $user_id );

		return $listings;
	}

	public static function get_terms_title( $listing_ids, $tax_slug, $tax_name ) {
		$model = new UserListingsModel();
		$terms = $model->get_listing_term_data( $listing_ids, $tax_slug );

		if ( empty( $terms ) ) {
			return '';
		}

		$terms_slug = array();

		foreach ( $terms as $term ) {
			$term       = explode( ',', $term->meta_value );
			$terms_slug = array_merge( $terms_slug, $term );
		}

		$terms_name = array_intersect( array_unique( $terms_slug ), $tax_name );
		$terms_name = array_column( $model->get_terms_name( $terms_name ), 'name' );

		return implode( '/', $terms_name );
	}
}
