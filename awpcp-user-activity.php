<?php

class USIN_AWPCP_User_Activity{


	public function init(){
		add_filter('usin_user_activity', array($this, 'add_enabled_ads_to_user_activity'), 10, 2);
		add_filter('usin_user_activity', array($this, 'add_disabled_ads_to_user_activity'), 10, 2);
	}

	public function add_enabled_ads_to_user_activity( $activity, $user_id ){
		return $this->add_ads_to_user_activity( $activity, $user_id, 0);
	}

	public function add_disabled_ads_to_user_activity( $activity, $user_id ){
		return $this->add_ads_to_user_activity( $activity, $user_id, 1);	
	}	

	public function add_ads_to_user_activity( $activity, $user_id, $disabled ){

		$args = array(  'context' => array( 'public-listings', 'latest-listings-widget' ),
						'orderby' => 'renewed-date',
						'limit' => -1,
						'user_id' => $user_id,
						'disabled' => $disabled
						);

		if ( function_exists( 'awpcp_listings_collection' ) ) {
			$items = awpcp_listings_collection()->find_listings_with_query( $args );			
		}

		$count = count( $items );
		if ( $count > 10 ) {
			array_splice( $items, 10 ); //output only 10 last ads		
		}
			
		if( !empty( $items ) ){
			$list = array();
			
			foreach ( $items as $awpcp_ad ) { 

				if ( $awpcp_ad->ad_category_parent_id ) {
					$category_name = get_adcatname( $awpcp_ad->ad_category_parent_id ) . ' → ';
				}
				$category_name .= get_adcatname( $awpcp_ad->ad_category_id );
				$title = "{$category_name}: \"{$awpcp_ad->ad_title}\"";
				$link = add_query_arg( array( 'action' => 'view', 'id' => $awpcp_ad->ad_id ), awpcp_get_user_panel_url() );
				
				$list[] = array( 'title'=>$title, 'link'=>$link );
			}

			$activity[] = array(
				'type' => 'ads',
				//'for' => $this->order_post_type,
				'label' => ( $disabled ? 'Disabled ' : 'Enabled ' ) . 'Classifieds',
				'count' => $count,
				'link' => admin_url('admin.php?page=awpcp-panel'),
				'list' => $list
			);
		}
		
		return $activity;
	}

}