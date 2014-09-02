<?php

namespace Addons\Forms\Controller;

use Addons\Forms\Controller\BaseController;

class FormsValueController extends BaseController {
	var $model;
	var $forms_id;
	function _initialize() {
		parent::_initialize ();
		
		$this->model = $this->getModel ( 'forms_value' );
		
		$param ['forms_id'] = $this->forms_id = intval ( $_REQUEST ['forms_id'] );
		
		$res ['title'] = '通用表单';
		$res ['url'] = addons_url ( 'Forms://Forms/lists' );
		$res ['class'] = '';
		$nav [] = $res;
		
		$res ['title'] = '数据管理';
		$res ['url'] = addons_url ( 'Forms://FormsValue/lists', $param );
		$res ['class'] = 'current';
		$nav [] = $res;
		
		$this->assign ( 'nav', $nav );
	}
	
	// 通用插件的列表模型
	public function lists() {
		// 解析列表规则
		$fields [] = 'openid';
		$fields [] = 'cTime';
		$fields [] = 'forms_id';
		
		$girds ['field'] [0] = 'openid';
		$girds ['title'] = 'OpenId';
		$list_data ['list_grids'] [] = $girds;
		
		$girds ['field'] [0] = 'cTime|time_format';
		$girds ['title'] = '增加时间';
		$list_data ['list_grids'] [] = $girds;
		
		$map ['forms_id'] = $this->forms_id;
		$attribute = M ( 'forms_attribute' )->where ( $map )->order('sort asc, id asc')->select ();
		foreach ( $attribute as $vo ) {
			$girds ['field'] [0] = $fields [] = $vo ['name'];
			$girds ['title'] = $vo ['title'];
			$list_data ['list_grids'] [] = $girds;
			
			$attr [$vo ['name']] ['type'] = $vo ['type'];
			
			if ($vo ['type'] == 'radio' || $vo ['type'] == 'checkbox' || $vo ['type'] == 'select') {
				$extra = parse_config_attr ( $vo ['extra'] );
				if (is_array ( $extra ) && ! empty ( $extra )) {
					$attr [$vo ['name']] ['extra'] = $extra;
				}
			} elseif ($vo ['type'] == 'cascade') {
				$attr [$vo ['name']] ['extra'] = $vo ['extra'];
			}
		}
		
		$fields [] = 'id';
		$girds ['field'] [0] = 'id';
		$girds ['title'] = '操作';
		$girds ['href'] = '[EDIT]&forms_id=[forms_id]|编辑,[DELETE]&forms_id=[forms_id]|	删除';
		$list_data ['list_grids'] [] = $girds;
		
		$list_data ['fields'] = $fields;
		
		$param ['forms_id'] = $this->forms_id;
		$param ['model'] = $this->model ['id'];
		$add_url = U ( 'add', $param );
		$this->assign ( 'add_url', $add_url );
		
		// 搜索条件
		$map = $this->_search_map ( $this->model, $fields );
		
		$page = I ( 'p', 1, 'intval' );
		$row = 20;
		
		$name = parse_name ( get_table_name ( $this->model ['id'] ), true );
		$list = M ( $name )->where ( $map )->order ( 'id DESC' )->selectPage ();
		$list_data = array_merge ( $list_data, $list );
		
		foreach ( $list_data ['list_data'] as &$vo ) {
			$value = unserialize ( $vo ['value'] );
			foreach ( $value as $n => &$d ) {
				$type = $attr [$n] ['type'];
				$extra = $attr [$n] ['extra'];
				if ($type == 'radio' || $type == 'select') {
					if (isset ( $extra [$d] )) {
						$d = $extra [$d];
					}
				} elseif ($type == 'checkbox') {
					foreach ( $d as &$v ) {
						if (isset ( $extra [$v] )) {
							$v = $extra [$v];
						}
					}
					$d = implode ( ', ', $d );
				} elseif ($type == 'datetime') {
					$d = time_format ( $d );
				} elseif ($type == 'picture') {
					$d = get_cover_url ( $d );
				} elseif ($type == 'cascade') {
					$d = getCascadeTitle ( $d, $extra );
				}
			}
			
			unset ( $vo ['value'] );
			$vo = array_merge ( $vo, $value );
		}
		
		$this->assign ( $list_data );
		// dump ( $list_data );
		
		$this->display ();
	}
	
