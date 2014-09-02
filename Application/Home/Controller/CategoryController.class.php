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
 * 通用的级联数据管理
 */
class CategoryController extends HomeController {
	var $model;
	protected static $allow = array (
			'tree' 
	);
	function _initialize() {
		$act = strtolower ( ACTION_NAME );
		$nav = array ();
		$res ['title'] = '级联分组';
		$res ['url'] = U ( 'home/cascade/lists' );
		$nav [] = $res;
		
		$res ['title'] = '数据管理';
		$res ['url'] = U ( 'lists', 'module=' . $_GET ['module'] );
		$res ['class'] = 'current';
		$nav [] = $res;
		
		$this->assign ( 'nav', $nav );
		
		$this->model = $this->getModel ( 'common_category' );
		$_GET['sidenav'] = 'home_cascade';
	}
	public function lists() {
		$tree = D ( 'Category' )->getTree ( 0 );
		// dump($tree);exit;
		$this->assign ( 'tree', $tree );
		
		C ( '_SYS_GET_CATEGORY_TREE_', true ); // 标记系统获取分类树模板
		
		$map ['name'] = I ( 'module' );
		$group = M ( 'common_category_group' )->where ( $map )->find (  );
		$level_path = '';
		for($i = 1; $i < $group['level']; $i ++) {
			$level_path .= ' dd';
		}
		$this->assign ( 'level_path', $level_path ); // 最多允许增加的层级
		$this->assign ( 'group', $group );
		
		$this->display ( 'Addons/category' );
	}
	public function del() {
		parent::common_del ( $this->model );
	}
	// 导出数据
	function exportData() {
		$ht = array (
				'编号',
				'标识',
				'标题',
				'排序',
				'上级编号' 
		);
		$map ['module'] = I ( 'module' );
		$list = M ( 'common_category' )->where ( $map )->field ( 'id,name,title,sort,pid' )->limit ( 10000 )->select ();
		$arr [0] = $ht;
		if (! empty ( $list ))
			$arr = array_merge ( $arr, $list );
		
		outExcel ( $arr, $map ['module'] );
	}
	// 导入数据
	function inputData() {
		$module = I ( 'get.module' );
		if (IS_POST) {
			$attach = I ( 'attach', 0 );
			if (empty ( $attach ) || ! is_numeric ( $attach )) {
				$this->error ( '上传文件ID无效！' );
			}
			$file = M ( 'file' )->where ( 'id=' . $attach )->find ();
			$root = C ( 'DOWNLOAD_UPLOAD.rootPath' );
			$filename = $root . $file ['savepath'] . $file ['savename'];
			
			if (! file_exists ( $filename )) {
				$this->error ( '上传的文件失败' );
			}
			$extend = $file ['ext'];
			if (! ($extend == 'xls' || $extend == 'xlsx')) {
				$this->error ( '文件格式不对，请上传xls,xlsx格式的文件' );
			}
			
			vendor ( 'PHPExcel' );
			vendor ( 'PHPExcel.PHPExcel_IOFactory' );
			vendor ( 'PHPExcel.Reader.Excel5' );
			
			$format = strtolower ( $extend [count ( $extend ) - 1] ) == 'xls' ? 'Excel5' : 'excel2007';
			$objReader = \PHPExcel_IOFactory::createReader ( $format );
			$objPHPExcel = $objReader->load ( $filename );
			$objPHPExcel->setActiveSheetIndex ( 0 );
			$sheet = $objPHPExcel->getSheet ( 0 );
			$highestRow = $sheet->getHighestRow (); // 取得总行数
			for($j = 2; $j <= $highestRow; $j ++) {
				$addData ['code'] = $map ['code'] = trim ( ( string ) $objPHPExcel->getActiveSheet ()->getCell ( 'A' . $j )->getValue () );
				$addData ['title'] = trim ( ( string ) $objPHPExcel->getActiveSheet ()->getCell ( 'B' . $j )->getValue () );
				$addData ['pid'] = trim ( ( string ) $objPHPExcel->getActiveSheet ()->getCell ( 'C' . $j )->getValue () );
				$addData ['module'] = $map ['module'] = $module;
				$addData ['token'] = $map ['token'] = get_token ();
				
				if (empty ( $addData ['code'] ) || empty ( $addData ['title'] ))
					continue;
				
				if (M ( 'common_category' )->where ( $map )->find ()) {
					$res = M ( 'common_category' )->where ( $map )->save ( $addData );
				} else {
					$res = M ( 'common_category' )->add ( $addData );
				}
			}
			$this->success ( '导入完成', U ( 'home/Category/lists', 'module=' . $module ) );
		} else {
			$fields [1] [0] = array (
					"name" => "attach",
					"title" => "上传文件",
					"type" => "file",
					"remark" => "只支持上传xls,xlsx两种格式的导入文件",
					"is_show" => "1",
					"is_must" => "1",
					"status" => "1" 
			);
			$this->assign ( 'fields', $fields );
			$this->meta_title = '导入数据';
			
			$this->assign ( 'post_url', U ( 'inputData', 'module=' . $module ) );
			
			$this->display ( 'Addons/add' );
		}
	}
	
