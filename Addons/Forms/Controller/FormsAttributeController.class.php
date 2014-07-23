<?php

namespace Addons\Forms\Controller;

use Addons\Forms\Controller\BaseController;

class FormsAttributeController extends BaseController {
	var $model;
	var $forms_id;
	function _initialize() {
		parent::_initialize();
		
		$this->model = $this->getModel ( 'forms_attribute' );
		
		$param ['forms_id'] = $this->forms_id = intval ( $_REQUEST ['forms_id'] );
		
		$res ['title'] = '通用表单';
		$res ['url'] = addons_url ( 'Forms://Forms/lists' );
		$res ['class'] = '';
		$nav [] = $res;
		
		$res ['title'] = '字段管理';
		$res ['url'] = addons_url ( 'Forms://FormsAttribute/lists', $param );
		$res ['class'] = 'current';
		$nav [] = $res;
		
		$this->assign ( 'nav', $nav );
	}
	// 通用插件的列表模型
	public function lists() {
		$param ['forms_id'] = $this->forms_id;
		$param ['model'] = $this->model ['id'];
		$add_url = U ( 'add', $param );
		$this->assign ( 'add_url', $add_url );
		
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
				$this->_saveKeyword ( $this->model, $id );
				
				$param ['forms_id'] = $this->forms_id;
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
				$this->_saveKeyword ( $this->model, $id );
				
				$param ['forms_id'] = $this->forms_id;
				$param ['model'] = $this->model ['id'];
				$url = U ( 'lists', $param );
				$this->success ( '添加' . $this->model ['title'] . '成功！', $url );
			} else {
				$this->error ( $Model->getError () );
			}
			exit;
		}
		
		
		$normal_tips = '字段类型为单选、多选、下拉选择的参数格式第行一项，每项的值和标题用英文冒号分开。如：<br/>0:男<br/>1:女<br/>2:保密<br/>';
		$normal_tips .= '字段类型为级联的参数格式有两种：
				<br/>一是数据源从数据库取,如： type=db&table=common_category&module=shop_category 
				<br/>二是手工输入，如： type=text&data=[广西[南宁,桂林], 广东[广州, 深圳[福田区, 龙岗区, 宝安区]]]';
		$this->assign ( 'normal_tips', $normal_tips );
		
		parent::common_add ( $this->model );
	}
	
	// 通用插件的删除模型
	public function del() {
		parent::common_del ( $this->model );
	}
}
