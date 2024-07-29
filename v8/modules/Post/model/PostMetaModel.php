<?php
namespace STM_Listing\Modules\Post;

use STM_Listing\Modules\Core\CoreModel;

/**
 * Class PostMetaModel
 */
class PostMetaModel extends CoreModel {

	public function __construct() {
		parent::__construct();
		$this->set_table();
	}

	private function set_table() {
		$this->table = $this->prefix . 'postmeta';
	}

	public function get_posts_all_metadata( $post_ids = array(), $is_single = true ) {
		$result = array();
		if ( empty( $post_ids ) || ! is_array( $post_ids ) ) {
			return $result;
		}
		$post_ids = array_map( 'absint', $post_ids );
		$post_ids = esc_sql( $post_ids );

		$placeholders = $this->generate_placeholders_for_prepare( $post_ids );

		$posts_meta = $this->wpdb->get_results(
			$this->wpdb->prepare( //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				"SELECT * FROM {$this->wpdb->postmeta}  WHERE post_id IN (%s) ORDER BY meta_id", //phpcs:ignore
				$placeholders //phpcs:ignore
			)
		);

		foreach ( $posts_meta as $post_meta ) {
			if ( $is_single ) {
				$result[ $post_meta->post_id ][ $post_meta->meta_key ] = maybe_unserialize( $post_meta->meta_value );
				continue;
			}
			/** part for not single */
			if ( array_key_exists( $post_meta->meta_key, $result[ $post_meta->post_id ] )
				&& is_array( $result[ $post_meta->post_id ][ $post_meta->meta_key ] ) ) {
				$result[ $post_meta->post_id ][ $post_meta->meta_key ] = array_merge( $result[ $post_meta->post_id ][ $post_meta->meta_key ], $post_meta->meta_value );
			} else {
				$result[ $post_meta->post_id ][ $post_meta->meta_key ] = array( $post_meta->meta_value );
			}
			$result[ $post_meta->post_id ][ $post_meta->meta_key ] = array( $post_meta->meta_value );

		}

		return $result;
	}
}