	/**
	 * 分类管理列表
	 *
	 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
	 */
	public function index() {
		redirect ( U ( 'lists', 'module=' . $_GET ['module'] ) );
	}
	
	/**
	 * 显示分类树，仅支持内部调
	 *
	 * @param array $tree
	 *        	分类树
	 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
	 */
	public function tree($tree = null) {
		C ( '_SYS_GET_CATEGORY_TREE_' ) || $this->_empty ();
		$this->assign ( 'tree', $tree );
		// dump($tree);
		
		$this->display ( 'tree' );
	}
	
	/* 编辑分类 */
	public function edit($id = null, $pid = 0) {
		$Category = D ( 'Category' );
		if (IS_POST) { // 提交表单
			$_POST ['module'] = session ( 'common_category_module' );
			if (false !== $Category->update ()) {
				$this->success ( '编辑成功！', U ( 'lists', 'module=' . $_POST ['module'] ) );
			} else {
				$error = $Category->getError ();
				$this->error ( empty ( $error ) ? '未知错误！' : $error );
			}
		} else {
			$cate = '';
			if ($pid) {
				/* 获取上级分类信息 */
				$cate = $Category->info ( $pid, 'id,name,title' );
				if (! $cate) {
					$this->error ( '指定的上级分类不存在或被禁用！' );
				}
			}
			
			/* 获取分类信息 */
			$info = $id ? $Category->info ( $id ) : '';
			
			$map ['name'] = I ( 'module' );
			$group = M ( 'common_category_group' )->where ( $map )->find ();
			$this->assign ( 'group', $group );
			
			$this->assign ( 'info', $info );
			$this->assign ( 'category', $cate );
			$this->meta_title = '编辑分类';
			$this->display ();
		}
	}
	
	/* 新增分类 */
	public function add($pid = 0) {
		$Category = D ( 'Category' );
		
		if (IS_POST) { // 提交表单
			$_POST ['module'] = session ( 'common_category_module' );
			$_POST ['token'] = get_token ();
			if (false !== $Category->update ()) {
				$this->success ( '新增成功！', U ( 'lists', 'module=' . $_POST ['module'] ) );
			} else {
				$error = $Category->getError ();
				$this->error ( empty ( $error ) ? '未知错误！' : $error );
			}
		} else {
			$cate = array ();
			if ($pid) {
				/* 获取上级分类信息 */
				$cate = $Category->info ( $pid, 'id,name,title' );
				if (! $cate) {
					$this->error ( '指定的上级分类不存在或被禁用！' );
				}
			}
			
			$map ['name'] = I ( 'module' );
			$group = M ( 'common_category_group' )->where ( $map )->find ();
			$this->assign ( 'group', $group );
			
			/* 获取分类信息 */
			$this->assign ( 'category', $cate );
			$this->meta_title = '新增分类';
			$this->display ( 'edit' );
		}
	}
	
	/**
	 * 删除一个分类
	 *
	 * @author huajie <banhuajie@163.com>
	 */
	public function remove() {
		$cate_id = I ( 'id' );
		if (empty ( $cate_id )) {
			$this->error ( '参数错误!' );
		}
		
		// 判断该分类下有没有子分类，有则不允许删除
		$child = D ( 'Category' )->where ( array (
				'pid' => $cate_id 
		) )->field ( 'id' )->select ();
		if (! empty ( $child )) {
			$this->error ( '请先删除该分类下的子分类' );
		}
		
		// 删除该分类信息
		$res = D ( 'Category' )->delete ( $cate_id );
		if ($res !== false) {
			// 记录行为
			action_log ( 'update_category', 'category', $cate_id, UID );
			$this->success ( '删除分类成功！' );
		} else {
			$this->error ( '删除分类失败！' );
		}
	}
	
	/**
	 * 操作分类初始化
	 *
	 * @param string $type        	
	 * @author huajie <banhuajie@163.com>
	 */
	public function operate($type = 'move') {
		// 检查操作参数
		if (strcmp ( $type, 'move' ) == 0) {
			$operate = '移动';
		} elseif (strcmp ( $type, 'merge' ) == 0) {
			$operate = '合并';
		} else {
			$this->error ( '参数错误！' );
		}
		$from = intval ( I ( 'get.from' ) );
		empty ( $from ) && $this->error ( '参数错误！' );
		
		// 获取分类
		$map = array (
				'id' => array (
						'neq',
						$from 
				) 
		);
		$list = D ( 'Category' )->where ( $map )->field ( 'id,title' )->select ();
		
		$this->assign ( 'type', $type );
		$this->assign ( 'operate', $operate );
		$this->assign ( 'from', $from );
		$this->assign ( 'list', $list );
		$this->meta_title = $operate . '分类';
		$this->display ();
	}
	
	/**
	 * 移动分类
	 *
	 * @author huajie <banhuajie@163.com>
	 */
	public function move() {
		$to = I ( 'post.to' );
		$from = I ( 'post.from' );
		$res = D ( 'Category' )->where ( array (
				'id' => $from 
		) )->setField ( 'pid', $to );
		if ($res !== false) {
			$this->success ( '分类移动成功！', U ( 'lists', 'module=' . $_GET ['module'] ) );
		} else {
			$this->error ( '分类移动失败！' );
		}
	}
}