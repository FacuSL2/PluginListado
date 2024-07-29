<?php


namespace STM_Listing\Modules\Users;

use STM_Listing\Modules\Users\UserReviewsModel as UserReviewsModel;

class UserReviewsController {
	public static function get_reviews( $user_id ) {
		$model   = new UserReviewsModel();
		$reviews = $model->get_data( $user_id );

		return ( ! empty( $reviews ) ) ? self::prepare_reviews( $reviews ) : self::get_default_ratings();
	}

	public static function get_default_ratings() {
		return array(
			'average'     => 0,
			'rate1'       => 0,
			'rate1_label' => apply_filters( 'stm_me_get_nuxy_mod', esc_html__( 'Customer Service', 'stm_vehicles_listing' ), 'dealer_rate_1' ),
			'rate2'       => 0,
			'rate2_label' => apply_filters( 'stm_me_get_nuxy_mod', esc_html__( 'Buying Process', 'stm_vehicles_listing' ), 'dealer_rate_2' ),
			'rate3'       => 0,
			'rate3_label' => apply_filters( 'stm_me_get_nuxy_mod', esc_html__( 'Overall Experience', 'stm_vehicles_listing' ), 'dealer_rate_3' ),
			'likes'       => 0,
			'dislikes'    => 0,
			'count'       => 0,
		);
	}

	public static function prepare_reviews( $reviews ) {
		$ratings = self::get_default_ratings();

		foreach ( $reviews as $review ) {
			$ratings['rate1'] += $review->stm_rate_1;
			$ratings['rate2'] += $review->stm_rate_2;
			$ratings['rate3'] += $review->stm_rate_3;

			if ( 'yes' === $review->stm_recommended ) {
				$ratings['likes'] ++;
			}

			if ( 'no' === $review->stm_recommended ) {
				$ratings['dislikes'] ++;
			}

			$ratings['count'] ++;
		}

		$average_num = 0;

		if ( empty( $ratings['rate1_label'] ) ) {
			$ratings['rate1'] = 0;
		} else {
			$ratings['rate1'] = round( $ratings['rate1'] / $ratings['count'], 1 );

			$ratings['rate1_width'] = ( ( $ratings['rate1'] * 100 ) / 5 ) . '%';

			$ratings['average'] = $ratings['average'] + $ratings['rate1'];

			$average_num ++;
		}

		if ( empty( $ratings['rate2_label'] ) ) {
			$ratings['rate2'] = 0;
		} else {
			$ratings['rate2'] = round( $ratings['rate2'] / $ratings['count'], 1 );

			$ratings['rate2_width'] = ( ( $ratings['rate2'] * 100 ) / 5 ) . '%';

			$ratings['average'] = $ratings['average'] + $ratings['rate2'];

			$average_num ++;
		}

		if ( empty( $ratings['rate3_label'] ) ) {
			$ratings['rate3'] = 0;
		} else {
			$ratings['rate3'] = round( $ratings['rate3'] / $ratings['count'], 1 );

			$ratings['rate3_width'] = ( ( $ratings['rate3'] * 100 ) / 5 ) . '%';

			$ratings['average'] = $ratings['average'] + $ratings['rate3'];

			$average_num ++;
		}

		$ratings['average']       = number_format( round( $ratings['average'] / $average_num, 1 ), '1', '.', '' );
		$ratings['average_width'] = ( ( $ratings['average'] * 100 ) / 5 ) . '%';

		if ( empty( $ratings['rate1_label'] ) && empty( $ratings['rate2_label'] ) && empty( $ratings['rate3_label'] ) ) {
			$ratings['average'] = 0;
		}

		return $ratings;
	}
}
