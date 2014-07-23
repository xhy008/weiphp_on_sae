<?php

namespace Addons\Survey;

use Common\Controller\Addon;

/**
 * 微调研插件
 * 
 * @author 凡星
 */
class SurveyAddon extends Addon {
	public $info = array (
			'name' => 'Survey',
			'title' => '微调研',
			'description' => '实现通用的调研功能，支持单选、多选和简答三种题目的录入',
			'status' => 1,
			'author' => '凡星',
			'version' => '0.1',
			'has_adminlist' => 1,
			'type' => 1 
	);
	public function install() {
		$install_sql = './Addons/Survey/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/Survey/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}
	
	// 实现的weixin钩子方法
	public function weixin($param) {
	}
}