<?php

if(!defined( 'ABSPATH' )){
	exit;
}

class USIN_AWPCP{

	protected $order_post_type = 'shop_order';

	public function __construct(){
		add_filter('usin_module_options', array($this , 'register_module'));

		if(USIN_Helper::is_plugin_activated('another-wordpress-classifieds-plugin/awpcp.php')){
			add_action('usin_module_options_loaded', array($this, 'init'));
			add_filter('usin_fields', array($this , 'register_fields'));
			add_filter('usin_exclude_post_types', array($this , 'exclude_post_types'));
		}
	}

	public function init(){
		if(usin_module_options()->is_module_active('awpcp')){
			require_once 'awpcp-query.php';
			require_once 'awpcp-user-activity.php';

			$wc_query = new USIN_AWPCP_Query($this->order_post_type);
			$wc_query->init();

			$wc_user_activity = new USIN_AWPCP_User_Activity($this->order_post_type);
			$wc_user_activity->init();
		}
	}

	public function register_module($default_modules){
		if(!empty($default_modules) && is_array($default_modules)){
			$default_modules[]=array(
				'id' => 'awpcp',
				'name' => __('Another WordPress Classifieds', 'usin'),
				'desc' => __('Retrieves and displays data from the Another WordPress Classifieds made by the WordPress users.', 'usin'),
				'allow_deactivate' => true,
				'buttons' => array(
					array('text'=> __('Learn More', 'usin'), 'link'=>'https://usersinsights.com/awpcp-users-data/', 'target'=>'_blank')
				),
				'active' => false
			);
		}
		return $default_modules;
	}

	public function register_fields($fields){
		if(!empty($fields) && is_array($fields)){

			$fields[]=array(
				'name' => __('Ads', 'usin'),
				'id' => 'ads_num',
				'order' => 'ASC',
				'show' => true,
				'fieldType' => 'awpcp',
				'filter' => array(
					'type' => 'number',
					'disallow_null' => true
				),
				'module' => 'awpcp'
			);

			$fields[]=array(
				'name' => __('Last ad', 'usin'),
				'id' => 'last_ad',
				'order' => 'DESC',
				'show' => true,
				'fieldType' => 'awpcp',
				'filter' => array(
					'type' => 'date'
				),
				'module' => 'awpcp'
			);

			$fields[]=array(
				'name' => __('Paid for Ads', 'usin'),
				'id' => 'ads_paid',
				'order' => 'ASC',
				'show' => true,
				'fieldType' => 'awpcp',
				'filter' => array(
					'type' => 'number',
					'disallow_null' => true
				),
				'module' => 'awpcp'
			);


			$fields[]=array(
				'name' => __('Ordered products', 'usin'),
				'id' => 'has_ordered',
				'order' => 'ASC',
				'show' => false,
				'hideOnTable' => true,
				'fieldType' => 'awpcp',
				'filter' => array(
					'type' => 'include_exclude',
					'options' => $this->get_product_options()
				),
				'module' => 'awpcp'
			);

			$fields[]=array(
				'name' => __('Orders status', 'usin'),
				'id' => 'has_order_status',
				'order' => 'ASC',
				'show' => false,
				'hideOnTable' => true,
				'fieldType' => 'awpcp',
				'filter' => array(
					'type' => 'include_exclude',
					'options' => $this->get_order_status_options()
				),
				'module' => 'awpcp'
			);

		}

		return $fields;
	}

	protected function get_product_options(){
		$product_options = array();
		$products = get_posts( array( 'post_type' => 'product', 'posts_per_page' => -1 ) );

		foreach ($products as $product) {
			$product_options[] = array('key'=>$product->ID, 'val'=>$product->post_title);
		}

		return $product_options;
	}

	protected function get_order_status_options(){
		$status_options = array();

		if(function_exists('wc_get_order_statuses')){
			$wc_statuses = wc_get_order_statuses();
			if(!empty($wc_statuses)){
				foreach ($wc_statuses as $key => $value) {
					$status_options[]= array('key'=>$key, 'val'=>$value);
				}
			}
		}

		return $status_options;
	}

	public function exclude_post_types($exclude){
		return array_merge ($exclude,  array('shop_order','shop_order_refund','shop_coupon','shop_webhook', 'product_variation'));
	}
	
}

new USIN_AWPCP();