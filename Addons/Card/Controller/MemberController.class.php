<?php

namespace Addons\Card\Controller;

use Addons\Card\Controller\BaseController;

class MemberController extends BaseController {
	var $model;
	function _initialize() {
		$this->model = $this->getModel ( 'card_member' );
		parent::_initialize ();
	}
	// 通用插件的列表模型
	public function lists() {
		// 不显示增加按钮
		// $this->assign ( 'add_button', false );
		
		$map ['token'] = get_token ();
		session ( 'common_condition', $map );
		
		parent::common_lists ( $this->model );
	}
	
	// 通用插件的编辑模型
	public function edit() {
		parent::common_edit ( $this->model );
	}
	
	// 通用插件的增加模型
	public function add() {
		parent::common_add ( $this->model );
	}
	
	// 通用插件的删除模型
	public function del() {
		parent::common_del ( $this->model );
	}
}
