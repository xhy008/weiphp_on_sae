<?php

namespace Addons\Card\Controller;

use Home\Controller\AddonsController;

class BaseController extends AddonsController {
	function _initialize() {
		parent::_initialize();
		
		$controller = strtolower ( _CONTROLLER );
		
		$res ['title'] = '会员卡制作';
		$res ['url'] = addons_url ( 'Card://Card/config' );
		$res ['class'] = $controller == 'card' ? 'current' : '';
		$nav [] = $res;
		
		$res ['title'] = '会员管理';
		$res ['url'] = addons_url ( 'Card://member/lists' );
		$res ['class'] = $controller == 'member' ? 'current' : '';
		$nav [] = $res;
		
		$res ['title'] = '通知管理';
		$res ['url'] = addons_url ( 'Card://notice/lists' );
		$res ['class'] = $controller == 'notice' ? 'current' : '';
		$nav [] = $res;
		
		$res ['title'] = '优惠券';
		$res ['url'] = addons_url ( 'Coupon://Coupon/lists' );
		$res ['class'] = $controller == 'coupon' ? 'current' : '';
		$nav [] = $res;
		
		$this->assign ( 'nav', $nav );
		
		$config = getAddonConfig ( 'Card' );
		$config ['background_url'] = $config ['background'] == 11 ? $config ['background_custom'] : ADDON_PUBLIC_PATH . '/card_bg_' . $config ['background'] . '.png';
		$this->assign ( 'config', $config );
		//dump ( $config );
		//dump(get_token());
	}
}
