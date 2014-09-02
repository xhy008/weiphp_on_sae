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
 * 通用的级联数据管理
 */
class CascadeController extends HomeController {
	var $model;
	function _initialize() {
		$act = strtolower ( ACTION_NAME );
		$nav = array ();
		$res ['title'] = '级联分组';
		$res ['url'] = U ( 'lists' );
		$res ['class'] = $act == 'lists' ? 'current' : '';
		$nav [] = $res;
		
		$this->assign ( 'nav', $nav );
		$this->model = $this->getModel ( 'common_category_group' );
	}
	public function lists() {
		$this->assign ( 'search_url', U ( 'lists' ) );
		$this->assign ( 'check_all', false );
		
		$map['token'] = get_token();
		session ( 'common_condition', $map );
		
		parent::common_lists ( $this->model, 0, 'Addons/lists' );
	}
	public function del() {
		parent::common_del ( $this->model );
	}
	public function edit() {
		$id = I ( 'id' );
		parent::common_edit ( $this->model, $id, 'Addons/edit' );
	}
	public function add() {
		parent::common_add ( $this->model, 'Addons/add' );
	}
	function cascade() {
		$module = I ( 'module' );
		session ( 'common_category_module', $module );
		redirect ( U ( 'home/Category/lists', array (
				'module' => $module 
		) ) );
	}
}