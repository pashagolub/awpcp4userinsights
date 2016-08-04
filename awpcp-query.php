<?php

class USIN_AWPCP_Query{

	protected $order_post_type;
	protected $has_ordered_join_applied = false;
	protected $has_order_status_join_applied = false;

	public function __construct($order_post_type){
		$this->order_post_type = $order_post_type;
	}

	public function init(){
		add_filter('usin_db_map', array($this, 'filter_db_map'));
		add_filter('usin_query_join_table', array($this, 'filter_query_joins'), 10, 2);
		add_filter('usin_custom_query_filter', array($this, 'apply_filters'), 10, 2);
	}

	public function filter_db_map($db_map){
		$db_map['ads_num'] = array('db_ref'=>'ads_num', 'db_table'=>'awpcp_ads', 'no_ref' => 'true', 'null_to_zero'=>true, 'set_alias'=>true);
		$db_map['ads_paid'] = array('db_ref'=>'ads_paid', 'db_table'=>'awpcp_ads', 'no_ref' => 'true', 'null_to_zero'=>true, 'set_alias'=>true);
		$db_map['has_ordered'] = array('db_ref'=>'', 'db_table'=>'postmeta', 'no_select'=>true);
		//$db_map['has_order_status'] = array('db_ref'=>'', 'db_table'=>'orders', 'no_select'=>true);
		$db_map['last_ad'] = array('db_ref'=>'last_ad', 'db_table'=>'orders', 'nulls_last'=>true, 'cast'=>'DATETIME');
		return $db_map;
	}

	public function filter_query_joins($query_joins, $table){
		global $wpdb;

		if($table === 'awpcp_ads'){
			$query_joins .= " LEFT JOIN (SELECT count(ad_id) as ads_num, MAX(COALESCE(renewed_date,ad_postdate)) as last_ad,".
				" SUM(IF(payment_status = 'Completed', ad_fee_paid, 0)) as ads_paid, user_id FROM {$wpdb->prefix}awpcp_ads ". 
				" GROUP BY user_id) as orders ON $wpdb->users.ID = orders.user_id";
		}


		//error_log($query_joins);
		return $query_joins;
	}



	public function apply_filters($custom_query_data, $filter){

		
		if(in_array($filter->operator, array('include', 'exclude'))){
			global $wpdb;
			
			$operator = $filter->operator == 'include' ? '>' : '=';

			if($filter->by == 'has_ordered'){
				
				if(!$this->has_ordered_join_applied){
					//apply the joins only once, even when this type of filter is applied multiple times
					$custom_query_data['joins'] .= 
						" INNER JOIN $wpdb->postmeta AS wpm ON $wpdb->users.ID = wpm.meta_value".
						" INNER JOIN $wpdb->posts AS woop ON wpm.post_id = woop.ID".
						" INNER JOIN ".$wpdb->prefix."woocommerce_order_items AS woi ON woop.ID =  woi.order_id".
						" INNER JOIN ".$wpdb->prefix."woocommerce_order_itemmeta AS woim ON woi.order_item_id = woim.order_item_id";

					$this->has_ordered_join_applied = true;
				}
				

				$custom_query_data['where'] = " AND wpm.meta_key = '_customer_user' AND woim.meta_key = '_product_id'";

				$custom_query_data['having'] = $wpdb->prepare(" AND SUM(woim.meta_value IN (%d)) $operator 0", $filter->condition);


			}elseif($filter->by == 'has_order_status'){

				if(!$this->has_order_status_join_applied){
					//apply the joins only once, even when this type of filter is applied multiple times
					$custom_query_data['joins'] .=
						" INNER JOIN $wpdb->postmeta AS wsm ON $wpdb->users.ID = wsm.meta_value".
						" INNER JOIN $wpdb->posts AS wsp ON wsm.post_id = wsp.ID";

					$this->has_order_status_join_applied = true;
				}


				$custom_query_data['where'] = " AND wsm.meta_key = '_customer_user'";

				$custom_query_data['having'] = $wpdb->prepare(" AND SUM(wsp.post_status IN (%s)) $operator 0", $filter->condition);
			
			}
		}

		return $custom_query_data;
	}

}