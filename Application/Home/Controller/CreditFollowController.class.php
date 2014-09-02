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
class CreditFollowController extends HomeController {
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
		$this->assign ( 'add_button', false );
		$this->assign ( 'del_button', false );
		$this->assign ( 'check_all', false );
		$this->assign ( 'search_button', false );
		
		$model = $this->getModel ( 'follow' );
		
		$map ['token'] = get_token ();
		if (! empty ( $_REQUEST ['nickname'] )) {
			$map ['nickname'] = array (
					'like',
					'%' . htmlspecialchars ( $_REQUEST ['nickname'] ) . '%' 
			);
		}
		
		$list_data = M ( 'follow' )->where ( $map )->order ( 'id DESC' )->selectPage ();
		
		$grid ['field'] [0] = 'id';
		$grid ['title'] = '粉丝编号';
		$list_data ["list_grids"] [] = $grid;
		
		$grid ['field'] [0] = 'openid';
		$grid ['title'] = 'Openid';
		$list_data ["list_grids"] [] = $grid;
		
		$grid ['field'] [0] = 'nickname';
		$grid ['title'] = '昵称';
		$list_data ["list_grids"] [] = $grid;
		
		$grid ['field'] [0] = 'score';
		$grid ['title'] = '财富值';
		$list_data ["list_grids"] [] = $grid;
		
		$grid ['field'] [0] = 'experience';
		$grid ['title'] = '经验值';
		$list_data ["list_grids"] [] = $grid;
		
		$grid ['field'] [0] = 'id';
		$grid ['title'] = '详情';
		
		$varController = C ( 'VAR_CONTROLLER' );
		
		$grid ['href'] = 'CreditData/lists?uid=[id]&target=_blank|详情';
		$list_data ["list_grids"] [] = $grid;
		
		$this->assign ( $list_data );
		
		$this->display ( 'Addons/lists' );
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
		
		$token = get_token ();
		if (isset ( $data ['token'] ) && $token != $data ['token'] && defined ( 'ADDON_PUBLIC_PATH' )) {
			$this->error ( '非法访问！' );
		}		
		
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