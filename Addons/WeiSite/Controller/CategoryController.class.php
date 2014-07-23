<?php

namespace Addons\WeiSite\Controller;

use Addons\WeiSite\Controller\BaseController;

class CategoryController extends BaseController {
	var $model;
	function _initialize() {
		$this->model = $this->getModel ( 'weisite_category' );
		parent::_initialize ();
	}
	// 通用插件的列表模型
	public function lists() {
		// 使用提示
		$normal_tips = '外链为空时默认跳转到该分类的文章列表页面';
		$this->assign ( 'normal_tips', $normal_tips );
				
		$map ['token'] = get_token ();
		session ( 'common_condition', $map );
		
		$list_data = $this->_get_model_list ( $this->model );
		foreach ( $list_data ['list_data'] as &$vo ) {
			$src = get_cover_url ( $vo ['icon'] );
			$vo ['icon'] = empty($src) ? '' : '<img style="background:#ddd" src="' . $src . '" width="50px" >';
		}
		$this->assign ( $list_data );
		//dump ( $list_data );
		
		$templateFile = $this->model ['template_list'] ? $this->model ['template_list'] : '';
		$this->display ( $templateFile );
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
	// 首页
	function index() {
		$this->display ();
	}
	// 分类列表
	function category() {
		$this->display ();
	}
	// 相册模式
	function picList() {
		$this->display ();
	}
	// 详情
	function detail() {
		$this->display ();
	}
}
