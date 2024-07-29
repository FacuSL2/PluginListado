<?php
namespace STM_Listing\Modules\Post;

use STM_Listing\Modules\Core\CoreController;
use STM_Listing\Modules\Helper\CleanHelper;
use STM_Listing\Modules\Terms\TermsController;

/** PostController Class */
class PostController extends CoreController {

	public function init() {}

	/**
	* @param array $filter_data -
	*   $filter_data contain
	*  - dynamic options data from option
	*  - 'stm_vehicle_listing_options' ( get_option( 'stm_vehicle_listing_options' ) )
	*  - as_html ( default false )
	*  - paged
	* - status ( default = publish )
	* - posts_per_page - ( default = 9 )
	* - sort_order - ( default = date_high| possible values - PostModel ORDER_SORT_DATA)
	* - popular - used for sorting ( default = false )
	*
	* @param false $with_meta - will add to each post object post_meta data
	*
	* @return array of post objects
	*/
	public static function get_filtered_listing( $filter_data = array(), $with_meta = false ) {
		$filter_data  = CleanHelper::clean_and_parse_request_data( $filter_data );
		$vehicles_ids = TermsController::get_filtered_vehicle_ids( $filter_data );

		$post_model = new PostModel();
		$results    = $post_model->get_listing_by_wp_query( $filter_data, $vehicles_ids );

		if ( true === $with_meta ) {
			$post_ids = array_column( $results['listing'], 'ID' );

			$postmeta_model = new PostMetaModel();
			$post_meta      = $postmeta_model->get_posts_all_metadata( $post_ids );

			self::append_postmeta_to_post( $post_meta, $results['listing'] );
		}
		return $results;
	}

	/**
	 * @param $post_meta
	 * @param $listing
	 *
	 * @return array
	 */
	protected static function append_postmeta_to_post( $post_meta, $listing ) {
		if ( empty( $post_meta ) ) {
			return $listing;
		}

		$result = array();
		foreach ( $listing as $post_key => $post ) {
			$result[ $post_key ]            = $post;
			$result[ $post_key ]->post_meta = array();
			if ( array_key_exists( $post->ID, $post_meta ) ) {
				$result[ $post_key ]->post_meta = $post_meta[ $post->ID ];
			}
		}
		return $result;
	}

	public static function get_post_media( $post ) {
		if ( empty( $post ) ) {
			return array();
		}
		$image_limit = '';

		if ( apply_filters( 'stm_pricing_enabled', false ) ) {
			$user_added = ( array_key_exists( 'stm_car_user', $post->post_meta ) ) ? $post->post_meta['stm_car_user'] : '';
			if ( ! empty( $user_added ) ) {
				$limits      = apply_filters(
					'stm_get_post_limits',
					array(
						'premoderation' => true,
						'posts_allowed' => 0,
						'posts'         => 0,
						'images'        => 0,
						'role'          => 'user',
					),
					$user_added
				);
				$image_limit = $limits['images'];
			}
		}
		$car_media = array();

		// Photo.
		$car_photos         = array();
		$car_gallery        = ( array_key_exists( 'gallery', $post->post_meta ) ) ? $post->post_meta['gallery'] : '';
		$car_videos_posters = ( array_key_exists( 'gallery_videos_posters', $post->post_meta ) ) ? $post->post_meta['gallery_videos_posters'] : '';

		if ( has_post_thumbnail( $post->ID ) ) {
			$car_photos[] = wp_get_attachment_url( get_post_thumbnail_id( $post->ID ) );
		}

		if ( ! empty( $car_gallery ) ) {
			$i = 0;
			foreach ( $car_gallery as $car_gallery_image ) {
				if ( empty( $image_limit ) ) {
					if ( wp_get_attachment_url( $car_gallery_image ) ) {
						$car_photos[] = wp_get_attachment_url( $car_gallery_image );
					}
				} else {
					$i ++;
					if ( $i < $image_limit ) {
						if ( wp_get_attachment_url( $car_gallery_image ) ) {
							$car_photos[] = wp_get_attachment_url( $car_gallery_image );
						}
					}
				}
			}
		}

		$car_photos = array_unique( $car_photos );

		$car_media['car_photos']       = $car_photos;
		$car_media['car_photos_count'] = count( $car_photos );

		// Video.
		$car_video      = array();
		$car_video_main = ( array_key_exists( 'gallery_video', $post->post_meta ) ) ? $post->post_meta['gallery_video'] : '';
		$car_videos     = ( array_key_exists( 'gallery_videos', $post->post_meta ) ) ? $post->post_meta['gallery_videos'] : '';

		if ( ! empty( $car_video_main ) ) {
			$car_video[] = $car_video_main;
		}

		if ( ! empty( $car_videos ) ) {
			foreach ( $car_videos as $car_video_single ) {
				if ( ! empty( $car_video_single ) ) {
					$car_video[] = $car_video_single;
				}
			}
		}

		$car_media['car_videos']         = $car_video;
		$car_media['car_videos_posters'] = $car_videos_posters;
		$car_media['car_videos_count']   = count( $car_video );

		return $car_media;
	}

	public static function search( $search ) {

	}
}
