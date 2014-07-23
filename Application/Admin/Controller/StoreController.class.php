<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 凡星
// +----------------------------------------------------------------------
namespace Admin\Controller;

/**
 * 在线应用商店
 */
class StoreController extends AdminController {
	var $web_info = array ();
	protected function _initialize() {
		$this->assign ( '__MENU__', $this->getMenus () );
		
		C ( 'SESSION_PREFIX', 'weiphp_home' );
		C ( 'COOKIE_PREFIX', 'weiphp_home_' );
		
		$host_url = urldecode ( $_GET ['callback'] );
		if ($host_url) {
			$this->assign ( 'host_url', $host_url );
			session ( 'host_url', $host_url );
		}
	}
	function index() {
		switch ($_GET ['type']) {
			case 'addon' :
				$remote_url = '/index.php?s=/Admin/Store/lists&type=0';
				break;
			case 'template' :
				$remote_url = '/index.php?s=/Admin/Store/lists&type=1';
				break;
			case 'material' :
				$remote_url = '/index.php?s=/Admin/Store/lists&type=2';
				break;
			case 'diy' :
				$remote_url = '/index.php?s=/Admin/Store/lists&type=1';
				break;
			case 'developer' :
				$remote_url = '/index.php?s=/home/Developer/myApps';
				break;
			case 'help' :
				$remote_url = '/index.php?s=/Admin/Store/help';
				break;
			case 'home' :
				$remote_url = '/index.php?s=/Admin/Store/home';
				break;
			case 'recharge' :
				$remote_url = '/index.php?s=/Admin/Store/recharge';
				break;
			case 'bug' :
				$remote_url = '/index.php?s=/Admin/Store/bug';
				break;
			case 'online_recharge' :
				$remote_url = '/index.php?s=/Admin/Store/online_recharge';
				break;
			default :
				$remote_url = '/index.php?s=/Admin/Store/main';
		}
		
		$this->assign ( 'remote_url', REMOTE_BASE_URL . $remote_url );
		$this->display ();
	}
}
