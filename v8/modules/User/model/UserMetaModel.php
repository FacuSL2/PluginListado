<?php

namespace STM_Listing\Modules\Users;

use STM_Listing\Modules\Core\CoreModel;

class UserMetaModel extends CoreModel {

	private $fields_sql = '';

	public function __construct() {
		parent::__construct();
		$this->set_table();
		$this->create_sql();
	}

	private function set_table() {
		$this->table = $this->wpdb->base_prefix . 'usermeta';
	}

	private function create_sql() {
		$fields = array(
			'stm_phone',
			'stm_whatsapp_number',
			'stm_show_email',
			'first_name',
			'last_name',
			'stm_user_avatar',
			'stm_user_facebook',
			'stm_user_twitter',
			'stm_user_linkedin',
			'stm_user_youtube',
			'stm_dealer_logo',
			'stm_dealer_image',
			'stm_company_license',
			'stm_website_url',
			'stm_dealer_location',
			'stm_dealer_location_lat',
			'stm_dealer_location_lng',
			'stm_company_name',
			'stm_company_license',
			'stm_message_to_user',
			'stm_sales_hours',
			'stm_seller_notes',
			'stm_payment_status',
		);

		foreach ( $fields as $k => $field ) {
			$this->fields_sql .= " MAX(CASE WHEN meta_key = '$field' THEN meta_value END) AS $field";
			if ( $k < count( $fields ) - 1 ) {
				$this->fields_sql .= ',';
			}
		}
	}

	public function get_data( $user_id ) {
		$sql = "SELECT $this->fields_sql
				FROM $this->table
				WHERE user_id = %d
				";

		return $this->wpdb->get_results( $this->wpdb->prepare( $sql, $user_id ) );//phpcs:ignore
	}
}
