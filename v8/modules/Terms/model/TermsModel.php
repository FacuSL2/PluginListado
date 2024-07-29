<?php
namespace STM_Listing\Modules\Term;

use STM_Listing\Modules\Core\CoreModel;
use STM_Listing\Modules\Terms\TermsController;


/**
 * Class TaxonomyModel
 */
class TermsModel extends CoreModel {
	public $term_parent_meta_key = 'stm_parent';

	public function __construct() {
		parent::__construct();
		$this->set_table();
	}

	private function set_table() {
		$this->table = $this->wpdb->terms;
	}

	public function get_child_term_ids_by_parent( $child_taxonomy = '', $parent_values = array() ) {
		if ( ! is_array( $parent_values ) ) {
			return array();
		}
		if ( empty( $child_taxonomy ) ) {
			return array();
		}

		$placeholders = $this->generate_placeholders_for_prepare( $parent_values );

		$sql  = "SELECT GROUP_CONCAT( t.term_id SEPARATOR ',') ";
		$sql .= "FROM {$this->wpdb->termmeta} as m
			INNER JOIN {$this->table} as t 
				ON t.term_id = m.term_id
			INNER JOIN {$this->wpdb->term_taxonomy} as tt
				ON m.term_id = tt.term_id ";
		$sql .= "WHERE m.meta_key = '{$this->term_parent_meta_key}' ";
		$sql .= "AND m.meta_value IN ({$placeholders}) ";
		$sql .= "AND tt.taxonomy = '{$child_taxonomy}'";

		$sql    = $this->wpdb->prepare( $sql, $parent_values ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$result = $this->wpdb->get_var( $sql ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( is_null( $result ) ) {
			return array();
		}

		return explode( ',', $result );

	}

	public static function get_stm_terms( $taxonomies = array(), $hide_empty = false ) {
		if ( count( $taxonomies ) <= 0 ) {
			return array();
		}

		$result = get_terms(
			array(
				'taxonomy'               => $taxonomies,
				'hide_empty'             => $hide_empty,
				'update_term_meta_cache' => false,
			)
		);
		if ( is_wp_error( $result ) ) {
			return array();
		}
		return $result;
	}

	private function generate_condition_for_object_ids_by_taxonomy_and_slug( $filter_data = array() ) {
		if ( empty( $filter_data ) ) {
			return '';
		}

		$condition_array  = array();
		$condition_string = "( taxonomy.taxonomy IN ('%s') AND t.slug IN ( '%s' ) )";
		foreach ( $filter_data as $filter_name => $filter_value ) {
			/* rename to stm_additional_features **/
			if ( TermsController::FEATURES_META_KEY['arg_name'] === $filter_name ) {
				$filter_name = TermsController::FEATURES_META_KEY['key'];
			}
			if ( is_array( $filter_value ) ) {
				$placeholders           = $this->generate_placeholders_for_prepare( $filter_value );
				$array_condition_string = "( taxonomy.taxonomy IN ('%s') AND t.slug IN ( {$placeholders} ) )";
				$condition_array[]      = $this->wpdb->prepare( $array_condition_string, array_merge( array( $filter_name ), $filter_value ) ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			} else {
				$condition_array[] = $this->wpdb->prepare( $condition_string, array( $filter_name, $filter_value ) ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}
		}
		return ' WHERE ' . implode( ' OR ', $condition_array );
	}

	/**
	 * @param $filter_data
	 * array where key is slug
	 * value contain array of possible values for slug
	 * @return array
	 */
	public function get_object_ids_by_taxonomy_and_slug( $filter_data = array() ) {

		if ( ! is_array( $filter_data ) ) {
			return array();
		}

		$where = $this->generate_condition_for_object_ids_by_taxonomy_and_slug( $filter_data );

		$sql    = "SELECT GROUP_CONCAT( t.object_id SEPARATOR ',') FROM ( ";
		$sql   .= "SELECT r.object_id 
			FROM {$this->wpdb->term_relationships} as r 
			LEFT JOIN {$this->wpdb->term_taxonomy} as taxonomy 
				ON r.term_taxonomy_id = taxonomy.term_taxonomy_id
			LEFT JOIN {$this->table} as t
				ON t.term_id = taxonomy.term_id";
		$sql   .= $where;
		$sql   .= 'GROUP BY r.object_id HAVING COUNT(taxonomy.term_taxonomy_id) = %d';
		$sql   .= ') as t  ';
		$result = $this->wpdb->get_var(
			$this->wpdb->prepare( //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$sql, //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				count( $filter_data )
			)
		);

		if ( is_null( $result ) ) {
			return array();
		}

		return explode( ',', $result );
	}
}
