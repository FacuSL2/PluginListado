<?php

namespace STM_Listing\Modules\Users;

use STM_Listing\Modules\Core\CoreModel;

use WP_User;
use WP_User_Query;
use WP_Comment;

/**
 * Class UserModel
 */
class UserModel extends CoreModel {

	private $user_meta_table;
	private $post_table;
	private $post_meta_table;
	private $term_relationships_table;
	private $key_capabilities;

	public $user_id;
	public $users_total;

	public function __construct() {
		parent::__construct();
		$this->key_capabilities = $this->wpdb->prefix . 'capabilities';
		$this->set_table();
		$this->set_user_meta_table();
		$this->set_post_table();
		$this->set_postmeta_table();
		$this->set_term_relationship_table();
	}

	private function set_table() {
		$this->table = $this->wpdb->base_prefix . 'users';
	}

	private function set_user_meta_table() {
		$this->user_meta_table = $this->wpdb->base_prefix . 'usermeta';
	}

	private function set_post_table() {
		$this->post_table = $this->prefix . 'posts';
	}

	private function set_postmeta_table() {
		$this->post_meta_table = $this->prefix . 'postmeta';
	}

	private function set_term_relationship_table() {
		$this->term_relationships_table = $this->prefix . 'term_relationships';
	}

	public function get_users( $offset = 0, $limit = 12, $sort = 'ID', $include_users = array() ) {
		$sql = "SELECT SQL_CALC_FOUND_ROWS users.*
				FROM %1s AS users
				LEFT JOIN %2s AS meta ON users.ID = meta.user_id
				WHERE meta.meta_key = '$this->key_capabilities' AND meta.meta_value LIKE '%3s' %4s
				ORDER BY users.%5s ASC LIMIT %6d OFFSET %7d
				";

		$user_in           = ( ! empty( $include_users ) ) ? 'AND users.ID IN (' . implode( ',', $include_users ) . ')' : '';
		$prepare           = $this->wpdb->prepare( $sql, $this->table, $this->user_meta_table, '%stm_dealer%', $user_in, $sort, $limit, $offset );//phpcs:ignore
		$result            = $this->wpdb->get_results( $prepare );//phpcs:ignore
		$this->users_total = $this->wpdb->get_var( 'SELECT FOUND_ROWS()' );

		return $result;
	}

	public function get_filtered_users( $left_join, $where, $distance_fields = '', $distance_join = '', $distance_having = '', $distance_order = '' ) {

		$sql = "SELECT SQL_CALC_FOUND_ROWS DISTINCT u.ID $distance_fields
				FROM $this->table AS u
				JOIN $this->user_meta_table AS um ON um.user_id = u.ID
				RIGHT JOIN $this->post_table AS p ON p.post_author = u.ID AND p.post_type = '$this->post_type' AND p.post_status = 'publish'
				RIGHT JOIN $this->post_meta_table AS pm ON pm.post_id = p.ID AND pm.meta_key = 'car_mark_as_sold' AND pm.meta_value = ''
				$left_join 
				$distance_join
				where um.meta_value LIKE '%1s'
				$where
				$distance_having
			  	$distance_order";

		$prepare           = $this->wpdb->prepare( $sql, '%stm_dealer%' );//phpcs:ignore
		$results           = $this->wpdb->get_results( $prepare );//phpcs:ignore
		$this->users_total = $this->wpdb->get_var( 'SELECT FOUND_ROWS()' );

		return $results;
	}

	public function get_filtered_users_by_location( $distance_fields = '', $distance_join = '', $distance_having = '', $distance_order = '' ) {

		$sql = "SELECT DISTINCT u.ID $distance_fields
				FROM %1s AS u
				$distance_join
				$distance_having
			  	$distance_order";

		$prepare           = $this->wpdb->prepare( $sql, $this->table );//phpcs:ignore
		$results           = $this->wpdb->get_results( $prepare );//phpcs:ignore
		$this->users_total = $this->wpdb->get_var( 'SELECT FOUND_ROWS()' );

		return $results;
	}

	public function get_sorted_users_by_review( $offset = 0, $limit = 12, $include_users = array() ) {
		$sql = "SELECT SQL_CALC_FOUND_ROWS u.*, pm.meta_value AS review_for_user, COUNT(pm.meta_value) AS total_reviews
				FROM $this->table AS u
				LEFT JOIN $this->user_meta_table AS um ON u.ID = um.user_id
				LEFT JOIN $this->post_meta_table AS pm ON u.ID = pm.meta_value AND pm.meta_key = 'stm_review_added_on'
				LEFT JOIN $this->post_table AS p ON p.ID = pm.post_id AND p.post_type = 'dealer_review' AND p.post_status = 'publish'
				WHERE um.meta_value LIKE '%1s' %2s
				GROUP BY u.ID
				ORDER BY total_reviews DESC
				LIMIT %3d OFFSET %4d
				";

		$user_in           = ( ! empty( $include_users ) ) ? ' AND u.ID IN (' . implode( ',', $include_users ) . ') ' : '';
		$prepare           = $this->wpdb->prepare( $sql, '%stm_dealer%', $user_in, $limit, $offset );//phpcs:ignore
		$results           = $this->wpdb->get_results( $prepare );//phpcs:ignore
		$this->users_total = $this->wpdb->get_var( 'SELECT FOUND_ROWS()' );

		return $results;
	}

