<?php

namespace Addons\Suggestions;

use Common\Controller\Addon;

/**
 * 建议意见插件
 * 
 * @author 凡星
 */
class SuggestionsAddon extends Addon {
	public $info = array (
			'name' => 'Suggestions',
			'title' => '建议意见',
			'description' => '用户在微信里输入“建议意见”四个字时，返回一个图文信息，引导用户进入填写建议意见的3G页面，用户填写信息提交后显示感谢之意并提示关闭页面返回微信
管理员可以在管理中心里看到用户反馈的内容列表，并对内容进行编辑，删除操作',
			'status' => 1,
			'author' => '凡星',
			'version' => '0.1' 
	);
	public $admin_list = array (
			'model' => 'Suggestions', // 要查的表
			'fields' => '*', // 要查的字段
			'map' => '', // 查询条件, 如果需要可以再插件类的构造方法里动态重置这个属性
			'order' => 'id desc', // 排序,
			'listKey' => array ( // 这里定义的是除了id序号外的表格里字段显示的表头名
					'字段名' => '表头显示名' 
			) 
	);
	public function install() {
		$install_sql = './Addons/Suggestions/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/Suggestions/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}
	
	// 实现的weixin钩子方法
	public function weixin($param) {
	}
}