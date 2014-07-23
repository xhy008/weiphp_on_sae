<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace Home\Controller;

/**
 * 讨论区配置
 * 讨论区bbs
 */
class ForumController extends HomeController {	
	// bbs首页
	public function index($name = 'Forum', $temp = 'index') {
		! isset ( $_GET ['model'] ) || $name = I ( 'model', 'Forum' );
		! isset ( $_GET ['temp'] ) || $name = I ( 'temp', 'index' );
		
		$model = M ( 'Model' )->getByName ( $name );
		$this->assign ( 'model', $model );
		// dump ( $model );
		
		$this->right_data ( $model );
		
		unset ( $map );
		$page = I ( 'p', 1, 'intval' );
		$row = empty ( $model ['list_row'] ) ? 20 : $model ['list_row'];
		! isset ( $_GET ['cid'] ) || $map ['cid'] = intval ( $_GET ['cid'] );
		$list_data ['list_data'] = M ( $name )->where ( $map )->order ( 'is_top desc, id DESC' )->page ( $page, $row )->select ();
		
		// 分页
		$count = M ( $name )->where ( $map )->count ();
		if ($count > $row) {
			$page = new \Think\Page ( $count, $row );
			$page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
			$list_data ['_page'] = $page->show ();
		}
		$this->assign ( $list_data );
		// dump($list_data);
		
		$this->display ( $temp );
	}
	function form() {
		if (! is_login ()) {
			Cookie ( '__forward__', $_SERVER ['REQUEST_URI'] );
			$this->error ( '您还没有登录，请先登录！', U ( 'User/login' ) );
		}
		
		$model = M ( 'Model' )->find ( I ( 'get.model' ) );
		$this->assign ( 'model', $model );
		$id = I ( 'id', 0 );
		
		$this->right_data ( $model );
		
		if (IS_POST) {
			$Model = D ( parse_name ( get_table_name ( $model ['id'] ), 1 ) );
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $model ['id'] );
			
			$res = $Model->create ();
			if ($id) {
				$res = $Model->save ();
			} else {
				$res = $Model->add ();
			}
			if ($res) {
				$url = U ( 'index' );
				if ($model ['name'] == 'store') {
					$url = U ( 'store' );
				}
				$this->success ( '保存成功！', $url );
			} else {
				$this->error ( $Model->getError () );
			}
		} else {
			$fields = get_model_attribute ( $model ['id'] );
			$this->assign ( 'fields', $fields );
			
			// 获取数据
			if ($id) {
				$data = M ( get_table_name ( $model ['id'] ) )->find ( $id );
				$data || $this->error ( '数据不存在！' );
				$this->assign ( 'data', $data );
			}
			
			$this->meta_title = '编辑' . $model ['title'];
			
			$this->display ();
		}
	}
	function right_data($model) {
		$map ['model_id'] = $model ['id'];
		$map ['name'] = 'cid';
		$cate = M ( 'attribute' )->where ( $map )->getField ( 'extra' );
		$cate = preg_split ( '/[;\r\n]+/s', $cate );
		foreach ( $cate as $k => $vo ) {
			$arr = explode ( ':', $vo );
			$cateArr [$k] ['cid'] = $arr [0];
			$cateArr [$k] ['name'] = $arr [1];
		}
		$this->assign ( 'cateArr', $cateArr );
		$this->assign ( 'topic_count', M ( 'forum' )->count () );
	}
	// bbs详情页
	public function topic($name = 'Forum', $temp = 'topic') {
		! isset ( $_GET ['model'] ) || $name = I ( 'model', 'Forum' );
		
		$model = M ( 'Model' )->getByName ( $name );
		$this->assign ( 'model', $model );
		
		$this->right_data ( $model );
		
		$map ['id'] = I ( 'id', 0 );
		$dao = M ( get_table_name ( $model ['id'] ) );
		
		$data = $dao->find ( $map ['id'] );
		$data || $this->error ( '数据不存在！' );
		$this->assign ( 'data', $data );
		
		$dao->where ( $map )->setInc ( 'view_count' );
		
		$this->display ( $temp );
	}
	// 插件商店
	public function store() {
		$this->index ( 'Store', 'store' );
	}
	public function store_detail() {
		$this->topic ( 'Store', 'store_detail' );
	}
	function download() {
		/* 获取附件ID */
		$id = I ( 'get.attach' );
		if (empty ( $id ) || ! is_numeric ( $id )) {
			$this->error ( '附件ID无效！' );
		}
		
		M ( 'store' )->where ( 'id=' . I ( 'id', 0 ) )->setInc ( 'download_count' );
		
		/* 下载附件 */
		$Attachment = D ( 'File' );
		$config = C ( 'DOWNLOAD_UPLOAD' );
		if (false === $Attachment->download ( $config ['rootPath'], $id )) {
			$this->error ( $Attachment->getError () );
		}
	}
}