<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;

use Admin\Model\AuthGroupModel;

/**
 * 模型管理控制器
 *
 * @author huajie <banhuajie@163.com>
 */
class ModelController extends AdminController {
	
	/**
	 * 模型管理首页
	 *
	 * @author huajie <banhuajie@163.com>
	 */
	public function index() {
		$map = array (
				'status' => array (
						'gt',
						- 1 
				) 
		);
		$list = $this->lists ( 'Model', $map, '`name` asc' );
		int_to_string ( $list );
		// 记录当前列表页的cookie
		Cookie ( '__forward__', $_SERVER ['REQUEST_URI'] );
		
		$this->assign ( '_list', $list );
		$this->meta_title = '模型管理';
		$this->display ();
	}
	
	/**
	 * 新增页面初始化
	 *
	 * @author huajie <banhuajie@163.com>
	 */
	public function add() {
		// 获取所有的模型
		$models = M ( 'Model' )->where ( array (
				'extend' => 0 
		) )->field ( 'id,title' )->select ();
		
		$this->assign ( 'models', $models );
		$this->meta_title = '新增模型';
		$this->display ();
	}
	
	/**
	 * 编辑页面初始化
	 *
	 * @author huajie <banhuajie@163.com>
	 */
	public function edit() {
		$id = I ( 'get.id', '' );
		if (empty ( $id )) {
			$this->error ( '参数不能为空！' );
		}
		
		/* 获取一条记录的详细数据 */
		$Model = M ( 'Model' );
		$data = $Model->field ( true )->find ( $id );
		if (! $data) {
			$this->error ( $Model->getError () );
		}
		
		$fields = M ( 'Attribute' )->where ( array (
				'model_id' => $data ['id'] 
		) )->field ( 'id,name,title,is_show' )->select ();
		// 是否继承了其他模型
		if ($data ['extend'] != 0) {
			$extend_fields = M ( 'Attribute' )->where ( array (
					'model_id' => $data ['extend'] 
			) )->field ( 'id,name,title,is_show' )->select ();
			$fields = array_merge ( $fields, $extend_fields );
		}

		/* 获取模型排序字段 */
		$field_sort = json_decode ( $data ['field_sort'], true );
		if (! empty ( $field_sort )) {
			/* 对字段数组重新整理 */
			$fields_f = array ();
			foreach ( $fields as $v ) {
				$fields_f [$v ['name']] = $v;
				$field_sort[1][] = $v['name'];
			}
			$fields = array ();
			$field_sort[1] = array_unique($field_sort[1]);
			foreach ( $field_sort as $key => $groups ) {
				foreach ( $groups as $group ) {
					$fields [] = array (
							'id' => $fields_f [$group] ['id'],
							'name' => $fields_f [$group] ['name'],
							'title' => $fields_f [$group] ['title'],
							'is_show' => $fields_f [$group] ['is_show'],
							'group' => $key 
					);
				}
			}
		}
		
		$this->assign ( 'fields', $fields );  //dump($fields);
		$this->assign ( 'info', $data ); // dump($data);
		$this->meta_title = '编辑模型';
		$this->display ();
	}
	
	/**
	 * 删除一条数据
	 *
	 * @author huajie <banhuajie@163.com>
	 */
	public function del() {
		$ids = I ( 'get.ids' );
		empty ( $ids ) && $this->error ( '参数不能为空！' );
		$ids = explode ( ',', $ids );
		foreach ( $ids as $value ) {
			$res = D ( 'Model' )->del ( $value );
			if (! $res) {
				break;
			}
		}
		if (! $res) {
			$this->error ( D ( 'Model' )->getError () );
		} else {
			$this->success ( '删除模型成功！' );
		}
	}
	
	/**
	 * 更新一条数据
	 *
	 * @author huajie <banhuajie@163.com>
	 */
	public function update() {
		$res = D ( 'Model' )->update ();
		
		if (! $res) {
			$this->error ( D ( 'Model' )->getError () );
		} else {
			$this->success ( $res ['id'] ? '更新成功' : '新增成功', Cookie ( '__forward__' ) );
		}
	}
	
