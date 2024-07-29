<?php


namespace STM_Listing\Modules\Users;

use STM_Listing\Modules\Core\CoreModel;

class UserListingsModel extends CoreModel {

	private $meta;
	private $terms;
	private $fields_sql;
	private $join_sql;

	public function __construct() {
		parent::__construct();
		$this->set_table();
		$this->set_meta_table();
		$this->set_terms_table();
		$this->create_sql();
	}

	private function set_table() {
		$this->table = $this->prefix . 'posts';
	}

	private function set_meta_table() {
		$this->meta = $this->prefix . 'postmeta';
	}

	private function set_terms_table() {
		$this->terms = $this->prefix . 'terms';
	}

	private function create_sql() {
		$fields = array(
			array(
				'field' => 'stm_car_views',
				'join'  => 'LEFT JOIN',
			),
		);

		foreach ( $fields as $k => $var ) {
			$field = $var['field'];
			$join  = $var['join'];

			$this->fields_sql .= " m$k.meta_value AS $field";
			if ( $k < count( $fields ) - 1 ) {
				$this->fields_sql .= ',';
			}

			$this->join_sql .= " $join $this->meta as m$k ON m$k.post_id = posts.ID AND m$k.meta_key = '$field'";
		}
	}

	public function get_total( $user_id ) {
		$sql = "SELECT SQL_CALC_FOUND_ROWS ID
				FROM $this->table
				WHERE post_author = %1d AND post_type = '$this->post_type' AND post_status = 'publish' LIMIT 1";

		$this->wpdb->get_results( $this->wpdb->prepare( $sql, $user_id ) );//phpcs:ignore
		return $this->wpdb->get_var( 'SELECT FOUND_ROWS()' );
	}

	public function get_data( $user_id ) {
		$sql = "SELECT DISTINCT posts.ID, $this->fields_sql
				FROM $this->table as posts 
				$this->join_sql
				WHERE posts.post_author = %1d AND posts.post_type = '$this->post_type' AND posts.post_status = 'publish'";

		return $this->wpdb->get_results( $this->wpdb->prepare( $sql, $user_id ) );//phpcs:ignore
	}

	public function get_listing_term_data( $ids, $taxonomy ) {
		$meta_keys = '';
		foreach ( $taxonomy as $tax ) {
			$meta_keys .= " AND meta_key = '$tax'";
		}

		$sql = "SELECT DISTINCT meta.meta_value
			FROM $this->meta as meta
			WHERE post_id IN (%1s)
			$meta_keys";

		return $this->wpdb->get_results( $this->wpdb->prepare( $sql, implode( ',', $ids ) ) );//phpcs:ignore
	}

	public function get_terms_name( $terms_slug ) {

		$sql     = "SELECT DISTINCT name FROM $this->terms WHERE slug IN ('%s')";
		$prepare = $this->wpdb->prepare( $sql, implode( "','", $terms_slug ) );//phpcs:ignore

		return $this->wpdb->get_results( wp_unslash( $prepare ) );//phpcs:ignore
	}
}
