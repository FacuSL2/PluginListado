<?php

namespace STM_Listing\Modules\Users;

use http\Client\Curl\User;
use STM_Listing\Modules\Core\CoreController;
use STM_Listing\Modules\Users\UserModel as UserModel;
use STM_Listing\Modules\Users\UserMetaController as UserMetaController;
use STM_Listing\Modules\Users\UserReviewsController as UserReviewsController;
use STM_Listing\Modules\Users\UserListingsController as UserListingsController;

/**
 * UserController Class
 */
class UserController extends CoreController {

	public $user_role = 'stm_dealer';
	public $offset    = 0;
	public $limit     = 12;

	public function init() {
		/**
		 * add fillters, actions etc here
		 */
	}

	public static function get_dealers_page_title() {
		$title = esc_html__( 'Displaying Local Car Dealerships', 'stm_vehicles_listing' );

		if ( ! empty( $_GET ) ) {// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$terms = '';
			foreach ( $_GET as $tax => $term ) {// phpcs:ignore WordPress.Security.NonceVerification.Missing
				if ( term_exists( sanitize_title( $term ), sanitize_title( $tax ) ) ) {
					$term_data = get_term_by( 'slug', sanitize_title( $term ), sanitize_title( $tax ) );
					$terms    .= $term_data->name . ' ';
				}
			}

			if ( ! empty( $terms ) ) {
				$title = esc_html__( 'Displaying', 'stm_vehicles_listing' ) . ' <span class="green">' . $terms . '</span> ' . esc_html__( 'Dealerships', 'stm_vehicles_listing' );

				if ( ! empty( $_GET['ca_location'] ) && ! empty( $_GET['stm_lng'] ) && ! empty( $_GET['stm_lat'] ) ) {
					$title = esc_html__( 'Displaying', 'stm_vehicles_listing' ) . ' <span class="green">' . $terms . '</span> ' . esc_html__( 'Dealerships near', 'stm_vehicles_listing' ) . ' <span class="green">' . $location_pretty . '</span>';
				}
			}
		}

		return $title;
	}

	public static function get_dealers( $taxonomy = '', $offset = 0, $limit = 12 ) {
		$model         = new UserModel();
		$left_join     = '';
		$where         = '';
		$include_users = array();

		if ( ! empty( $_GET ) ) {// phpcs:ignore WordPress.Security.NonceVerification.Missing
			foreach ( $_GET as $tax => $term ) {// phpcs:ignore WordPress.Security.NonceVerification.Missing
				if ( term_exists( sanitize_title( $term ), sanitize_title( $tax ) ) ) {
					$term_data  = get_term_by( 'slug', sanitize_title( $term ), sanitize_title( $tax ) );
					$tr         = $tax . '_tr';
					$left_join .= $model->left_join( $tr );
					$where     .= $model->where( $tr, $term_data->term_id );
				}
			}

			$lat    = apply_filters( 'stm_listings_input', null, 'stm_lat' );
			$lng    = apply_filters( 'stm_listings_input', null, 'stm_lng' );
			$radius = apply_filters( 'stm_distance_search_value', 100 );
			$radius = ( ! empty( $radius ) ) ? $radius : 5000;

			if ( empty( $left_join ) && ! empty( floatval( $lat ) ) && ! empty( floatval( $lng ) ) ) {
				$include_users = $model->get_filtered_users_by_location( $model->fields_by_location( $lat, $lng ), $model->join_by_location( $lat ), $model->having_by_location( $lat, $radius ), $model->order_by_location( $lat ) );
			} elseif ( ! empty( $left_join ) && ! empty( $where ) ) {
				$include_users = $model->get_filtered_users( $left_join, $where, $model->fields_by_location( $lat, $lng ), $model->join_by_location( $lat ), $model->having_by_location( $lat, $radius ), $model->order_by_location( $lat ) );
			}

			$include_users = array_column( $include_users, 'ID' );
		}

		if ( isset( $_GET['stm_sort_by'] ) ) {
			switch ( $_GET['stm_sort_by'] ) {
				case 'alphabet':
					$users = $model->get_sorted_users_by_alphabet( 'stm_company_name', $offset, $limit, $include_users );
					break;
				case 'reviews':
					$users = $model->get_sorted_users_by_review( $offset, $limit, $include_users );
					break;
				case 'date':
					$users = $model->get_users( $offset, $limit, 'user_registered', $include_users );
					break;
				case 'cars': //cars number
					$users = $model->get_sorted_users_by_listings( $offset, $limit, $include_users );
					break;
				case 'watches': //popularity
					$users = $model->get_sorted_users_by_listing_views( $offset, $limit, $include_users );
					break;
			}
		} else {
			$users = $model->get_users( $offset, $limit );
		}

		foreach ( $users as $k => $user ) {
			$reviews               = UserReviewsController::get_reviews( $user->ID );
			$meta                  = UserMetaController::get_usermeta( $user->ID );
			$listings              = UserListingsController::get_listings( $user->ID );
			$users[ $k ]           = (object) array_merge( (array) $user, (array) $meta );
			$users[ $k ]->listings = $listings;
			$users[ $k ]->ratings  = $reviews;
		}

		return array(
			'users' => $users,
			'total' => $model->users_total,
		);
	}

	public static function get_dealers_data( $taxonomy = '', $offset = 0, $limit = 12 ) {
		$users = self::get_dealers( $taxonomy, $offset, $limit );

		$button = 'hide';

		if ( $users['total'] > intval( ( $offset + $limit ) ) ) {
			$button = 'show';
		}

		$users['button'] = $button;

		return $users;
	}
}
