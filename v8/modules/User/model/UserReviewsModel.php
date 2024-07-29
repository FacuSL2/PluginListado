<?php

namespace STM_Listing\Modules\Users;

use STM_Listing\Modules\Core\CoreModel;

class UserReviewsModel extends CoreModel {

	private $meta_table = '';

	public function __construct() {
		parent::__construct();
		$this->set_table();
		$this->set_meta_table();
	}

	private function set_table() {
		$this->table = $this->prefix . 'posts';
	}

	private function set_meta_table() {
		$this->meta_table = $this->prefix . 'postmeta';
	}

	public function get_data( $user_id ) {
		$sql = "SELECT posts.ID,
				MAX(CASE WHEN meta_2.meta_key = 'stm_rate_1' THEN meta_2.meta_value ELSE NULL END) as stm_rate_1,
				MAX(CASE WHEN meta_2.meta_key = 'stm_rate_2' THEN meta_2.meta_value ELSE NULL END) as stm_rate_2,
				MAX(CASE WHEN meta_2.meta_key = 'stm_rate_3' THEN meta_2.meta_value ELSE NULL END) as stm_rate_3,
				MAX(CASE WHEN meta_2.meta_key = 'stm_recommended' THEN meta_2.meta_value ELSE NULL END) as stm_recommended
				FROM $this->table AS posts
				LEFT JOIN $this->meta_table AS meta ON meta.post_id = posts.ID
				LEFT JOIN $this->meta_table AS meta_2 ON meta_2.post_id = posts.ID
				WHERE posts.post_type = 'dealer_review' 
				AND posts.post_status = 'publish'
				AND meta.meta_key = 'stm_review_added_on'
				AND meta.meta_value = %d
				GROUP BY posts.ID
				ORDER BY ID ASC";

		return $this->wpdb->get_results( $this->wpdb->prepare( $sql, $user_id ) );//phpcs:ignore
	}
}
