<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: yangweijie <yangweijiester@gmail.com> <code-tech.diandian.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;

/**
 * 扩展后台管理页面
 *
 * @author yangweijie <yangweijiester@gmail.com>
 */
class AddonCategoryController extends AdminController {
	protected $model;
	function _initialize() {
		parent::_initialize ();
		
		$this->model = $this->getModel ( 'addon_category' );
	}
	// 通用插件的列表模型
	public function lists() {
		parent::common_lists ( $this->model, 0, T ( 'Think/lists' ) );
	}
	
	// 通用插件的编辑模型
	public function edit() {
		parent::common_edit ( $this->model, 0, T ( 'Think/edit' ) );
	}
	
	// 通用插件的增加模型
	public function add() {
		parent::common_add ( $this->model, T ( 'Think/add' ) );
	}
	
	// 通用插件的删除模型
	public function del() {
		parent::common_del ( $this->model );
	}
	
	// 插件分类编辑
	function category() {
		$map ['id'] = I ( 'id' );
		if (IS_POST) {
			M ( 'addons' )->where ( $map )->setField ( 'cate_id', I ( 'cate_id' ) );
			$this->success ( '设置成功', U ( 'Admin/Addons/weixin' ) );
			exit ();
		}
		$data = M ( 'addons' )->where ( $map )->find ();
		$this->assign ( 'data', $data );
		// dump ( $data );
		
		$categorys = M ( 'addon_category' )->order ( 'sort asc, id desc' )->select ();
		$this->assign ( 'categorys', $categorys );
		
		$this->display ();
	}
}
