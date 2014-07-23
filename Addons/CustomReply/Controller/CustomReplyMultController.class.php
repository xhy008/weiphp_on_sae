<?php

namespace Addons\CustomReply\Controller;

use Addons\CustomReply\Controller\BaseController;

class CustomReplyMultController extends BaseController {
	var $model;
	function _initialize() {
		$this->model = $this->getModel ( 'custom_reply_mult' );
		parent::_initialize ();
	}
	// 通用插件的列表模型
	public function lists() {
		$map ['token'] = get_token ();
		
		$page = I ( 'p', 1, 'intval' );
		$row = 20;
		
		$data = M ( 'custom_reply_mult' )->where ( $map )->order ( 'id DESC' )->page ( $page, $row )->select ();
		$count = M ( 'custom_reply_mult' )->where ( $map )->count ();
		
		unset ( $map );
		foreach ( $data as &$vo ) {
			$map ['id'] = array (
					'in',
					$vo ['mult_ids'] 
			);
			$list = M ( 'custom_reply_news' )->field ( 'title' )->where ( $map )->select ();
			$list = getSubByKey ( $list, 'title' );
			$vo ['title'] = implode ( '<br/>', $list );
		}
		$list_data ['list_data'] = $data;
		
		// 分页
		if ($count > $row) {
			$page = new \Think\Page ( $count, $row );
			$page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
			$list_data ['_page'] = $page->show ();
		}
		
		$girds ['field'] [0] = 'keyword';
		$girds ['title'] = '关键词';
		$list_data ['list_grids'] [] = $girds;
		
		$girds ['field'] [0] = 'keyword_type|get_name_by_status';
		$girds ['title'] = '关键词类型';
		$list_data ['list_grids'] [] = $girds;
		
		$girds ['field'] [0] = 'title';
		$girds ['title'] = '图文列表';
		$list_data ['list_grids'] [] = $girds;
		
		$girds ['field'] [0] = 'id';
		$girds ['title'] = '操作';
		$girds ['href'] = '[EDIT]|编辑,[DELETE]|	删除';
		$list_data ['list_grids'] [] = $girds;
		
		$this->assign ( $list_data );
		// dump ( $list_data );
		$this->assign('search_button', false);
		$templateFile = $this->model ['template_list'] ? $this->model ['template_list'] : '';
		$this->display ( $templateFile );
	}
	// 通用插件的编辑模型
	public function edit() {
		if (IS_POST) {
			$ids = array_filter ( $_POST ['ids'] );
			if (count ( $ids ) < 2) {
				$this->error ( '图文数不能少于2条' );
			}
			
			$map ['id'] = intval ( $_GET ['id'] );
			$save ['mult_ids'] = implode ( ',', $ids );
			$save ['keyword'] = I ( 'post.keyword' );
			$save ['keyword_type'] = I ( 'post.keyword_type' );
			M ( 'custom_reply_mult' )->where ( $map )->save ( $save );
			
			$model = $this->getModel ( 'custom_reply_mult' );
			$this->_saveKeyword ( $model, $map ['id'], 'custom_reply_mult' );

			$this->success ( '操作成功', U ( 'lists' ) );
			exit ();
		}
		
		$map ['id'] = intval ( $_GET ['id'] );
		$info = M ( 'custom_reply_mult' )->where ( $map )->find ();
		$this->assign ( 'mult', $info );
		
		$map ['id'] = array (
				'in',
				$info ['mult_ids'] 
		);
		$list = M ( 'custom_reply_news' )->where ( $map )->select ();
		$this->assign ( 'select_news', $list );
		
		$this->add ();
	}
	
	// 通用插件的增加模型
	public function add() {
		if (IS_POST) {
			$ids = array_filter ( $_POST ['ids'] );
			if (count ( $ids ) < 2) {
				$this->error ( '图文数不能少于2条' );
			}
			
			$save ['mult_ids'] = implode ( ',', $ids );
			$save ['keyword'] = I ( 'post.keyword' );
			$save ['keyword_type'] = I ( 'post.keyword_type' );
			$save ['token'] = get_token();
			$map ['id'] = M ( 'custom_reply_mult' )->add ( $save );
			
			$model = $this->getModel ( 'custom_reply_mult' );
			$this->_saveKeyword ( $model, $map ['id'], 'custom_reply_mult' );
			
			$this->success ( '操作成功', U ( 'lists' ) );
			exit ();
		}
		// 使用提示
		$normal_tips = '使用说明：请先在左边通过分类或者搜索出你需要的图文，然后点击“选择“把它增加到右边的列表。';
		$this->assign ( 'normal_tips', $normal_tips );
		
		$map ['token'] = get_token ();
		if (isset ( $_REQUEST ['cate_id'] )) {
			$map ['cate_id'] = intval ( $_REQUEST ['cate_id'] );
		}
		if (isset ( $_REQUEST ['title'] )) {
			$map ['title'] = array (
					'like',
					'%' . htmlspecialchars ( $_REQUEST ['title'] ) . '%' 
			);
		}
		
		$page = I ( 'p', 1, 'intval' ); // 默认显示第一页数据
		$row = 20;

		$data = M ( 'custom_reply_news' )->where ( $map )->order ( 'id DESC' )->page ( $page, $row )->select ();

		/* 查询记录总数 */
		$count = M ( 'custom_reply_news' )->where ( $map )->count ();
		$list_data ['list_data'] = $data;
		
		// 分页
		if ($count > $row) {
			$page = new \Think\Page ( $count, $row );
			$page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
			$list_data ['_page'] = $page->show ();
		}
		
		// 分类数据
		$map ['is_show'] = 1;
		$list = M ( 'weisite_category' )->where ( $map )->field ( 'id,title' )->select ();
		$this->assign ( 'weisite_category', $list );
		
		unset ( $list_data ['list_grids'] );
		$girds ['field'] [0] = 'title';
		$girds ['title'] = '标题';
		$list_data ['list_grids'] [] = $girds;
		
		$girds ['field'] [0] = 'id';
		$girds ['title'] = '操作';
		$girds ['href'] = '';
		$list_data ['list_grids'] [] = $girds;
		
		$this->assign ( $list_data );
		
		$this->display ( T ( 'Addons://' . _ADDONS . '@' . _CONTROLLER . '/add' ) );
	}
	
	// 通用插件的删除模型
	public function del() {
		parent::common_del ( $this->model );
	}
	
	// 获取所属分类
	function getCateData() {
		$map ['is_show'] = 1;
		$map ['token'] = get_token ();
		$list = M ( 'weisite_category' )->where ( $map )->select ();
		foreach ( $list as $v ) {
			$extra .= $v ['id'] . ':' . $v ['title'] . "\r\n";
		}
		return $extra;
	}
}
