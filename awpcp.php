<?php

if(!defined( 'ABSPATH' )){
	exit;
}

class USIN_AWPCP{

	public function __construct(){
		add_filter('usin_module_options', array($this , 'register_module'));

		if(USIN_Helper::is_plugin_activated('another-wordpress-classifieds-plugin/awpcp.php')){
			add_action('usin_module_options_loaded', array($this, 'init'));
			add_filter('usin_fields', array($this , 'register_fields'));
		}
	}

	public function init(){
		if(usin_module_options()->is_module_active('awpcp')){
			require_once 'awpcp-query.php';
			require_once 'awpcp-user-activity.php';

			$wc_query = new USIN_AWPCP_Query();
			$wc_query->init();

			$wc_user_activity = new USIN_AWPCP_User_Activity();
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

		}

		return $fields;
	}

}

new USIN_AWPCP();