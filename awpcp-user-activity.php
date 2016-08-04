<?php

class USIN_AWPCP_User_Activity{

	protected $order_post_type;

	public function __construct($order_post_type){
		$this->order_post_type = $order_post_type;
	}

	public function init(){
		add_filter('usin_user_activity', array($this, 'add_orders_to_user_activity'), 10, 2);
		add_action('pre_get_posts', array($this, 'admin_orders_filter'));
	}
	
	public function add_orders_to_user_activity($activity, $user_id){

		$args = array(
			'meta_key'    => '_customer_user',
			'meta_value'  => $user_id,
			'post_type'   => $this->order_post_type,
			'post_status' => 'any',
			'numberposts'=>-1
		);

		$all_orders = get_posts($args);
		$count = sizeof($all_orders);

		$args['numberposts'] = 5;
		$orders = get_posts($args);


		if(!empty($orders)){
			$list = array();
			foreach ($orders as $order) {

				$title = get_the_date( get_option('date_format'), $order->ID);

				if(class_exists('WC_Order')){
					$wc_order = new WC_Order($order->ID);

					$order_status = $wc_order->get_status();
					$order_items = $wc_order->get_items();

					

					if(!empty($order_items)){
						$item_names = wp_list_pluck($order_items, 'name');

						$title .= sprintf(' | %s: %s (%s)', 
							__('Ordered Items', 'usin'), implode(', ', $item_names), $order_status);

					}
					
				}
				
				
				$list[]=array('title'=>$title, 'link'=>get_edit_post_link( $order->ID, ''));
			}

			$post_type_data = get_post_type_object($this->order_post_type);

			$activity[] = array(
				'type' => 'order',
				'for' => $this->order_post_type,
				'label' => $count == 1 ? $post_type_data->labels->singular_name : $post_type_data->labels->name,
				'count' => $count,
				'link' => admin_url('edit.php?post_type=shop_order&usin_customer='.$user_id),
				'list' => $list
			);
		}
		
		return $activity;
	}


	public function admin_orders_filter($query){
		if( is_admin() && isset($_GET['usin_customer']) && $query->get('post_type') == $this->order_post_type){
			$user_id = intval($_GET['usin_customer']);

			if($user_id){
				$query->set('meta_key', '_customer_user');
				$query->set('meta_value', $user_id);
			}
		}
	}
}