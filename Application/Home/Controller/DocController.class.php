<?php
// +----------------------------------------------------------------------
// | WeiPHP
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.weiphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 凡星 <weiphp@weiphp.cn>
// +----------------------------------------------------------------------
namespace Home\Controller;

use OT\DataDictionary;

/**
 * 文档模型控制器
 * 文档模型列表和详情
 */
class DocController extends HomeController {
	
	/* 文档模型频道页 */
	public function index() {
		/* 分类信息 */
		$category = D ( 'Category' )->getTree ( 43 );
		$html = '<ul class="thinktree-showline thinktree-root">';
		foreach ( $category ['_'] as $cate ) {
			$html .= '<li data-id="20" data-name="onethink_architecture" class="closed">';
			$html .= '<div><i class="tree-icon-switch"></i><i class="tree-icon-item"></i><a href="http://i/doc/index.html">简介</a></div>';
			
			$html .= '</li>';
		}
		$html .= '</ul>';
		dump ( $category );
		exit ();
		
		$html = '
		<li data-id="19" data-name="index" class="active">
        <div><i class="tree-icon-switch"></i><i class="tree-icon-item"></i><a href="http://i/doc/index.html">简介</a></div>
        <ul>
          <li data-id="238" data-name="index2">
            <div><i class="tree-icon-switch"></i><i class="tree-icon-item"></i><a href="http://i/doc/index2.html">安装</a></div>
          </li>
        </ul>
      </li>
				
      <li data-id="20" data-name="onethink_architecture" class="closed">
        <div><i class="tree-icon-switch"></i><i class="tree-icon-item"></i><a href="http://document.onethink.cn/manual_1_0/onethink_architecture.html">架构设计</a></div>
        <ul>
          <li data-id="23" data-name="onethink_dir">
            <div><i class="tree-icon-switch"></i><i class="tree-icon-item"></i><a href="http://document.onethink.cn/manual_1_0/onethink_dir.html">应用架构及目录结构</a></div>
          </li>
          <li data-id="155" data-name="onethink_2_7">
            <div><i class="tree-icon-switch"></i><i class="tree-icon-item"></i><a href="http://document.onethink.cn/manual_1_0/onethink_2_7.html">独立模型</a></div>
          </li>
        </ul>
      </li>';
		
		$this->assign ( 'left_tree', $html );
		
		$this->display ();
	}
	
	/* 文档模型列表页 */
	public function lists($page = 1) {
		/* 分类信息 */
		$category = $this->category ();
		
		/* 获取当前分类列表 */
		$Document = D ( 'Document' );
		$list = $Document->page ( $page, $category ['list_row'] )->lists ( $category ['id'] );
		if (false === $list) {
			$this->error ( '获取列表数据失败！' );
		}
		
		/* 模板赋值并渲染模板 */
		$this->assign ( 'category', $category );
		$this->assign ( 'list', $list );
		$this->display ( $category ['template_lists'] );
	}
	
	/* 文档模型详情页 */
	public function detail($id = 0, $page = 1) {
		/* 标识正确性检测 */
		if (! ($id && is_numeric ( $id ))) {
			$this->error ( '文档ID错误！' );
		}
		
		/* 页码检测 */
		$page = intval ( $page );
		$page = empty ( $page ) ? 1 : $page;
		
		/* 获取详细信息 */
		$Document = D ( 'Document' );
		$info = $Document->detail ( $id );
		if (! $info) {
			$this->error ( $Document->getError () );
		}
		
		/* 分类信息 */
		$category = $this->category ( $info ['category_id'] );
		
		/* 获取模板 */
		if (! empty ( $info ['template'] )) { // 已定制模板
			$tmpl = $info ['template'];
		} elseif (! empty ( $category ['template_detail'] )) { // 分类已定制模板
			$tmpl = $category ['template_detail'];
		} else { // 使用默认模板
			$tmpl = 'Article/' . get_document_model ( $info ['model_id'], 'name' ) . '/detail';
		}
		
		/* 更新浏览数 */
		$map = array (
				'id' => $id 
		);
		$Document->where ( $map )->setInc ( 'view' );
		
		/* 模板赋值并渲染模板 */
		$this->assign ( 'category', $category );
		$this->assign ( 'info', $info );
		$this->assign ( 'page', $page ); // 页码
		$this->display ( $tmpl );
	}
	
	/* 文档分类检测 */
	private function category($id = 0) {
		/* 标识正确性检测 */
		$id = $id ? $id : I ( 'get.category', 0 );
		if (empty ( $id )) {
			$this->error ( '没有指定文档分类！' );
		}
		
		/* 获取分类信息 */
		$category = D ( 'Category' )->info ( $id );
		if ($category && 1 == $category ['status']) {
			switch ($category ['display']) {
				case 0 :
					$this->error ( '该分类禁止显示！' );
					break;
				// TODO: 更多分类显示状态判断
				default :
					return $category;
			}
		} else {
			$this->error ( '分类不存在或被禁用！' );
		}
	}
}
