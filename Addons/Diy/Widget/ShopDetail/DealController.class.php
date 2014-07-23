<?php

namespace Addons\Diy\Widget\ShopDetail;

use Addons\Diy\Controller\WidgetController;

class DealController extends WidgetController {
	// widget的说明，必填写
	function info() {
		return array (
				'title' => '商品详情模块', // 必填，显示在选择widget显示的名称
				'icon' => C ( 'TMPL_PARSE_STRING.__IMG__' ) . '/m/icon_detail.png'  // 可为空，获取选择的模板的html代码，为空则使用通用的方法获取
				);
	}
	
	// 模块参数配置
	function param() {
		return '';
	}
	// 模块解析
	function show($widget) {
		if ($widget ['data_param'] == '[id]') {
			$widget ['data_param'] = $_REQUEST ['id'];
			if (isset ( $_REQUEST ['product_id'] )) {
				$widget ['data_param'] = $_REQUEST ['product_id'];
			}
		}
		$map ['id'] = intval ( $widget ['data_param'] );
		
		$product = M ( 'shop_product' )->where ( $map )->find ();
		$this->assign ( 'product', $product );
		
		return $this->getWidgetHtml ( $widget );
	}
}
