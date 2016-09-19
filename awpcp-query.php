<?php

class USIN_AWPCP_Query{
	
	protected $join_set = false;

	public function init(){
		add_filter('usin_db_map', array($this, 'filter_db_map'));
		add_filter('usin_query_join_table', array($this, 'filter_query_joins'), 10, 2);
	}

	public function filter_db_map($db_map){
		$db_map['ads_num'] = array('db_ref'=>'ads_num', 'db_table'=>'awpcp_ads', 'no_ref' => 'true', 'null_to_zero'=>true, 'set_alias'=>true);
		$db_map['ads_paid'] = array('db_ref'=>'ads_paid', 'db_table'=>'awpcp_ads', 'no_ref' => 'true', 'null_to_zero'=>true, 'set_alias'=>true);
		$db_map['last_ad'] = array('db_ref'=>'last_ad', 'db_table'=>'orders', 'nulls_last'=>true, 'cast'=>'DATETIME');
		return $db_map;
	}

	public function filter_query_joins($query_joins, $table){
		global $wpdb;

		if(in_array($table, array('awpcp_ads', 'orders')) && !$this->join_set){
			$query_joins .= " LEFT JOIN (SELECT count(ad_id) as ads_num, MAX(COALESCE(renewed_date,ad_postdate)) as last_ad,".
				" SUM(IF(payment_status = 'Completed', ad_fee_paid, 0)) as ads_paid, user_id FROM {$wpdb->prefix}awpcp_ads ". 
				" GROUP BY user_id) as orders ON $wpdb->users.ID = orders.user_id";
			$this->join_set = true;
		}
		return $query_joins;
	}

}