<?php

namespace STM_Listing\Modules\Post;

use STM_Listing\Modules\Core\CoreModel;
use STM_Listing\Modules\Helper\CleanHelper;

/**
 * Class PostModel
 */
class PostModel extends CoreModel {

	const ORDER_SORT_DATA = array(
		'price'   => array(
			'meta_key' => 'stm_genuine_price',
			'orderby'  => 'meta_value_num',
		),
		'date'    => array(
			'meta_key' => false,
			'orderby'  => 'date',
		),
		'mileage' => array(
			'meta_key' => 'mileage',
			'orderby'  => 'meta_value_num',
		),
		'popular' => array(
			'meta_key' => 'stm_car_views',
			'orderby'  => 'meta_value_num',
		),
	);

	/** @var string post_type in wp_posts*/
	public $posts_per_page      = 9;
	public $default_post_status = 'publish';
	public $default_orderby     = 'date';
	public $default_sort        = 'date_high';

	protected $price_key = 'stm_genuine_price';


	public function __construct() {
		parent::__construct();
		$this->set_table();
	}

	private function set_table() {
		$this->table = $this->wpdb->posts;
	}

	private function get_meta_query_by_filter( $filter_data ) {
		$result = array();
		foreach ( $filter_data as $filter_name => $filter_value ) {
			if ( 'search_radius' === $filter_name ) {
				continue;
			}
			if ( is_array( $filter_value ) && array_key_exists( CleanHelper::BTW_KEY, $filter_value ) ) {
				$value = array();
				if ( 'price' === $filter_name ) {
					$compare = 'BETWEEN';
					if ( isset( $filter_data['price']['min'] ) && isset( $filter_data['price']['max'] ) ) {
						$value[0] = stm_convert_to_normal_price( $filter_data['price']['min'] );
						$value[1] = stm_convert_to_normal_price( $filter_data['price']['max'] );
					} elseif ( isset( $filter_data['price']['max'] ) && ! isset( $filter_data['price']['min'] ) ) {
						$compare = '<=';
						$value   = stm_convert_to_normal_price( $filter_data['price']['max'] );
					} elseif ( isset( $filter_data['price']['min'] ) && ! isset( $filter_data['price']['max'] ) ) {
						$compare = '>=';
						$value   = stm_convert_to_normal_price( $filter_data['price']['min'] );
					}
					$result[] = array(
						array(
							'key'     => $this->price_key,
							'value'   => $value,
							'type'    => 'DECIMAL',
							'compare' => $compare,
						),
					);
					continue;
				}

				$compare = 'BETWEEN';
				if ( isset( $filter_value['min'] ) && isset( $filter_value['max'] ) ) {

					$value[0] = $filter_value['min'];
					$value[1] = $filter_value['max'];

				} elseif ( isset( $filter_value['min'] ) ) {
					$compare = '>=';
					$value   = $filter_value['min'];
				} elseif ( isset( $filter_value['max'] ) ) {
					$compare = '<=';
					$value   = $filter_value['max'];
				}

				$result[] = array(
					'key'     => $filter_name,
					'value'   => $value,
					'type'    => 'DECIMAL',
					'compare' => $compare,
				);
			}
		}

		if ( count( $result ) > 0 ) {
			$result['relation'] = 'AND';
		}
		return $result;
	}

