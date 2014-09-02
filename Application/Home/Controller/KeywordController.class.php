<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace Home\Controller;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class KeywordController extends HomeController {
	function _initialize() {
		$act = strtolower ( ACTION_NAME );
		$nav = array ();
		$res ['title'] = '关键词维护';
		$res ['url'] = U ( 'lists' );
		$res ['class'] = $act == 'lists' ? 'current' : '';
		$nav [] = $res;
		
		$this->assign ( 'nav', $nav );
		
	}
	public function lists() {
		// $this->assign ( 'add_button', false );
		$this->assign ( 'search_url', U ( 'lists' ) );
		
		$model = $this->getModel ( 'keyword' );
		
		$page = I ( 'p', 1, 'intval' ); // 默认显示第一页数据
		                                
		// 解析列表规则
		$list_data = $this->_list_grid ( $model );
		$fields = $list_data ['fields'];
		
// 		foreach ( $list_data ['list_grids'] as &$vo ) {
// 			if (isset ( $vo ['href'] )) {
// 				$vo ['href'] = '[DELETE]|删除';
// 			}
// 		}
		
		// 搜索条件
		$map = $this->_search_map ( $model, $fields );
		$map ['token'] = get_token ();
		
		$row = empty ( $model ['list_row'] ) ? 20 : $model ['list_row'];
		
		empty ( $fields ) || in_array ( 'id', $fields ) || array_push ( $fields, 'id' );
		$name = parse_name ( get_table_name ( $model ['id'] ), true );
		$list_data ['list_data'] = M ( $name )->field ( empty ( $fields ) ? true : $fields )->where ( $map )->order ( 'id DESC' )->page ( $page, $row )->select ();
		
		// 分页
		$count = M ( $name )->where ( $map )->count ();
		if ($count > $row) {
			$page = new \Think\Page ( $count, $row );
			$page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
			$list_data ['_page'] = $page->show ();
		}
		
		$addons = M ( 'addons' )->where ( "type=1" )->field ( 'name,title' )->select ();
		foreach ( $addons as $a ) {
			$addonsArr [$a ['name']] = $a ['title'];
		}
		
		foreach ( $list_data ['list_data'] as &$vo ) {
			$vo ['addon'] = $addonsArr [$vo ['addon']];
		}
		
		$this->assign ( $list_data );
		// dump($list_data);
		
		$this->display ( 'Addons/lists' );
	}
	public function del() {
		$model = $this->getModel ( 'keyword' );
		parent::common_del ( $model );
	}
	public function edit() {
		$model = $this->getModel ( 'keyword' );
		parent::common_edit ( $model, 0, 'Addons/edit' );
	}
	public function add() {
		$model = $this->getModel ( 'keyword' );
		
		parent::common_add ( $model, 'Addons/add' );
	}
}