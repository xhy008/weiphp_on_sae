<?php

namespace Addons\Diy;

use Common\Controller\Addon;

/**
 * 万能页面插件
 *
 * @author 凡星
 */
class DiyAddon extends Addon {
	public $info = array (
			'name' => 'Diy',
			'title' => '万能页面',
			'description' => '可以通过拖拉的方式配置一个3G页面',
			'status' => 1,
			'author' => '凡星',
			'version' => '0.1',
			'has_adminlist' => 1,
			'type' => 1 
	);
	public function install() {
		$install_sql = './Addons/Diy/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/Diy/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}
	
	// 实现的weixin钩子方法
	public function weixin($param) {
	}
}