<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: yangweijie <yangweijiester@gmail.com> <code-tech.diandian.com>
// +----------------------------------------------------------------------
namespace Admin\Model;

use Think\Model;

/**
 * 插件模型
 * 
 * @author yangweijie <yangweijiester@gmail.com>
 */
class AddonsModel extends Model {
	
	/**
	 * 查找后置操作
	 */
	protected function _after_find(&$result, $options) {
	}
	protected function _after_select(&$result, $options) {
		foreach ( $result as &$record ) {
			$this->_after_find ( $record, $options );
		}
	}
	/* 自动验证规则 */
	protected $_validate = array(
			array('name', 'require', '插件标识不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
			array('name', '/^[a-zA-Z][\w_]{1,29}$/', '插件标识不合法', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
			array('name', '', '插件已安装，请勿重复安装。或者请先卸载后再安装', self::VALUE_VALIDATE, 'unique', self::MODEL_BOTH),
	);	
	/**
	 * 文件模型自动完成
	 * 
	 * @var array
	 */
	protected $_auto = array (
			array (
					'create_time',
					NOW_TIME,
					self::MODEL_INSERT 
			) 
	);
	
	/**
	 * 获取插件列表
	 * 
	 * @param string $addon_dir        	
	 */
	public function getList($addon_dir = '', $type = 0) {
		if (! $addon_dir)
			$addon_dir = ONETHINK_ADDON_PATH;
		$dirs = array_map ( 'basename', glob ( $addon_dir . '*', GLOB_ONLYDIR ) );
		if ($dirs === FALSE || ! file_exists ( $addon_dir )) {
			$this->error = '插件目录不可读或者不存在';
			return FALSE;
		}
		$addons = array ();
		$where ['name'] = array (
				'in',
				$dirs 
		);
 		$list = $this->where ( $where )->field ( true )->select ();
		foreach ( $list as $addon ) {
			$addon ['is_weixin'] = file_exists ( $addon_dir . $addon ['name'] . '/Model/WeixinAddonModel.class.php' );
			$addon ['uninstall'] = 0;
			$addons [$addon ['name']] = $addon;
		}
		foreach ( $dirs as $value ) {
			if (! isset ( $addons [$value] )) {
				$class = get_addon_class ( $value );
				if (! class_exists ( $class )) { // 实例化插件失败忽略执行
					\Think\Log::record ( '插件' . $value . '的入口文件不存在！' );
					continue;
				}
				$obj = new $class ();
				$addons [$value] = $obj->info;
				if ($addons [$value]) {
					$addons [$value] ['uninstall'] = 1;
					unset ( $addons [$value] ['status'] );
				}
				$addons [$value] ['is_weixin'] = file_exists ( $addon_dir . $value . '/Model/WeixinAddonModel.class.php' );
			}
		}
		foreach ( $addons as $key => $val ) {
			if (($type == 1 && ! $val ['is_weixin']) || ($type == 0 && $val ['is_weixin'])) {
				unset ( $addons [$key] );
			}
		}
		
		int_to_string ( $addons, array (
				'status' => array (
						- 1 => '损坏',
						0 => '禁用',
						1 => '启用',
						null => '未安装' 
				) 
		) );
		$addons = list_sort_by ( $addons, 'uninstall', 'desc' );
		return $addons;
	}
	
	/**
	 * 获取插件的后台列表
	 */
	public function getAdminList() {
		$admin = array ();
		$db_addons = $this->where ( "status=1 AND has_adminlist=1 AND type=0" )->field ( 'title,name' )->select ();
		if ($db_addons) {
			foreach ( $db_addons as $value ) {
				$admin [] = array (
						'title' => $value ['title'],
						'url' => "Addons/adminList?name={$value['name']}" 
				);
			}
		}
		return $admin;
	}
}
