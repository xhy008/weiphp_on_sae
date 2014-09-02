<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 朱亚杰 <zhuyajie@topthink.net>
// +----------------------------------------------------------------------
namespace Admin\Controller;

/**
 * 公众号管理控制器
 *
 * @author 凡星
 */
class PublicLinkController extends AdminController {
	var $table = 'member_public_link';
	function lists() {
		$addon_list = D ( 'Home/Addons' )->getWeixinList ( true );
		foreach ( $addon_list as $v ) {
			$all_ids [] = $v ['id'];
			$nameArr [$v ['id']] = $v ['title'];
		}
		
		$model = $this->getModel ( $this->table );
		
		$map ['mp_id'] = intval ( $_GET ['mp_id'] );
		if (! empty ( $_GET ['title'] )) {
			$title = I ( 'get.title' );
			$where = "nickname like '%$title%'";
			$uids = M ( 'member' )->where ( $where )->field ( 'uid' )->select ();
			$uids = getSubByKey ( $uids, 'uid' );
			$uids [] = 0;
			$map ['uid'] = array (
					'in',
					$uids 
			);
		}
		session ( 'common_condition', $map );
		
		$list_data = $this->_get_model_list ( $model );
		foreach ( $list_data ['list_data'] as &$vo ) {
			$vo ['addon_status'] = explode ( ',', $vo ['addon_status'] );
			$vo ['addon_status'] = array_diff ( $all_ids, $vo ['addon_status'] );
			$vo ['addon_status'] = $this->_idToName ( $vo ['addon_status'], $nameArr );
		}
		
		$this->assign ( $list_data );
		// dump($list_data);
		
		$this->assign ( 'add_url', U ( 'add?model=' . $model ['id'] . '&mp_id=' . $map ['mp_id'] ) );
		$this->assign ( 'search_url', U ( 'lists?model=' . $model ['id'] . '&mp_id=' . $map ['mp_id'] ) );
		// $this->assign ( 'search_button', false );
		
		$this->display ( 'Think:lists' );
	}
	// 通过插件ID转换成名字
	function _idToName($ids, $list) {
		foreach ( $ids as $id ) {
			$res [] = $list [$id];
		}
		return implode ( ',', $res );
	}
	public function del($ids = null) {
		$model = $this->getModel ( $this->table );
		
		! empty ( $ids ) || $ids = I ( 'id' );
		! empty ( $ids ) || $ids = array_unique ( ( array ) I ( 'ids', 0 ) );
		! empty ( $ids ) || $this->error ( '请选择要操作的数据!' );
		
		$Model = M ( get_table_name ( $model ['id'] ) );
		$map ['id'] = array (
				'in',
				$ids 
		);
		
		if ($Model->where ( $map )->delete ()) {
			$this->success ( '删除成功' );
		} else {
			$this->error ( '删除失败！' );
		}
	}
	public function edit($id = 0) {
		$model = $this->getModel ( $this->table );
		$id || $id = I ( 'id' );
		
		if (IS_POST) {
			$addon_list = D ( 'Common/AddonStatus' )->getPublicAddons ( $_POST ['mp_id'] );
			foreach ( $addon_list as $v ) {
				$all_ids [] = $v ['id'];
			}
			$_POST ['addon_status'] = array_diff ( $all_ids, $_POST ['addon_status'] );
			
			$Model = D ( parse_name ( get_table_name ( $model ['id'] ), 1 ) );
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $model ['id'] );
			if ($Model->create () && $Model->save ()) {
				$this->success ( '保存' . $model ['title'] . '成功！', U ( 'lists?model=' . $model ['name'] . '&mp_id=' . $_POST ['mp_id'] ) );
			} else {
				$this->error ( $Model->getError () );
			}
		} else {
			$fields = get_model_attribute ( $model ['id'] );
			$this->_deal_addon ( $fields [1] [1], $_POST ['mp_id'] );
			
			// 获取数据
			$data = M ( get_table_name ( $model ['id'] ) )->find ( $id );
			$data || $this->error ( '数据不存在！' );
			
			$data ['addon_status'] = explode ( ',', $data ['addon_status'] );
			$data ['addon_status'] = array_diff ( $fields [1] [1] ['value'], $data ['addon_status'] );
			
			$this->assign ( 'fields', $fields );
			$this->assign ( 'data', $data );
			$this->meta_title = '编辑' . $model ['title'];
			
			$this->display ( 'Think:edit' );
		}
	}
	public function add() {
		$model = $this->getModel ( $this->table );
		if (IS_POST) {
			$addon_list = D ( 'Common/AddonStatus' )->getPublicAddons ( $_POST ['mp_id'] );
			foreach ( $addon_list as $v ) {
				$all_ids [] = $v ['id'];
			}
			$_POST ['addon_status'] = array_diff ( $all_ids, $_POST ['addon_status'] );
			
			$Model = D ( parse_name ( get_table_name ( $model ['id'] ), 1 ) );
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $model ['id'] );
			if ($Model->create () && $id = $Model->add ()) {
				$this->success ( '添加' . $model ['title'] . '成功！', U ( 'lists?model=' . $model ['name'] . '&mp_id=' . $_POST ['mp_id'] ) );
			} else {
				$this->error ( $Model->getError () );
			}
		} else {
			$fields = get_model_attribute ( $model ['id'] );
			$this->_deal_addon ( $fields [1] [1], $_POST ['mp_id'] );
			
			$this->assign ( 'fields', $fields );
			$this->meta_title = '新增' . $model ['title'];
			
			$this->display ( 'Think:add' );
		}
	}
	function _deal_addon(&$info, $mp_id) {
		$addon_list = D ( 'Common/AddonStatus' )->getPublicAddons ( $mp_id );
		
		foreach ( $addon_list as $vo ) {
			$extra .= $vo ['id'] . ':' . $vo ['title'] . "\n";
			$value [] = $vo ['id'];
		}
		$info ['extra'] = $extra;
		$info ['value'] = $value;
	}
}
