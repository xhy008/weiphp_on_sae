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
class CreditDataController extends HomeController {
	function _initialize() {
		$act = strtolower ( CONTROLLER_NAME );
		$nav = array ();
		$res ['title'] = '积分配置';
		$res ['url'] = U ( 'CreditConfig/lists' );
		$res ['class'] = $act == 'creditconfig' ? 'current' : '';
		$nav [] = $res;
		
		$res ['title'] = '积分流水帐';
		$res ['url'] = U ( 'CreditData/lists' );
		$res ['class'] = $act == 'creditdata' ? 'current' : '';
		$nav [] = $res;
		
		$res ['title'] = '粉丝积分';
		$res ['url'] = U ( 'CreditFollow/lists' );
		$res ['class'] = $act == 'creditfollow' ? 'current' : '';
		$nav [] = $res;
		
		$this->assign ( 'nav', $nav );
		
		$_GET['sidenav'] = 'home_creditconfig_lists';
	}
	public function lists() {		
		$model = $this->getModel ( 'credit_data' );
		
		$map ['token'] = get_token ();
		if(!empty($_GET['uid'])){
			$map['uid'] = intval($_GET['uid']);
		}elseif (! empty ( $_REQUEST ['nickname'] )) {
			$user_map ['nickname'] = array (
					'like',
					'%' . htmlspecialchars ( $_REQUEST ['nickname'] ) . '%' 
			);
		}
		
		if (! isset ( $map ['uid'] )) {
			$map ['uid'] = array (
					'exp',
					'>0' 
			);
		}
		
		if (! empty ( $_REQUEST ['credit_name'] )) {
			$map ['credit_name'] = safe ( $_REQUEST ['credit_name'] );
		}
		
		if (! empty ( $_REQUEST ['start_time'] ) && ! empty ( $_REQUEST ['end_time'] )) {
			$map ['cTime'] = array (
					'between',
					'"' . intval ( $_REQUEST ['start_time'] ) . ',' . intval ( $_REQUEST ['start_time'] ) . '"' 
			);
		} elseif (! empty ( $_REQUEST ['start_time'] )) {
			$map ['cTime'] = array (
					'egt',
					intval ( $_REQUEST ['start_time'] ) 
			);
		} elseif (! empty ( $_REQUEST ['end_time'] )) {
			$map ['cTime'] = array (
					'elt',
					intval ( $_REQUEST ['end_time'] ) 
			);
		}
		
		session ( 'common_condition', $map );
		
		$list_data = $this->_get_model_list ( $model );
		foreach ( $list_data ['list_data'] as &$vo ) {
			$vo ['nickname'] = get_nickname ( $vo ['uid'] );
		}
		$this->assign ( $list_data );
		// dump($list_data);
		
		$this->display ();
	}
	public function add() {
		$model = $this->getModel ( 'credit_data' );
		if (IS_POST) {
			$Model = D ( parse_name ( get_table_name ( $model ['id'] ), 1 ) );
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $model ['id'] );
			if ($Model->create () && $id = $Model->add ()) {
				$this->_saveKeyword ( $model, $id );
				
				$this->success ( '添加' . $model ['title'] . '成功！', U ( 'lists?model=' . $model ['name'] ) );
			} else {
				$this->error ( $Model->getError () );
			}
		} else {
			$fields = get_model_attribute ( $model ['id'] );
			
			$this->assign ( 'fields', $fields );
			$this->meta_title = '新增' . $model ['title'];
			
			$this->display ( 'Addons/add' );
		}
	}
	public function edit($id = 0) {
		$model = $this->getModel ( 'credit_data' );
		$id || $id = I ( 'id' );
		
		// 获取数据
		$data = M ( get_table_name ( $model ['id'] ) )->find ( $id );
		$data || $this->error ( '数据不存在！' );
		
		if (IS_POST) {
			$act = 'save';
			if ($data ['token'] == 0) {
				$_POST ['token'] = get_token ();
				unset ( $_POST ['id'] );
				$act = 'add';
			}
			$Model = D ( parse_name ( get_table_name ( $model ['id'] ), 1 ) );
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $model ['id'] );
			if ($Model->create () && $Model->$act ()) {
				// dump($Model->getLastSql());
				$this->success ( '保存' . $model ['title'] . '成功！', U ( 'lists?model=' . $model ['name'] ) );
			} else {
				// dump($Model->getLastSql());
				$this->error ( $Model->getError () );
			}
		} else {
			$fields = get_model_attribute ( $model ['id'] );
			
			$this->assign ( 'fields', $fields );
			$this->assign ( 'data', $data );
			$this->meta_title = '编辑' . $model ['title'];
			
			$this->display ( 'Addons/edit' );
		}
	}
	function del() {
		$model = $this->getModel ( 'credit_data' );
		parent::common_del ( $model );
	}
	function credit_data() {
		$model = $this->getModel ( 'credit_data' );
		
		$map ['token'] = get_token ();
		session ( 'common_condition', $map );
		
		parent::common_lists ( $model, 0, 'Addons/lists' );
	}
}