	/**
	 * 生成一个模型
	 *
	 * @author huajie <banhuajie@163.com>
	 */
	public function generate() {
		if (! IS_POST) {
			// 获取所有的数据表
			$tables = D ( 'Model' )->getTables ();
			
			$this->assign ( 'tables', $tables );
			$this->meta_title = '生成模型';
			$this->display ();
		} else {
			$table = I ( 'post.table' );
			empty ( $table ) && $this->error ( '请选择要生成的数据表！' );
			$res = D ( 'Model' )->generate ( $table );
			if ($res) {
				$this->success ( '生成模型成功！', U ( 'index' ) );
			} else {
				$this->error ( D ( 'Model' )->getError () );
			}
		}
	}
	/**
	 * 导出一个模型
	 */
	public function export() {
		$id = I ( 'get.id' );
		$type = I ( 'get.type', 0, 'intval' );
		empty ( $id ) && $this->error ( '参数不能为空！' );
		
		// 模型信息
		$map ['id'] = $id;
		$model = D ( 'Model' )->where ( $map )->find ();
		
		// 模型字段
		$map2 ['model_id'] = $id;
		$list = D ( 'Attribute' )->where ( $map2 )->select ();
		
		// 模型数据表
		$name = get_table_name ( $model ['id'] );
		$data = M ( parse_name ( $name, true ) )->select ();
		$name = strtolower ( $name );
		if ($type == 1) {
			$sql = "DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='{$model['name']}' ORDER BY id DESC LIMIT 1);\r\n";
			$sql .= "DELETE FROM `wp_model` WHERE `name`='{$model['name']}' ORDER BY id DESC LIMIT 1;\r\n";
			$sql .= "DROP TABLE IF EXISTS `wp_" . strtolower ( $name ) . "`;";
			$path = RUNTIME_PATH . 'uninstall.sql';
		} else {
			if ($model ['need_pk']) {
				$create_table = "`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',\r\n";
				$key = "PRIMARY KEY (`id`)";
			}
			foreach ( $list as $field ) {
				// 获取默认值
				if ($field ['value'] === '') {
					$default = '';
				} elseif (is_numeric ( $field ['value'] )) {
					$default = ' DEFAULT ' . $field ['value'];
				} elseif (is_string ( $field ['value'] )) {
					$default = ' DEFAULT \'' . $field ['value'] . '\'';
				} else {
					$default = '';
				}
				$create_table .= "`{$field['name']}`  {$field['field']} {$default} COMMENT '{$field['title']}',\r\n";
			}
			
			$sql .= <<<sql
CREATE TABLE IF NOT EXISTS `wp_{$name}` (
{$create_table}{$key}
) ENGINE={$model['engine_type']} DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci CHECKSUM=0 ROW_FORMAT=DYNAMIC DELAY_KEY_WRITE=0;\r\n
sql;
			
			unset ( $field );
			foreach ( $data as $d ) {
				$field = '';
				$value = '';
				foreach ( $d as $k => $v ) {
					$field .= "`$k`,";
					$value .= "'" . str_replace ( "\r\n", '\r\n', $v ) . "',";
				}
				$sql .= "INSERT INTO `wp_{$name}` (" . rtrim ( $field, ',' ) . ') VALUES (' . rtrim ( $value, ',' ) . ");\r\n";
			}
			
			unset ( $model ['id'] );
			$field = '';
			$value = '';
			foreach ( $model as $k => $v ) {
				$field .= "`$k`,";
				$value .= "'" . str_replace ( "\r\n", '\r\n', $v ) . "',";
			}
			$sql .= 'INSERT INTO `wp_model` (' . rtrim ( $field, ',' ) . ') VALUES (' . rtrim ( $value, ',' ) . ');' . "\r\n";
			
			// dump($list);
			foreach ( $list as $k => $vo ) {
				unset ( $vo ['id'] );
				$vo ['model_id'] = 0;
				$field = '';
				$value = '';
				foreach ( $vo as $k => $v ) {
					$field .= "`$k`,";
					$value .= "'" . str_replace ( "\r\n", '\r\n', $v ) . "',";
				}
				$sql .= 'INSERT INTO `wp_attribute` (' . rtrim ( $field, ',' ) . ') VALUES (' . rtrim ( $value, ',' ) . ');' . "\r\n";
			}
			$sql .= 'UPDATE `wp_attribute` SET model_id= (SELECT MAX(id) FROM `wp_model`) WHERE model_id=0;';
			
			$path = RUNTIME_PATH . 'install.sql';
		}
		
		@file_put_contents ( $path, $sql );
		redirect ( SITE_URL . '/' . $path );
	}
	// 一键增加微信插件常用模型
	function add_comon_model() {
		$install_sql = './Application/Admin/Conf/common_model.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		$this->success ( '增加成功' );
	}
}
