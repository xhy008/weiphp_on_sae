<?php

namespace Addons\Diy\Widget\Footer;

use Addons\Diy\Controller\WidgetController;

class DealController extends WidgetController {
	// widget的说明，必填写
	function info() {
		return array (
				'title' => '底部导航', // 必填，显示在选择widget显示的名称
				'icon' => C ( 'TMPL_PARSE_STRING.__IMG__' ) . '/m/icon_nav.png'  // 可为空，获取选择的模板的html代码，为空则使用通用的方法获取
				);
	}
	
	// 模块参数配置
	function param() {
		return '';
	}
	// 模块解析
	function show($widget) {
		$list = D ( 'Addons://Shop/Footer' )->get_list ();
		
		// 取一级菜单
		foreach ( $list as $k => $vo ) {
			$vo ['url'] = str_replace ( '{site_url}', SITE_URL, $vo ['url'] );
			if ($vo ['pid'] != 0)
				continue;
			
			$one_arr [$vo ['id']] = $vo;
			unset ( $list [$k] );
		}
		
		foreach ( $one_arr as &$p ) {
			$two_arr = array ();
			foreach ( $list as $key => $l ) {
				if ($l ['pid'] != $p ['id'])
					continue;
				
				$two_arr [] = $l;
				unset ( $list [$key] );
			}
			
			$p ['child'] = $two_arr;
		}
		
		$this->assign ( 'footer', $one_arr );
		
		return $this->getWidgetHtml ( $widget );
	}
}
