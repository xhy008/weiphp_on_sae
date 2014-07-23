<?php

namespace Addons\WeiSite\Controller;

use Home\Controller\AddonsController;

class BaseController extends AddonsController {
	var $config;
	function _initialize() {
		parent::_initialize();
		
		$controller = strtolower ( _CONTROLLER );
		
		$res ['title'] = '微网设置';
		$res ['url'] = addons_url ( 'WeiSite://WeiSite/config' );
		$res ['class'] = $controller == 'weisite' ? 'current' : '';
		$nav [] = $res;
		
		$res ['title'] = '分类管理';
		$res ['url'] = addons_url ( 'WeiSite://Category/lists' );
		$res ['class'] = $controller == 'category' ? 'current' : '';
		$nav [] = $res;

		$res ['title'] = '首页幻灯片';
		$res ['url'] = addons_url ( 'WeiSite://Slideshow/lists' );
		$res ['class'] = $controller == 'slideshow' ? 'current' : '';
		$nav [] = $res;
		
		$res ['title'] = '底部导航';
		$res ['url'] = addons_url ( 'WeiSite://Footer/lists' );
		$res ['class'] = $controller == 'footer' ? 'current' : '';
		$nav [] = $res;
		
		$res ['title'] = '文章管理';
		$res ['url'] = addons_url ( 'WeiSite://Cms/lists' );
		$res ['class'] = $controller == 'cms' ? 'current' : '';
		$nav [] = $res;

		$res ['title'] = '模板管理';
		$res ['url'] = addons_url ( 'WeiSite://Template/index' );
		$res ['class'] = $controller == 'template' ? 'current' : '';
		$nav [] = $res;
				
		$this->assign ( 'nav', $nav );
		
		$config = getAddonConfig ( 'WeiSite' );
		$config ['cover_url'] = get_cover_url ( $config ['cover'] );
		$config ['background'] = get_cover_url ( $config ['background'] );
		$this->config = $config;
		$this->assign ( 'config', $config );
		// dump ( $config );
		// dump(get_token());
		
		// 定义模板常量
		$act = strtolower ( _ACTION );
		$temp = $config ['template_' . $act];
		$act = ucfirst ( $act );
		
		define ( 'CUSTOM_TEMPLATE_PATH', ONETHINK_ADDON_PATH . 'WeiSite/View/default/Template');
	}
}
