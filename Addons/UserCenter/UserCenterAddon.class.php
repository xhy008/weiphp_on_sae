<?php

namespace Addons\UserCenter;

use Common\Controller\Addon;

/**
 * 微信用户中心插件
 * 
 * @author 无名
 */
class UserCenterAddon extends Addon {
	public $info = array (
			'name' => 'UserCenter',
			'title' => '微信用户中心',
			'description' => '实现3G首页、微信登录，微信用户绑定，微信用户信息初始化等基本功能',
			'status' => 1,
			'author' => '凡星',
			'version' => '0.1' 
	);
	public $admin_list = array ();
	public function install() {
		return true;
	}
	public function uninstall() {
		return true;
	}
	
	// 实现的weixin钩子方法
	public function weixin($param) {
	}
}