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
class PublicGroupController extends AdminController {
	// 公众号等级
	function PublicGroup() {
		$page = I ( 'p', 1, 'intval' );
		
		$addon_list = D ( 'Home/Addons' )->getWeixinList ( true );
		foreach ( $addon_list as $v ) {
			$all_ids [] = $v ['id'];
			$nameArr [$v ['id']] = $v ['title'];
		}
		
		$public_list = M ( 'member_public' )->field ( 'group_id,count(id) as public_count' )->group ( 'group_id' )->select ();
		foreach ( $public_list as $p ) {
			$public_count [$p ['group_id']] = $p ['public_count'];
		}
		
		// 获取模型信息
		$model = M ( 'Model' )->getByName ( 'member_public_group' );
		$list_data = $this->_get_model_list ( $model, $page );
		foreach ( $list_data ['list_data'] as &$vo ) {
			$vo ['addon_status'] = explode ( ',', $vo ['addon_status'] );
			$vo ['addon_status'] = array_diff ( $all_ids, $vo ['addon_status'] );
			$vo ['addon_status'] = $this->_idToName ( $vo ['addon_status'], $nameArr );
			
			$vo ['public_count'] = intval ( $public_count [$vo ['id']] );
		}
		
		$this->assign ( $list_data );
		
		$this->assign ( 'model', $model );
		$this->meta_title = $model ['title'] . '管理';
		$this->display ( 'Think:lists' );
	}
	// 通过插件ID转换成名字
	function _idToName($ids, $list) {
		foreach ( $ids as $id ) {
			$res [] = $list [$id];
		}
		return implode ( ',', $res );
	}
	function add() {
		$model = $this->getModel ();
		
		if (IS_POST) {
			$addon_list = D ( 'Home/Addons' )->getWeixinList ( true );
			foreach ( $addon_list as $v ) {
				$all_ids [] = $v ['id'];
			}
			$_POST ['addon_status'] = array_diff ( $all_ids, $_POST ['addon_status'] );
			
			$Model = D ( parse_name ( get_table_name ( $model ['id'] ), 1 ) );
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $model ['id'] );
			if ($Model->create () && $Model->add ()) {
				$this->success ( '添加' . $model ['title'] . '成功！', U ( 'PublicGroup' ) );
			} else {
				$this->error ( $Model->getError () );
			}
		} else {
			
			$fields = get_model_attribute ( $model ['id'] );
			$this->_deal_addon ( $fields [1] [1] );
			
			$this->assign ( 'fields', $fields );
			$this->meta_title = '新增' . $model ['title'];
			
			$this->display ( 'Think:add' );
		}
	}
	function del() {
		parent::common_del ();
	}
	function editPublicGroup() {
		$model = $this->getModel ( 'member_public_group' );
		$id = I ( 'id', 0, 'intval' );
		
		if (IS_POST) {
			$addon_list = D ( 'Home/Addons' )->getWeixinList ( true );
			foreach ( $addon_list as $v ) {
				$all_ids [] = $v ['id'];
			}
			$_POST ['addon_status'] = array_diff ( $all_ids, $_POST ['addon_status'] );
			
			$Model = D ( parse_name ( get_table_name ( $model ['id'] ), 1 ) );
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $model ['id'] );
			if ($Model->create () && $Model->save ()) {
				$this->success ( '保存' . $model ['title'] . '成功！', U ( 'PublicGroup' ) );
			} else {
				$this->error ( $Model->getError () );
			}
		} else {
			$fields = get_model_attribute ( $model ['id'] );
			$this->_deal_addon ( $fields [1] [1] );
			
			// 获取数据
			$data = M ( get_table_name ( $model ['id'] ) )->find ( $id );
			$data || $this->error ( '数据不存在！' );
			
			$data ['addon_status'] = explode ( ',', $data ['addon_status'] );
			$data ['addon_status'] = array_diff ( $fields [1] [1] ['value'], $data ['addon_status'] );
			
			$this->assign ( 'fields', $fields );
			$this->assign ( 'data', $data );
			$this->meta_title = '编辑' . $model ['title'];
			$this->assign ( 'post_url', U ( 'admin/PublicGroup/editPublicGroup', 'id=' . $id ) );
			
			$this->display ( 'Think:edit' );
		}
	}
	public function delPublicGroup() {
		$model = $this->getModel ( 'member_public_group' );
		
		$ids = I ( 'id' );
		if (empty ( $ids )) {
			$ids = array_unique ( ( array ) I ( 'ids', 0 ) );
		}
		if (empty ( $ids )) {
			$this->error ( '请选择要操作的数据!' );
		}
		
		$Model = M ( get_table_name ( $model ['id'] ) );
		$map ['id'] = array (
				'in',
				$ids 
		);
		if ($Model->where ( $map )->delete ()) {
			$save ['group_id'] = intval ( C ( 'DEFAULT_PUBLIC_GROUP_ID' ) );
			if (in_array ( $save ['group_id'], $ids )) {
				$save ['group_id'] = 0;
			} else {
				$map2 ['group_id'] = $map ['id'];
			}
			
			M ( 'member_public' )->where ( $map2 )->save ( $save );
			
			$this->success ( '删除成功' );
		} else {
			$this->error ( '删除失败！' );
		}
	}
	function _deal_addon(&$info) {
		$addon_list = D ( 'Home/Addons' )->getWeixinList ( true );
		foreach ( $addon_list as $vo ) {
			$extra .= $vo ['id'] . ':' . $vo ['title'] . "\n";
			$value [] = $vo ['id'];
		}
		$info ['extra'] = $extra;
		$info ['value'] = $value;
	}
	
	// 公众号管理
	function PublicAdmin() {
		$page = I ( 'p', 1, 'intval' );
		
		$addon_list = D ( 'Home/Addons' )->getWeixinList ( true );
		foreach ( $addon_list as $v ) {
			$all_ids [] = $v ['id'];
			$nameArr [$v ['id']] = $v ['title'];
		}
		
		$public_list = M ( 'member_public' )->field ( 'group_id,count(id) as public_count' )->group ( 'group_id' )->select ();
		foreach ( $public_list as $p ) {
			$public_count [$p ['group_id']] = $p ['public_count'];
		}
		
		// 获取模型信息
		$model = M ( 'Model' )->getByName ( 'member_public' );
		$list_data = $this->_get_model_list ( $model, $page );
		
		$ids = getSubByKey ( $list_data ['list_data'], 'id' );
		if ($ids) {
			$map ['mp_id'] = array (
					'in',
					$ids 
			);
			$link = M ( 'member_public_link' )->where ( $map )->select ();
			
			foreach ( $link as $k ) {
				$admin [$k ['mp_id']] [] = get_nickname ( $k ['uid'] );
			}
		}
		
		foreach ( $list_data ['list_data'] as &$vo ) {
			if (! empty ( $vo ['headface_url'] )) {
				$vo ['headface_url'] = '<img src="' . get_cover_url ( $vo ['headface_url'] ) . '" width="50" height="50" />';
			}
			$vo ['uid'] = implode ( ' , ', $admin [$vo ['id']] );
		}
		
		$href = 'editPublicAdmin?id=[id]|编辑,delPublicAdmin?id=[id]|删除,PublicLink?id=[id]|管理员配置';
		foreach ( $list_data ['list_grids'] as $k => &$g ) {
			if ($g ['title'] == '操作') {
				$g ['href'] = $href;
			}
			if ($g ['title'] == '当前公众号') {
				unset ( $list_data ['list_grids'] [$k] );
			}
		}
		
		$this->assign ( $list_data );
		$this->assign ( 'model', $model );
		$this->meta_title = $model ['title'] . '管理';
		$this->assign ( 'add_url', U('Home/MemberPublic/add') );
		$this->display ( 'Think:lists' );
	}
	function PublicLink() {
		$param ['mp_id'] = I ( 'id', 0, 'intval' );
		redirect ( U ( 'admin/PublicLink/lists', $param ) );
	}
	function editPublicAdmin() {
		$model = $this->getModel ( 'member_public' );
		$id = I ( 'id', 0, 'intval' );
		
		if (IS_POST) {
			$Model = D ( parse_name ( get_table_name ( $model ['id'] ), 1 ) );
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $model ['id'] );
			if ($Model->create () && $Model->save ()) {
				$this->success ( '保存' . $model ['title'] . '成功！', U ( 'PublicAdmin' ) );
			} else {
				$this->error ( $Model->getError () );
			}
		} else {
			$fields = get_model_attribute ( $model ['id'] );
			foreach ( $fields [1] as &$vo ) {
				if ($vo ['name'] == 'group_id') {
					$vo ['is_show'] = 1;
					
					$group_list = M ( 'member_public_group' )->field ( 'id, title' )->select ();
					$extra = "0:无\n";
					foreach ( $group_list as $g ) {
						$extra .= $g ['id'] . ':' . $g ['title'] . "\n";
					}
					$vo ['extra'] = $extra;
				}
			}
			
			// 获取数据
			$data = M ( get_table_name ( $model ['id'] ) )->find ( $id );
			$data || $this->error ( '数据不存在！' );
			
			$this->assign ( 'fields', $fields );
			$this->assign ( 'data', $data );
			$this->meta_title = '编辑' . $model ['title'];
			$this->assign ( 'post_url', U ( 'admin/PublicGroup/editPublicAdmin', 'id=' . $id ) );
			
			$this->display ( 'Think:edit' );
		}
	}
	public function delPublicAdmin() {
		$model = $this->getModel ( 'member_public' );
		
		$ids = I ( 'id' );
		if (empty ( $ids )) {
			$ids = array_unique ( ( array ) I ( 'ids', 0 ) );
		}
		if (empty ( $ids )) {
			$this->error ( '请选择要操作的数据!' );
		}
		
		$Model = M ( get_table_name ( $model ['id'] ) );
		$map = array (
				'id' => array (
						'in',
						$ids 
				) 
		);
		if ($Model->where ( $map )->delete ()) {
			$this->success ( '删除成功' );
		} else {
			$this->error ( '删除失败！' );
		}
	}
}
