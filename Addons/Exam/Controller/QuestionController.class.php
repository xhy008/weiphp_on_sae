<?php

namespace Addons\Exam\Controller;

use Home\Controller\AddonsController;

class QuestionController extends AddonsController {
	var $model;
	var $exam_id;
	function _initialize() {
		parent::_initialize();
		
		$this->model = $this->getModel ( 'exam_question' );
		
		$param ['exam_id'] = $this->exam_id = intval ( $_REQUEST ['exam_id'] );
		
		$res ['title'] = '微考试';
		$res ['url'] = addons_url ( 'Exam://Exam/lists' );
		$res ['class'] = '';
		$nav [] = $res;
		
		$res ['title'] = '题目管理';
		$res ['url'] = addons_url ( 'Exam://Question/lists', $param );
		$res ['class'] = 'current';
		$nav [] = $res;
		
		$this->assign ( 'nav', $nav );
	}
	// 通用插件的列表模型
	public function lists() {
		$param ['exam_id'] = $this->exam_id;
		$param ['model'] = $this->model ['id'];
		$add_url = U ( 'add', $param );
		$this->assign ( 'add_url', $add_url );
		
		$map ['exam_id'] = $this->exam_id;
		session ( 'common_condition', $map );
		
		parent::common_lists ( $this->model, 0, '', 'sort asc, id asc' );
	}
	
	// 通用插件的编辑模型
	public function edit() {
		$id = I ( 'id' );
		
		if (IS_POST) {
			$Model = D ( parse_name ( get_table_name ( $this->model ['id'] ), 1 ) );
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $this->model ['id'] );
			if ($Model->create () && $Model->save ()) {
				$param ['exam_id'] = $this->exam_id;
				$param ['model'] = $this->model ['id'];
				$url = U ( 'lists', $param );
				$this->success ( '保存' . $this->model ['title'] . '成功！', $url );
			} else {
				$this->error ( $Model->getError () );
			}
		}
		
		parent::common_edit ( $this->model, $id );
	}
	
	// 通用插件的增加模型
	public function add() {
		if (IS_POST) {
			$Model = D ( parse_name ( get_table_name ( $this->model ['id'] ), 1 ) );
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $this->model ['id'] );
			if ($Model->create () && $id = $Model->add ()) {
				$param ['exam_id'] = $this->exam_id;
				$param ['model'] = $this->model ['id'];
				$url = U ( 'lists', $param );
				$this->success ( '添加' . $this->model ['title'] . '成功！', $url );
			} else {
				$this->error ( $Model->getError () );
			}
			exit ();
		}
		
		$normal_tips = '单选、多选的参数格式每行一项，每项的标识和标题用英文冒号分开。如：<br/>A:李清照<br/>B:李白<br/>C:朱自清';
		$this->assign ( 'normal_tips', $normal_tips );
		
		parent::common_add ( $this->model );
	}
	
	// 通用插件的删除模型
	public function del() {
		parent::common_del ( $this->model );
	}
}
