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
 * 公众号管理
 */
class MemberPublicController extends HomeController {
	protected $addon, $model;
	public function _initialize() {
		parent::_initialize ();
		
		$this->assign ( 'check_all', false );
		$this->assign ( 'search_url', U ( 'lists' ) );
		
		define ( 'ADDON_PUBLIC_PATH', '' );
		defined ( '_ADDONS' ) or define ( '_ADDONS', MODULE_NAME );
		defined ( '_CONTROLLER' ) or define ( '_CONTROLLER', CONTROLLER_NAME );
		defined ( '_ACTION' ) or define ( '_ACTION', ACTION_NAME );
		
		$this->model = M ( 'Model' )->getByName ( 'member_public' );
		$this->assign ( 'model', $this->model );
		// dump ( $this->model );
		
		$res ['title'] = $this->model ['title'];
		$res ['url'] = U ( 'lists' );
		$res ['class'] = ACTION_NAME != 'help' ? 'current' : '';
		$nav [] = $res;
		
		$res ['title'] = '接口配置帮助';
		$res ['url'] = U ( 'help' );
		$res ['class'] = ACTION_NAME == 'help' ? 'current' : '';
		$nav [] = $res;
		
		$this->assign ( 'nav', $nav );
	}
	protected function _display() {
		$this->view->display ( 'Addons:' . ACTION_NAME );
	}
	function help() {
		$this->display ( 'Index/help' );
	}
	/**
	 * 显示指定模型列表数据
	 */
	public function lists() {
		// 获取模型信息
		$model = $this->model;
		
		$page = I ( 'p', 1, 'intval' );
		// 解析列表规则
		$fields = array ();
		$grids = preg_split ( '/[;\r\n]+/s', htmlspecialchars_decode ( $model ['list_grid'] ) );
		foreach ( $grids as &$value ) {
			// 字段:标题:链接
			$val = explode ( ':', $value );
			// 支持多个字段显示
			$field = explode ( ',', $val [0] );
			$value = array (
					'field' => $field,
					'title' => $val [1] 
			);
			if (isset ( $val [2] )) {
				// 链接信息
				$value ['href'] = $val [2];
				// 搜索链接信息中的字段信息
				preg_replace_callback ( '/\[([a-z_]+)\]/', function ($match) use(&$fields) {
					$fields [] = $match [1];
				}, $value ['href'] );
			}
			if (strpos ( $val [1], '|' )) {
				// 显示格式定义
				list ( $value ['title'], $value ['format'] ) = explode ( '|', $val [1] );
			}
			foreach ( $field as $val ) {
				$array = explode ( '|', $val );
				$fields [] = $array [0];
			}
		}
		// 过滤重复字段信息
		$fields = array_unique ( $fields );
		
		// 关键字搜索
		$list = M ( 'member_public_link' )->where ( "uid='{$this->mid}'" )->field ( 'mp_id,is_use' )->select ();
		foreach ( $list as $vo ) {
			$mp_ids [] = $vo ['mp_id'];
			$is_use [$vo ['mp_id']] = $vo ['is_use'];
		}
		$mp_ids = getSubByKey ( $list, 'mp_id' );
		
		$map ['id'] = 0;
		if (! empty ( $mp_ids )) {
			$map ['id'] = array (
					'in',
					$mp_ids 
			);
		}
		$key = $model ['search_key'] ? $model ['search_key'] : 'title';
		if (isset ( $_REQUEST [$key] )) {
			$map [$key] = array (
					'like',
					'%' . I ( $key ) . '%' 
			);
			unset ( $_REQUEST [$key] );
		}
		// 条件搜索
		foreach ( $_REQUEST as $name => $val ) {
			if (in_array ( $name, $fields )) {
				$map [$name] = $val;
			}
		}
		$row = empty ( $model ['list_row'] ) ? 20 : $model ['list_row'];
		
		// 读取模型数据列表
		empty ( $fields ) || in_array ( 'id', $fields ) || array_push ( $fields, 'id' );
		$name = parse_name ( get_table_name ( $model ['id'] ), true );
		$data = M ( $name )->where ( $map )->order ( 'id DESC' )->page ( $page, $row )->select ();
		
		foreach ( $data as &$vo ) {
			$vo ['is_use'] = $is_use [$vo ['id']];
			if (! empty ( $vo ['headface_url'] ))
				$vo ['headface_url'] = '<img src="' . get_cover_url ( $vo ['headface_url'] ) . '" width="50" height="50" />';
		}
		
		foreach ( $grids as $k => &$g ) {
			if ($g ['field'] [0] == 'uid') {
				unset ( $grids [$k] );
			}
		}
		
		/* 查询记录总数 */
		$count = M ( $name )->where ( $map )->count ();
		
		// 分页
		if ($count > $row) {
			$page = new \Think\Page ( $count, $row );
			$page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
			$_page = $page->show ();
			$this->assign ( '_page', $_page );
		}
		
		$this->assign ( 'list_grids', $grids );
		$this->assign ( 'list_data', $data );
		$this->meta_title = $model ['title'] . '列表';
		
		// 使用提示
		$normal_tips = '您目前最多可自己创建' . getPublicMax ( $this->mid ) . '个公众号（不包括管理员分配的公众号）。如需要更多名额请需要管理员在后台设置';
		$this->assign ( 'normal_tips', $normal_tips );
		
		$this->_display ();
	}
	public function del($model = null, $ids = null) {
		$model = $this->model;
		
		if (empty ( $ids )) {
			$ids = I ( 'id' );
		}
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
			$map_link ['mp_id'] = array (
					'in',
					$ids 
			);
			M ( 'member_public_link' )->where ( $map_link )->delete ();
			
			$this->success ( '删除成功' );
		} else {
			$this->error ( '删除失败！' );
		}
	}
	public function edit($model = null, $id = 0) {
		$model = $this->model;
		$id || $id = I ( 'id' );
		
		if (IS_POST) {
			$Model = D ( parse_name ( get_table_name ( $model ['id'] ), 1 ) );
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $model ['id'] );
			if ($Model->create () && $Model->save ()) {
				$this->success ( '保存' . $model ['title'] . '成功！', U ( 'lists?model=' . $model ['name'] ) );
			} else {
				$this->error ( $Model->getError () );
			}
		} else {
			$fields = get_model_attribute ( $model ['id'] );
			
			// 获取数据
			$data = M ( get_table_name ( $model ['id'] ) )->find ( $id );
			$data || $this->error ( '数据不存在！' );
			
			$this->assign ( 'fields', $fields );
			$this->assign ( 'data', $data );
			$this->meta_title = '编辑' . $model ['title'];
			$this->_display ( $model ['template_edit'] ? $model ['template_edit'] : '' );
		}
	}
	public function add($model = null) {
		$model = $this->model;
		if (IS_POST) {
			$_POST ['token'] = $_POST ['public_id'];
			$_POST ['group_id'] = intval ( C ( 'DEFAULT_PUBLIC_GROUP_ID' ) );
			$Model = D ( parse_name ( get_table_name ( $model ['id'] ), 1 ) );
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $model ['id'] );
			if ($Model->create () && $id = $Model->add ()) {
				// 增加公众号与用户的关联关系
				$data ['uid'] = $this->mid;
				$data ['mp_id'] = $id;
				$data ['is_creator'] = 1;
				M ( 'member_public_link' )->add ( $data );
				
				$this->success ( '添加' . $model ['title'] . '成功！', U ( 'lists?model=' . $model ['name'] ) );
			} else {
				$this->error ( $Model->getError () );
			}
		} else {
			$allow_add_count = getPublicMax ( $this->mid );
			$has_add_count = M ( 'member_public_link' )->where ( "uid='{$this->mid}'" )->getField ( 'sum(is_creator)' );
			if ($allow_add_count <= $has_add_count) {
				$this->error ( '您最多只能创建 ' . $allow_add_count . ' 个公众号！' );
				exit ();
			}
			
			$fields = get_model_attribute ( $model ['id'] );
			
			$this->assign ( 'fields', $fields );
			$this->meta_title = '新增' . $model ['title'];
			$this->_display ( $model ['template_add'] ? $model ['template_add'] : '' );
		}
	}
	protected function checkAttr($Model, $model_id) {
		$fields = get_model_attribute ( $model_id, false );
		$validate = $auto = array ();
		foreach ( $fields as $key => $attr ) {
			if ($attr ['is_must']) { // 必填字段
				$validate [] = array (
						$attr ['name'],
						'require',
						$attr ['title'] . '必须!' 
				);
			}
			// 自动验证规则
			if (! empty ( $attr ['validate_rule'] ) || $attr['validate_type']=='unique') {
				$validate [] = array (
						$attr ['name'],
						$attr ['validate_rule'],
						$attr ['error_info'] ? $attr ['error_info'] : $attr ['title'] . '验证错误',
						0,
						$attr ['validate_type'],
						$attr ['validate_time'] 
				);
			}
			// 自动完成规则
			if (! empty ( $attr ['auto_rule'] )) {
				$auto [] = array (
						$attr ['name'],
						$attr ['auto_rule'],
						$attr ['auto_time'],
						$attr ['auto_type'] 
				);
			} elseif ('checkbox' == $attr ['type']) { // 多选型
				$auto [] = array (
						$attr ['name'],
						'arr2str',
						3,
						'function' 
				);
			} elseif ('datetime' == $attr ['type']) { // 日期型
				$auto [] = array (
						$attr ['name'],
						'strtotime',
						3,
						'function' 
				);
			}
		}
		return $Model->validate ( $validate )->auto ( $auto );
	}
	function changPublic() {
		$map ['id'] = I ( 'id', 0, 'intval' );
		$info = M ( 'member_public' )->where ( $map )->find ();
		
		unset ( $map );
		$map ['uid'] = session ( 'mid' );
		$res = M ( 'member_public_link' )->where ( $map )->setField ( 'is_use', 0 );
		
		$map ['mp_id'] = $info ['id'];
		$res = M ( 'member_public_link' )->where ( $map )->setField ( 'is_use', 1 );
		
		session ( 'token', $info ['public_id'] );
		
		redirect ( U ( 'lists' ) );
	}
}