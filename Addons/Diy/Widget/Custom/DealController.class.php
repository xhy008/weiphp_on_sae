<?php

namespace Addons\Diy\Widget\Custom;

use Addons\Diy\Controller\WidgetController;

class DealController extends WidgetController {
	// widget的说明，必填写
	function info() {
		return array (
				'title' => '自定义模块', // 必填，显示在选择widget显示的名称
				'icon' => C ( 'TMPL_PARSE_STRING.__IMG__' ) . '/m/icon_custom.png'  // 可为空，获取选择的模板的html代码，为空则使用通用的方法获取
		);
	}
	
	// 模块参数配置
	function param() {
		return '';
	}
	// 模块解析
	function show($widget) {
		return $this->getWidgetHtml ( $widget );
	}
}
