<?php

namespace Addons\Test\Controller;

use Home\Controller\AddonsController;

class QuestionController extends AddonsController {
	var $model;
	var $test_id;
	function _initialize() {
		parent::_initialize();
		
		$this->model = $this->getModel ( 'test_question' );
		
		$param ['test_id'] = $this->test_id = intval ( $_REQUEST ['test_id'] );
		
		$res ['title'] = '微测试';
		$res ['url'] = addons_url ( 'Test://Test/lists' );
		$res ['class'] = '';
		$nav [] = $res;
		
		$res ['title'] = '题目管理';
		$res ['url'] = addons_url ( 'Test://Question/lists', $param );
		$res ['class'] = 'current';
		$nav [] = $res;
		
		$this->assign ( 'nav', $nav );
	}
	// 通用插件的列表模型
	public function lists() {
		$param ['test_id'] = $this->test_id;
		$param ['model'] = $this->model ['id'];
		$add_url = U ( 'add', $param );
		$this->assign ( 'add_url', $add_url );
		
		$map ['test_id'] = intval ( $_REQUEST ['test_id'] );
		session ( 'common_condition', $map );
		
		parent::common_lists ( $this->model, 0, '', 'sort asc, id asc' );
	}
	function _tip() {
		$normal_tips = '问题参数的录入格式：选项标识:选项内容[#得分][@下一题的ID]，例如：<br/>
A:我喜欢运功[+5][@10]<br/>
B:我不喜欢运功[+2]<br/>
C:我从来没运动<br/><br/>
上面用户选择A时得5分，并且自动跳转到ID为10的问题;选择B时得2分，自动进入下一题;选择C时得0分，自动进入下一题<br/>
问题的ID值可以在问题管理列表中看到。用户选择的最后一题不要设置跳转，否则用户无法进入结束页面';
		$this->assign ( 'normal_tips', $normal_tips );
	}
	
	// 通用插件的编辑模型
	public function edit() {
		$id = I ( 'id' );
		
		if (IS_POST) {
			$Model = D ( parse_name ( get_table_name ( $this->model ['id'] ), 1 ) );
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $this->model ['id'] );
			if ($Model->create () && $Model->save ()) {
				$param ['test_id'] = $this->test_id;
				$param ['model'] = $this->model ['id'];
				$url = U ( 'lists', $param );
				$this->success ( '保存' . $this->model ['title'] . '成功！', $url );
			} else {
				$this->error ( $Model->getError () );
			}
		}
		
		$this->_tip ();
		
		parent::common_edit ( $this->model, $id );
	}
	
	// 通用插件的增加模型
	public function add() {
		if (IS_POST) {
			$Model = D ( parse_name ( get_table_name ( $this->model ['id'] ), 1 ) );
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $this->model ['id'] );
			if ($Model->create () && $id = $Model->add ()) {
				$param ['test_id'] = $this->test_id;
				$param ['model'] = $this->model ['id'];
				$url = U ( 'lists', $param );
				$this->success ( '添加' . $this->model ['title'] . '成功！', $url );
			} else {
				$this->error ( $Model->getError () );
			}
			exit ();
		}
		
		$this->_tip ();
		
		$fields = get_model_attribute ( $this->model ['id'] );
		
		$this->assign ( 'fields', $fields );
		$this->meta_title = '新增' . $this->model ['title'];
		$this->display ();
	}
	
	// 通用插件的删除模型
	public function del() {
		parent::common_del ( $this->model );
	}
}