	public function get_order_data( $sort_order, $popular = false ) {
		$result = array(
			'order'   => $this->desc_order,
			'orderby' => $this->default_orderby,
		);

		if ( ! empty( $popular ) && true === $popular ) {
			$result['orderby']  = self::ORDER_SORT_DATA['popular']['orderby'];
			$result['meta_key'] = self::ORDER_SORT_DATA['popular']['meta_key'];
			return $result;
		}

		if ( empty( $sort_order ) ) {
			return $result;
		}

		if ( 'distance_nearby' === $sort_order ) {
			$result['order']   = $this->asc_order;
			$result['orderby'] = $this->distance_orderby;
			return $result;
		}

		if ( preg_match( '/_low|_high/', $sort_order, $matches ) ) {
			$result['meta_key'] = str_replace( $matches[0], '', $sort_order );
			$result['order']    = self::ORDER_DIRECTIONS[ $matches[0] ];

			if ( ! array_key_exists( $result['meta_key'], self::ORDER_SORT_DATA ) ) {
				return $result;
			}
			$data               = self::ORDER_SORT_DATA[ $result['meta_key'] ];
			$result['orderby']  = $data['orderby'];
			$result['meta_key'] = $data['meta_key'];
			if ( false === $data['meta_key'] ) {
				unset( $result['meta_key'] );
			}
		}

		return $result;
	}

	public function get_listing_by_wp_query( $filter_data, $vehicles_ids = array() ) {
		$paged = $this->get_paged();
		if ( array_key_exists( 'paged', $filter_data ) && $filter_data['paged'] ) {
			$paged = $filter_data['paged'];
		}

		$post_status = $this->default_post_status;
		if ( array_key_exists( 'status', $filter_data ) && $filter_data['status'] ) {
			$post_status = $filter_data['status'];
		}

		$query_params = array(
			'post_type'              => $this->post_type,
			'post_status'            => $post_status,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'paged'                  => $paged,
			'posts_per_page'         => $this->posts_per_page,
		);

		/** post type may be different if multilisting TODO need to check*/
		if ( ! empty( $filter_data['post_type'] ) ) {
			$query_params['post_type'] = $filter_data['post_type'];
		}

		if ( ! empty( $filter_data['posts_per_page'] ) ) {
			$query_params['posts_per_page'] = $filter_data['posts_per_page'];
		}

		$popular = false;
		if ( ! empty( $filter_data['popular'] ) ) {
			$popular = $filter_data['popular'];
		}

		$sort_order = $this->default_sort;
		if ( array_key_exists( 'sort_order', $filter_data ) && $filter_data['sort_order'] ) {
			$sort_order = $filter_data['sort_order'];
		}
		$order_data = $this->get_order_data( $sort_order, $popular );

		$query_params = array_merge( $query_params, $order_data );

		$query_params['meta_query'] = $this->get_meta_query_by_filter( $filter_data );

		if ( count( $vehicles_ids ) > 0 ) {
			$query_params['post__in'] = $vehicles_ids;
		}

		$query_params = apply_filters( 'stm_listings_build_query_args', $query_params, $filter_data );

		$posts = $this->query->query( $query_params );
		$count = $this->query->found_posts;

		return array(
			'listing' => $posts,
			'count'   => $count,
		);
	}

	/**
	 * [WIP]
	 * @param $limit
	 * @param $offset
	 *
	 * @return array|object|\stdClass[]|null
	 */
	public function get_listing( $filter_data, $limit = false, $offset = false ) {
		$sql = $this->wpdb->prepare( "SELECT * FROM $this->table WHERE post_type=%s ", $this->post_type ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( false !== $limit ) {
			$sql = $this->wpdb->prepare( $sql . ' LIMIT %d', $limit ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
		if ( false !== $offset ) {
			$sql = $this->wpdb->prepare( $sql . ' OFFSET %d', $offset ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		$result = $this->wpdb->get_results( $sql ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $result;
	}

	/**
	 * @param $limit
	 * @param $offset
	 *
	 * @return array|object|\stdClass[]|null
	 */
	public function get_by_ids( $ids = array() ) {
		if ( ! is_array( $ids ) || empty( $ids ) ) {
			return array();
		}
		$prepare_ids_placeholders = $this->generate_placeholders_for_prepare( $ids );

		$params = array( $this->post_type );
		$params = array_merge( $params, $ids );

		$result = $this->wpdb->get_results(
			$this->wpdb->prepare( //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				"SELECT * FROM $this->table WHERE post_type=%s AND ID IN ( {$prepare_ids_placeholders} )", //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$params //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			)
		);
		return $result;
	}
}
