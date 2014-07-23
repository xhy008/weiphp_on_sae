<?php

namespace Addons\Diy\Widget\ShopCategory;

use Addons\Diy\Controller\WidgetController;

class DealController extends WidgetController {
	// widget的说明，必填写
	function info() {
		return array (
				'title' => '商品分类模块', // 必填，显示在选择widget显示的名称
				'icon' => C ( 'TMPL_PARSE_STRING.__IMG__' ) . '/m/icon_store.png'  // 可为空，获取选择的模板的html代码，为空则使用通用的方法获取
				);
	}
	
	// 模块参数配置
	function param() {
		return '';
	}
	// 模块解析
	function show($widget) {
		$map ['module'] = 'shop_category';
		$map ['token'] = get_token ();
		
		if ($widget ['data_from'] == 0) {
			// 全部分类
			$map ['pid'] = intval ( $_REQUEST ['cate_id'] );
		} elseif ($widget ['data_from'] == 1) {
			// 指定分类
			$map = getIdsForMap ( $widget ['data_ids'], $map );
		}
		
		$list = M ( 'common_category' )->where ( $map )->order ( 'sort asc' )->select ();
		$this->assign ( 'category_list', $list );
		// dump ( $widget );
		return $this->getWidgetHtml ( $widget );
	}
}
