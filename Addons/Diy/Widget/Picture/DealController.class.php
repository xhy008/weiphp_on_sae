<?php

namespace Addons\Diy\Widget\Picture;

use Addons\Diy\Controller\WidgetController;

class DealController extends WidgetController {
	// widget的说明，必填写
	function info() {
		return array (
				'title' => '图片模块', // 必填，显示在选择widget显示的名称
				'icon' => C ( 'TMPL_PARSE_STRING.__IMG__' ) . '/m/icon_pic.png', // 可为空，获取选择的模板的html代码，为空则使用通用的方法获取
				'set' => addons_url ( "Diy://Diy/param", array (
						'widget' => 'Picture' 
				) ), // 可为空，点击确定后保存配置的地址，为空则使用通用的方法保存
				'save' => addons_url ( "Diy://widget/saveTemplateCode" ), // 可为空，保存自定义模板的地址，为空则使用通用的方法保存
				'html' => addons_url ( "Diy://Widget/getTemplateCode" )  // 可为空，获取选择的模板的html代码，为空则使用通用的方法获取
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
