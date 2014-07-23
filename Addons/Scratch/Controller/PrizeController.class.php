<?php

namespace Addons\Scratch\Controller;

use Home\Controller\AddonsController;

class PrizeController extends AddonsController {
	var $table = 'prize';
	var $addon = 'Scratch';
	function _initialize() {
		parent::_initialize();
		
		$controller = strtolower ( _CONTROLLER );
		
		$res ['title'] = '刮刮卡';
		$res ['url'] = addons_url ( 'Scratch://Scratch/lists' );
		$res ['class'] = $controller == 'scratch' ? 'current' : '';
		$nav [] = $res;
		
		$res ['title'] = '奖品设置';
		$res ['url'] = addons_url ( 'Scratch://Prize/lists' );
		$res ['class'] = $controller == 'prize' ? 'current' : '';
		$nav [] = $res;
		
		$this->assign ( 'nav', $nav );
	}
	function lists() {
		$this->assign ( 'search_button', false );
		$model = $this->getModel ( $this->table );
		
		$target_id = I ( 'target_id' );
		if ($target_id) {
			session ( 'target_id', $target_id );
		} else {
			$target_id = session ( 'target_id' );
		}
		if (! $target_id) {
			$this->error ( '非法访问' );
		}
		
		$map ['target_id'] = $target_id;
		$map ['addon'] = $this->addon;
		$map ['token'] = get_token ();
		session ( 'common_condition', $map );
		
		parent::lists ( $model );
	}
	function add() {
		if (IS_POST) {
			$_POST ['addon'] = $this->addon;
			$_POST ['target_id'] = session ( 'target_id' );
		}
		
		$model = $this->getModel ( $this->table );
		parent::add ( $model );
	}
	function edit() {
		$model = $this->getModel ( $this->table );
		parent::edit ( $model );
	}
	function del() {
		$model = $this->getModel ( $this->table );
		parent::del ( $model );
	}
}
