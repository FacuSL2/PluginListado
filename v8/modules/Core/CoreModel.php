<?php

namespace STM_Listing\Modules\Core;

use WP_Query;

/**
 * Class Model
 *
 * @package STM_Listing\Core\Model
 */
abstract class CoreModel {

	const ORDER_DIRECTIONS = array(
		'_high' => 'DESC',
		'_low'  => 'ASC',
	);

	protected $table;
	protected $query;
	protected $prefix;
	protected $wpdb;

	protected $post_type  = 'listings';
	protected $desc_order = 'DESC';
	protected $asc_order  = 'ASC';

	protected $distance_orderby = 'stm_distance';

	public function __construct() {
		$this->wpdb   = $this->get_wpdb();
		$this->query  = $this->get_query();
		$this->prefix = $this->get_prefix();
	}

	private function get_prefix() {
		return $this->wpdb->prefix;
	}

	private function get_wpdb() {
		if ( $this->wpdb ) {
			return $this->wpdb;
		}
		global $wpdb;

		return $wpdb;
	}

	private function get_query() {
		if ( $this->query && $this->query instanceof WP_Query ) {
			return $this->query;
		}
		$query = new WP_Query();

		return $query;
	}

	public function get_paged() {
		$paged = null;
		if ( isset( $this->query->query_vars['paged'] ) ) {
			$paged = $this->query->query_vars['paged'];
		} elseif ( isset( $_GET['paged'] ) ) {
			$paged = sanitize_text_field( $_GET['paged'] );
		}

		return $paged;
	}

	/**
	 * @param $limit
	 * @param $offset
	 *
	 * @return array|object|\stdClass[]|null
	 */
	public function get_all( $limit = false, $offset = false ) {
		$sql = "SELECT * FROM $this->table";

		if ( false !== $limit ) {
			$sql = $this->wpdb->prepare( $sql . ' LIMIT %d', $limit ); //phpcs:ignore
		}
		if ( false !== $offset ) {
			$sql = $this->wpdb->prepare( $sql . ' OFFSET %d', $offset ); //phpcs:ignore
		}

		$result = $this->wpdb->get_results( $sql ); //phpcs:ignore

		return $result;
	}

	/**
	 * Generate placeholder string for wpdb prepare based on item type
	 * if is_array true will return placeholders as array
	 *
	 * @param       $data
	 * @param false $as_array
	 *
	 * @return string|string[]
	 */
	public function generate_placeholders_for_prepare( $data, $as_array = false ) {
		if ( ! is_array( $data ) ) {
			return $as_array ? array() : '';
		}
		$placeholders = array_map(
			function ( $item ) {
				if ( is_int( $item ) ) {
					return '%d';
				}
				if ( is_float( $item ) ) {
					return '%f';
				}

				return '%s';
			},
			$data
		);
		if ( $as_array ) {
			return $placeholders;
		}

		return join( ',', $placeholders );
	}

	/**
	 * @param int $id
	 */
	public function get_by_id( $id ) {
		$sql = "SELECT * FROM $this->table WHERE $this->table.id = %d";

		$result = $this->wpdb->get_row( $this->wpdb->prepare( $sql, intval( $id ) ) ); //phpcs:ignore

		return $result;
	}

	protected function update( $id, $data ) {
	}

	private function catch_error() {
		if ( $this->wpdb->last_error ) {
			var_dump( $this->wpdb->last_error ); //phpcs:ignore
		}
	}

	/**
	 * @param $id
	 *
	 * @return bool|int|\mysqli_result|resource
	 */
	protected function delete( $id ) {
		$sql = "DELETE FROM $this->table WHERE $this->table.id = %d";

		$result = $this->wpdb->query( $this->wpdb->prepare( $sql, $id ) ); //phpcs:ignore

		return $result;
	}
}
