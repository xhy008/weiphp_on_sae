<?php

namespace Addons\Coupon\Controller;

use Home\Controller\AddonsController;

class SnController extends AddonsController {
	var $table = 'sn_code';
	var $addon = 'Coupon';
	function _initialize() {
		parent::_initialize ();
		
		$controller = strtolower ( _CONTROLLER );
		
		$res ['title'] = '优惠券';
		$res ['url'] = addons_url ( 'Coupon://Coupon/lists' );
		$res ['class'] = $controller == 'coupon' ? 'current' : '';
		$nav [] = $res;
		
		$this->assign ( 'nav', $nav );
	}
	function lists() {
		$this->assign ( 'add_button', false );
		$this->assign ( 'del_button', false );
		$this->assign ( 'search_button', false );
		$this->assign ( 'check_all', false );
		
		$model = $this->getModel ( $this->table );
		$page = I ( 'p', 1, 'intval' ); // 默认显示第一页数据
		                                
		// 解析列表规则
		$list_data = $this->_list_grid ( $model );
		unset ( $list_data ['list_grids'] [2] );
		$grids = $list_data ['list_grids'];
		$fields = $list_data ['fields'];
		
		// 搜索条件
		$map ['addon'] = $this->addon;
		$map ['target_id'] = I ( 'target_id' );
		$map ['token'] = get_token ();
		session ( 'common_condition', $map );
		$map = $this->_search_map ( $model, $fields );
		
		$row = empty ( $model ['list_row'] ) ? 20 : $model ['list_row'];
		
		empty ( $fields ) || in_array ( 'id', $fields ) || array_push ( $fields, 'id' );
		$name = parse_name ( get_table_name ( $model ['id'] ), true );
		$data = M ( $name )->field ( empty ( $fields ) ? true : $fields )->where ( $map )->order ( 'id DESC' )->page ( $page, $row )->select ();
		
		/* 查询记录总数 */
		$count = M ( $name )->where ( $map )->count ();
		$list_data ['list_data'] = $data;
		
		// 分页
		if ($count > $row) {
			$page = new \Think\Page ( $count, $row );
			$page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
			$list_data ['_page'] = $page->show ();
		}
		$this->assign ( $list_data );
		// dump($list_data);
		
		$this->display ();
	}
	function del() {
		$model = $this->getModel ( $this->table );
		parent::del ( $model );
	}
	function set_use() {
		$map ['id'] = I ( 'id' );
		$map ['token'] = get_token ();
		$data = M ( $this->table )->where ( $map )->find ();
		if (! $data) {
			$this->error ( '数据不存在' );
		}
		
		if ($data ['is_use']) {
			$data ['is_use'] = 0;
			$data ['use_time'] = '';
		} else {
			$data ['is_use'] = 1;
			$data ['use_time'] = time ();
		}
		
		$res = M ( $this->table )->where ( $map )->save ( $data );
		if ($res) {
			$this->success ( '设置成功' );
		} else {
			$this->error ( '设置失败' );
		}
	}
}
