<?php

namespace Common\Model;

use Think\Model;

/**
 * 插件配置操作集成
 */
class AddonConfigModel extends Model {
	protected $tableName = 'addons';
	/**
	 * 保存配置
	 */
	function set($addon, $config) {
		$map ['token'] = get_token ();
		if (empty ( $map ['token'] )) {
			return false;
		}
		$info = M ( 'member_public' )->where ( $map )->find ();
		if (! $info) {
			$map ['uid'] = session('mid');
			$addon_config [$addon] = $config;
			$map ['addon_config'] = json_encode ( $addon_config );
			$flag = M ( 'member_public' )->add ( $map );
		} else {
			$addon_config = json_decode ( $info ['addon_config'], true );
			$addon_config [$addon] = $config;
			$flag = M ( 'member_public' )->where ( $map )->setField ( 'addon_config', json_encode ( $addon_config ) );
		}

		return $flag;
	}
	/**
	 * 获取插件配置
	 * 获取的优先级：当前公众号设置》后台默认配置》安装文件上的配置
	 */
	function get($addon) {
		// 当前公众号的设置
		$map ['token'] = get_token ();
		$token_config = M ( 'member_public' )->where ( $map )->getField ( 'addon_config' );
		$token_config = json_decode ( $token_config, true );
		$token_config = ( array ) $token_config [$addon];
		//dump($token_config);
		unset ( $map );
		
		// 后台默认的配置
		$map ['name'] = $addon;
		$addon = M ( 'Addons' )->where ( $map )->find ();
		$addon_config = ( array ) json_decode ( $addon ['config'], true );
		//dump($addon_config);
		
		// 安装文件上的配置
		$file_config = array ();
		$file = ONETHINK_ADDON_PATH . $addon . '/config.php';
		if (file_exists ( $file )) {
			$file_config = include $data->config_file;
		}
		//dump($file_config);
		
		return array_merge ( $file_config, $addon_config, $token_config );
	}
}
?>