	public function get_sorted_users_by_alphabet( $sort_type, $offset = 0, $limit = 12, $include_users = array() ) {
		$sql = "SELECT SQL_CALC_FOUND_ROWS users.*, MAX(CASE WHEN meta_2.meta_key = '%1s' THEN meta_2.meta_value ELSE '' END) AS %2s
				FROM %3s AS users
				LEFT JOIN %4s AS meta ON users.ID = meta.user_id
				LEFT JOIN %5s AS meta_2 ON users.ID = meta_2.user_id
				WHERE meta.meta_value LIKE '%6s' %7s
				GROUP BY users.ID
				ORDER BY %8s ASC 
				LIMIT %9d OFFSET %10d
				";

		$user_in           = ( ! empty( $include_users ) ) ? 'AND users.ID IN (' . implode( ',', $include_users ) . ')' : '';
		$prepare           = $this->wpdb->prepare( $sql, $sort_type, $sort_type, $this->table, $this->user_meta_table, $this->user_meta_table, '%stm_dealer%', $user_in, $sort_type, $limit, $offset );//phpcs:ignore
		$results           = $this->wpdb->get_results( $prepare );//phpcs:ignore
		$this->users_total = $this->wpdb->get_var( 'SELECT FOUND_ROWS()' );

		return $results;
	}

	public function get_sorted_users_by_listings( $offset = 0, $limit = 12, $include_users = array() ) {
		$sql = "SELECT SQL_CALC_FOUND_ROWS u.*, 
       				(SELECT COUNT(ID) 
       					FROM $this->post_table 
       					WHERE post_author = u.ID AND post_type = '$this->post_type' AND post_status = 'publish') AS total_cars
				FROM $this->table AS u
				JOIN $this->user_meta_table as um ON u.ID = um.user_id AND um.meta_key = '$this->key_capabilities' AND um.meta_value LIKE '%1s'
				%2s
				GROUP BY u.ID
				ORDER BY total_cars DESC
				LIMIT %3d OFFSET %4d";

		$user_in           = ( ! empty( $include_users ) ) ? ' WHERE u.ID IN (' . implode( ',', $include_users ) . ')' : '';
		$prepare           = $this->wpdb->prepare( $sql, '%stm_dealer%', $user_in, $limit, $offset );//phpcs:ignore
		$results           = $this->wpdb->get_results( $prepare );//phpcs:ignore
		$this->users_total = $this->wpdb->get_var( 'SELECT FOUND_ROWS()' );

		return $results;
	}

	public function get_sorted_users_by_listing_views( $offset = 0, $limit = 12, $include_users = array() ) {
		$sql = "SELECT SQL_CALC_FOUND_ROWS u.*, SUM(pm.meta_value) AS car_views
				FROM $this->table AS u
				JOIN $this->user_meta_table as um ON u.ID = um.user_id 
				LEFT JOIN $this->post_table AS p ON p.post_author = u.ID
				LEFT JOIN $this->post_meta_table AS pm ON pm.post_id = p.ID
				WHERE um.meta_value LIKE '%1s' %2s AND p.post_type = '$this->post_type' AND p.post_status = 'publish' AND pm.meta_key = 'stm_car_views'
				GROUP BY u.ID
				ORDER BY car_views DESC
				LIMIT %3d OFFSET %4d";

		$user_in           = ( ! empty( $include_users ) ) ? 'AND u.ID IN (' . implode( ',', $include_users ) . ')' : '';
		$prepare           = $this->wpdb->prepare( $sql, '%stm_dealer%', $user_in, $limit, $offset );//phpcs:ignore
		$results           = $this->wpdb->get_results( $prepare );//phpcs:ignore
		$this->users_total = $this->wpdb->get_var( 'SELECT FOUND_ROWS()' );

		return $results;
	}

	public function left_join( $key ) {
		return "LEFT JOIN $this->term_relationships_table AS $key ON (p.ID = $key.object_id)";
	}

	public function where( $key, $value ) {
		return " AND $key.term_taxonomy_id = $value ";
	}

	public function fields_by_location( $lat, $lng ) {
		if ( empty( $lat ) ) {
			return '';
		}
		$formula = "6378.137 * ACOS(COS(RADIANS(stm_lat_prefix.meta_value)) 
			* COS(RADIANS($lat)) 
			* COS(RADIANS(stm_lng_prefix.meta_value) - RADIANS($lng)) + SIN(RADIANS(stm_lat_prefix.meta_value)) 
			* SIN(RADIANS($lat)))";

		return ", $formula AS distance";
	}

	public function join_by_location( $lat ) {
		if ( empty( $lat ) ) {
			return '';
		}
		$join  = " JOIN $this->user_meta_table AS stm_lat_prefix ON (u.ID = stm_lat_prefix.user_id AND stm_lat_prefix.meta_key = 'stm_dealer_location_lat')";
		$join .= " JOIN $this->user_meta_table AS stm_lng_prefix ON (u.ID = stm_lng_prefix.user_id AND stm_lng_prefix.meta_key = 'stm_dealer_location_lng') ";

		return $join;
	}

	public function having_by_location( $lat, $radius ) {
		if ( empty( $lat ) ) {
			return '';
		}

		return "HAVING distance <= $radius";
	}

	public function order_by_location( $lat ) {
		if ( empty( $lat ) ) {
			return '';
		}

		return ' ORDER BY distance ASC ';
	}
}
