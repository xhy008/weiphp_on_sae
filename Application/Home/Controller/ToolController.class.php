<?php
// +----------------------------------------------------------------------
// | WeiPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 凡星
// +----------------------------------------------------------------------
namespace Home\Controller;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class ToolController extends HomeController {
	var $db2 = '`update`'; // 要更新的数据库
	var $db1 = '`dev_weiphp`'; // 源数据库
	function index() {
		$tables = array (
				'wp_auth_rule' => 'name',
				'wp_menu' => 'title',
				'wp_config' => 'name',
				'wp_addons' => 'name',
				'wp_action' => 'name',
				'wp_hooks' => 'name' 
		);
		
		foreach ( $tables as $t => $field ) {
			$this->adddel ( $t, $field );
		}
	}
	function upateModel() {
		// 先判断模型
		$this->adddel ( 'wp_model', 'name' );
		
		// 属性更新
		$table = 'wp_attribute';
		$sql = "SELECT a.*, m.name as model_name FROM {$this->db1}.`$table` a left join {$this->db1}.wp_model m on a.model_id=m.id";
		$list1 = M ()->query ( $sql );
		foreach ( $list1 as $vo1 ) {
			$arr1 [$vo1 ['model_name']] [$vo1 ['name']] = $vo1 ['id'];
		}
		// dump ( $arr1 );
		
		$sql = "SELECT a.*, m.name as model_name FROM {$this->db2}.`$table` a left join {$this->db2}.wp_model m on a.model_id=m.id";
		$list2 = M ()->query ( $sql );
		foreach ( $list2 as $vo1 ) {
			$arr2 [$vo1 ['model_name']] [$vo1 ['name']] = $vo1 ['id'];
			$fields2 [$vo1 ['id']] = $vo1;
		}
		// dump ( $arr2 );exit;
		
		foreach ( $list1 as $vo ) {
			if (isset ( $arr2 [$vo ['model_name']] [$vo ['name']] )) {
				$field1 = $vo;
				$field2 = $fields2 [$arr2 [$vo ['model_name']] [$vo ['name']]];
				unset ( $field1 ['id'], $field1 ['update_time'], $field1 ['create_time'] );
				unset ( $field2 ['id'], $field2 ['update_time'], $field2 ['create_time'] );
				
				$diff = array_diff ( $field1, $field2 );
				if (! empty ( $diff )) {
					$updateArr [$vo ['model_name']] [$vo ['name']] = $diff;
				}
			} else {
				
				$vo ['model_id'] = '{$model_id}';
				$model_name = $vo ['model_name'];
				unset ( $vo ['id'] );
				unset ( $vo ['model_name'] );
				$fields = array_keys ( $vo );
				$fields = '`' . implode ( '`,`', $fields ) . '`';
				$val = "'" . implode ( "','", $vo ) . "'";
				
				$insertArr [$model_name] .= " ({$val}),";
			}
		}
		foreach ( $list2 as $vo ) {
			if (isset ( $arr1 [$vo ['model_name']] [$vo ['name']] ))
				continue;
			
			$delArr [] = "DELETE a FROM wp_attribute a, wp_model m WHERE a.model_id=m.id and m.`name`='{$vo [model_name]}' and a.`name`='{$vo [name]}';";
		}
		
		if (! empty ( $insertArr )) {
			echo '$insertArr=' . var_export ( $insertArr, true ) . ';<br/><br/><br/>';
		}
		if (! empty ( $updateArr )) {
			echo '$updateArr=' . var_export ( $updateArr, true ) . ';<br/><br/><br/>';
		}
		if (! empty ( $delArr )) {
			echo '$delArr=' . var_export ( $delArr, true ) . ';<br/><br/><br/>';
		}
	}
	function adddel($table, $field) {
		$sql = "SELECT * FROM {$this->db1}.`$table`";
		$list1 = M ()->query ( $sql );
		$arr1 = getSubByKey ( $list1, $field );
		// dump ( $arr1 );
		
		$sql = "SELECT * FROM {$this->db2}.`$table`";
		$list2 = M ()->query ( $sql );
		$arr2 = getSubByKey ( $list2, $field );
		// dump ( $arr2 );
		foreach ( $list2 as $v ) {
			unset ( $v ['id'], $v ['update_time'], $v ['create_time'] );
			$fields [$v ['name']] = $v;
		}
		
		$add_arr = array_diff ( $arr1, $arr2 );
		// dump ( $add_arr );
		
		$del_arr = array_diff ( $arr2, $arr1 );
		// dump ( $del_arr );
		
		foreach ( $list1 as $key => $value ) {
			unset ( $value ['id'] );
			if (in_array ( $value [$field], $add_arr )) {
				$fields = array_keys ( $value );
				$fields = '`' . implode ( '`,`', $fields ) . '`';
				$val = "'" . implode ( "','", $value ) . "'";
				$sqlArr [] = "INSERT INTO $table ({$fields}) VALUES ({$val});<br/>";
			} elseif (in_array ( $value [$field], $del_arr )) {
				$sqlArr [] = "DELETE FROM $table WHERE `{$field}`='{$value [$field]}';<br/>";
			} else {
				unset ( $value ['id'], $value ['update_time'], $value ['create_time'] );
				$diff = array_diff ( $value, $fields [$value ['name']] );
				if (! empty ( $diff )) {
					$modelArr [$value ['name']] = $diff;
				}
			}
		}
		if (! empty ( $modelArr )) {
			echo '$modelArr=' . var_export ( $modelArr, true ) . ';<br/><br/><br/>';
		}
		if (! empty ( $sqlArr )) {
			echo '$sqlArr=' . var_export ( $sqlArr, true ) . ';<br/><br/><br/>';
		}
	}
	function updateFieldSort() {
		$list = M ( 'model' )->select ();
		foreach ( $list as $vo ) {
			if (empty ( $vo ['field_sort'] ))
				continue;
			
			$field_sort = json_decode ( $vo ['field_sort'], true );
			foreach ( $field_sort as &$f ) {
				foreach ( $f as &$id ) {
					if (! is_numeric ( $id ))
						continue;
					
					$map ['model_id'] = $vo ['id'];
					$map ['id'] = $id;
					$id = M ( 'attribute' )->where ( $map )->getField ( 'name' );
				}
				$f = array_filter ( $f );
			}
			$field_sort = json_encode ( $field_sort );
			
			M ( 'model' )->where ( 'id=' . $vo ['id'] )->setField ( 'field_sort', $field_sort );
		}
	}
}