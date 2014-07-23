<?php

namespace Addons\Leaflets\Controller;

use Home\Controller\AddonsController;

class LeafletsController extends AddonsController {
	function _initialize() {
		parent::_initialize();
		
		$config = getAddonConfig ( 'Leaflets' );
		$config ['img'] = is_numeric ( $config ['img'] ) ? get_cover_url ( $config ['img'] ) : SITE_URL . '/Addons/Leaflets/View/default/Public/qrcode_default.jpg';
		$this->assign ( 'config', $config );
		// dump($config);
	}
	// 通用设置插件模型
	public function config() {
		$this->getModel ();
		
		if (IS_POST) {
			$flag = D ( 'Common/AddonConfig' )->set ( _ADDONS, I ( 'config' ) );
			
			if ($flag !== false) {
				$this->success ( '保存成功', Cookie ( '__forward__' ) );
			} else {
				$this->error ( '保存失败' );
			}
		}
		
		$map ['name'] = _ADDONS;
		$addon = M ( 'Addons' )->where ( $map )->find ();
		if (! $addon)
			$this->error ( '插件未安装' );
		$addon_class = get_addon_class ( $addon ['name'] );
		if (! class_exists ( $addon_class ))
			trace ( "插件{$addon['name']}无法实例化,", 'ADDONS', 'ERR' );
		$data = new $addon_class ();
		$addon ['addon_path'] = $data->addon_path;
		$addon ['custom_config'] = $data->custom_config;
		$this->meta_title = '设置插件-' . $data->info ['title'];
		$db_config = D ( 'Common/AddonConfig' )->get ( _ADDONS );
		$addon ['config'] = include $data->config_file;
		if ($db_config) {
			foreach ( $addon ['config'] as $key => $value ) {
				if ($value ['type'] != 'group') {
					! isset ( $db_config [$key] ) || $addon ['config'] [$key] ['value'] = $db_config [$key];
				} else {
					foreach ( $value ['options'] as $gourp => $options ) {
						foreach ( $options ['options'] as $gkey => $value ) {
							! isset ( $db_config [$key] ) || $addon ['config'] [$key] ['options'] [$gourp] ['options'] [$gkey] ['value'] = $db_config [$gkey];
						}
					}
				}
			}
		}
		$this->assign ( 'data', $addon );
		
		$this->display ();
	}
	function show() {
		$this->display ();
	}
}