	// 通用插件的编辑模型
	public function edit() {
		$this->add ();
	}
	
	// 通用插件的增加模型
	public function add() {
		$id = I ( 'id', 0 );
		
		$forms = M ( 'forms' )->find ( $this->forms_id );
		$forms ['cover'] = ! empty ( $forms ['cover'] ) ? get_cover_url ( $forms ['cover'] ) : ADDON_PUBLIC_PATH . '/background.png';
		$this->assign ( 'forms', $forms );
		
		if (! empty ( $id )) {
			$act = 'save';
			
			$data = M ( get_table_name ( $this->model ['id'] ) )->find ( $id );
			$data || $this->error ( '数据不存在！' );
			
			// dump($data);
			$value = unserialize ( htmlspecialchars_decode ( $data ['value'] ) );
			// dump($value);
			unset ( $data ['value'] );
			$data = array_merge ( $data, $value );
			
			$this->assign ( 'data', $data );
			// dump($data);
		} else {
			$act = 'add';
			if ($this->mid != 0 && $this->mid != '-1') {
				$map ['uid'] = $this->mid;
				$map ['forms_id'] = $this->forms_id;
				
				$data = M ( get_table_name ( $this->model ['id'] ) )->where ( $map )->find ();
				if ($data && $forms ['jump_url']) {
					redirect ( $forms ['jump_url'] );
				}
			}
		}
		
		// dump ( $forms );
		
		$map ['forms_id'] = $this->forms_id;
		$map ['token'] = get_token ();
		$fields [1] = M ( 'forms_attribute' )->where ( $map )->order ( 'sort asc, id asc' )->select ();
		
		if (IS_POST) {
			foreach ( $fields [1] as $vo ) {
				$error_tip = ! empty ( $vo ['error_info'] ) ? $vo ['error_info'] : '请正确输入' . $vo ['title'] . '的值';
				$value = $_POST [$vo ['name']];
				if (($vo ['is_must'] && empty ( $value )) || (! empty ( $vo ['validate_rule'] ) && ! M()->regex ( $value, $vo ['validate_rule'] ))) {
					$this->error ( $error_tip );
					exit ();
				}

				$post [$vo ['name']] = $vo ['type'] == 'datetime' ? strtotime ( $_POST [$vo ['name']] ) : $_POST [$vo ['name']];
				unset ( $_POST [$vo ['name']] );
			}
			
			$_POST ['value'] = serialize ( $post );
			$act == 'add' && $_POST ['uid'] = $this->mid;
			// dump($_POST);exit;
			$Model = D ( parse_name ( get_table_name ( $this->model ['id'] ), 1 ) );
			
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $this->model ['id'], $fields [1] );
			
			if ($Model->create () && $res = $Model->$act ()) {
				// 增加积分
				add_credit ( 'forms' );
				
				$param ['forms_id'] = $this->forms_id;
				$param ['id'] = $act == 'add' ? $res : $id;
				$param ['model'] = $this->model ['id'];
				$url = empty ( $forms ['jump_url'] ) ? U ( 'edit', $param ) : $forms ['jump_url'];
				
				$tip = ! empty ( $forms ['finish_tip'] ) ? $forms ['finish_tip'] : '提交成功，谢谢参与';
				$this->success ( $tip, $url, 5 );
			} else {
				$this->error ( $Model->getError () );
			}
			exit ();
		}
		
		$fields [1] [] = array (
				'is_show' => 4,
				'name' => 'forms_id',
				'value' => $this->forms_id 
		);
		
		$this->assign ( 'fields', $fields );
		$this->meta_title = '新增' . $this->model ['title'];
		
		$this->display ( 'add' );
	}
	function detail() {
		$forms = M ( 'forms' )->find ( $this->forms_id );
		$forms ['cover'] = ! empty ( $forms ['cover'] ) ? get_cover_url ( $forms ['cover'] ) : ADDON_PUBLIC_PATH . '/background.png';
		$this->assign ( 'forms', $forms );
		
		$this->display ();
	}
	
	// 通用插件的删除模型
	public function del() {
		parent::common_del ( $this->model );
	}
}
