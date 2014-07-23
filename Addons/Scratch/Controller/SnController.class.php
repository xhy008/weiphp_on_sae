<?php

namespace Addons\Scratch\Controller;

use Home\Controller\AddonsController;

class SnController extends AddonsController {
	var $table = 'sn_code';
	var $addon = 'Scratch';
	function _initialize() {
		parent::_initialize ();
		
		$controller = strtolower ( _CONTROLLER );
		
		$res ['title'] = '刮刮卡';
		$res ['url'] = addons_url ( 'Scratch://Scratch/lists' );
		$res ['class'] = $controller == 'scratch' ? 'current' : '';
		$nav [] = $res;
		
		$res ['title'] = '中奖管理';
		$res ['url'] = addons_url ( 'Scratch://Sn/lists' );
		$res ['class'] = $controller == 'sn' ? 'current' : '';
		$nav [] = $res;
		
		$this->assign ( 'nav', $nav );
	}
	function lists() {
		$this->assign ( 'add_button', false );
		$this->assign ( 'del_button', false );
		$this->assign ( 'search_button', false );
		$this->assign ( 'check_all', false );
		
		$model = $this->getModel ( $this->table );
		
		$map ['addon'] = $this->addon;
		$map ['target_id'] = I ( 'target_id' );
		$map ['token'] = get_token ();
		$map ['_string'] = 'prize_id>0';
		session ( 'common_condition', $map );
		
		parent::lists ( $model );
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
