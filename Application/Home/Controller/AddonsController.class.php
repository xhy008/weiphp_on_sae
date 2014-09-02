<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace Home\Controller;

use Think\Controller;

/**
 * 扩展控制器
 * 用于调度各个扩展的URL访问需求
 */
class AddonsController extends Controller {
	protected $addons = null;
	protected $addon, $model;
	function _initialize() {
		$this->initFollow ();
		
		C ( 'EDITOR_UPLOAD.rootPath', './Uploads/Editor/' . get_token () . '/' );
	}
	public function execute($_addons = null, $_controller = null, $_action = null) {
		if (! empty ( $_action ) && ! empty ( $_addons ) && empty ( $_controller )) {
			$_controller = $_GET ['_controller'] = $_addons;
			$_REQUEST ['_controller'] = $_REQUEST ['_addons'];
		}
		
		if (C ( 'URL_CASE_INSENSITIVE' )) {
			$_addons = ucfirst ( parse_name ( $_addons, 1 ) );
			$_controller = parse_name ( $_controller, 1 );
		}
		
		define ( 'ADDON_PUBLIC_PATH', __ROOT__ . '/Addons/' . $_addons . '/View/default/Public' );
		defined ( '_ADDONS' ) or define ( '_ADDONS', $_addons );
		defined ( '_CONTROLLER' ) or define ( '_CONTROLLER', $_controller );
		defined ( '_ACTION' ) or define ( '_ACTION', $_action );
		
		$token = get_token ();
		if (in_array ( $_action, array (
				'lists',
				'config',
				'nulldeal' 
		) ) && (empty ( $token ) || $token == '-1')) {
			$this->error ( '请先增加公众号！', U ( 'Home/MemberPublic/lists' ) );
		}
		
		$this->_nav ();
		
		if (! empty ( $_addons ) && ! empty ( $_controller ) && ! empty ( $_action )) {
			tongji ( $_addons );
			
			A ( "Addons://{$_addons}/{$_controller}" )->$_action ();
		} else {
			$this->error ( '没有指定插件名称，控制器或操作！' );
		}
	}
	function _nav() {
		$map ['name'] = _ADDONS;
		$this->addon = $addon = M ( 'Addons' )->where ( $map )->find ();
		
		$nav = array ();
		if ($addon ['has_adminlist']) {
			$res ['title'] = $addon ['title'];
			$res ['url'] = U ( 'lists' );
			$res ['class'] = _ACTION == 'lists' ? 'current' : '';
			$nav [] = $res;
		}
		if (file_exists ( ONETHINK_ADDON_PATH . _ADDONS . '/config.php' )) {
			$res ['title'] = '功能配置';
			$res ['url'] = U ( 'config' );
			$res ['class'] = _ACTION == 'config' ? 'current' : '';
			$nav [] = $res;
		}
		if (empty ( $nav ) && _ACTION != 'nulldeal') {
			U ( 'nulldeal', '', true );
		}
		$this->assign ( 'nav', $nav );
		
		return $nav;
	}
	/**
	 * 重写模板显示 调用内置的模板引擎显示方法，
	 *
	 * @access protected
	 * @param string $templateFile
	 *        	指定要调用的模板文件
	 *        	默认为空 由系统自动定位模板文件
	 *        	支持格式: 空, index, UserCenter/index 和 完整的地址
	 * @param string $charset
	 *        	输出编码
	 * @param string $contentType
	 *        	输出类型
	 * @param string $content
	 *        	输出内容
	 * @param string $prefix
	 *        	模板缓存前缀
	 * @return void
	 */
	protected function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = '') {
		$templateFile = $this->getAddonTemplate ( $templateFile );
		$this->view->display ( $templateFile, $charset, $contentType, $content, $prefix );
	}
	function getAddonTemplate($templateFile = '') {
		if (file_exists ( $templateFile )) {
			return $templateFile;
		}
		// dump ( $templateFile );
		$oldFile = $templateFile;
		if (empty ( $templateFile )) {
			$templateFile = T ( 'Addons://' . _ADDONS . '@' . _CONTROLLER . '/' . _ACTION );
		} elseif (stripos ( $templateFile, '/Addons/' ) === false && stripos ( $templateFile, THINK_PATH ) === false) {
			if (stripos ( $templateFile, '/' ) === false) { // 如index
				$templateFile = T ( 'Addons://' . _ADDONS . '@' . _CONTROLLER . '/' . $templateFile );
			} elseif (stripos ( $templateFile, '@' ) === false) { // // 如 UserCenter/index
				$templateFile = T ( 'Addons://' . _ADDONS . '@' . $templateFile );
			}
		}
		
		if (stripos ( $templateFile, '/Addons/' ) !== false && ! file_exists ( $templateFile )) {
			$templateFile = ! empty ( $oldFile ) && stripos ( $oldFile, '/' ) === false ? $oldFile : _ACTION;
		}
		// dump ( $templateFile );//exit;
		return $templateFile;
	}
	
	// 通用插件的列表模型
	public function lists($model = null, $page = 0) {
		is_array ( $model ) || $model = $this->getModel ( $model );
		$templateFile = $this->getAddonTemplate ( $model ['template_list'] );
		parent::common_lists ( $model, $page, $templateFile );
	}
	
	// 通用插件的编辑模型
	public function edit($model = null, $id = 0) {
		is_array ( $model ) || $model = $this->getModel ( $model );
		$templateFile = $this->getAddonTemplate ( $model ['template_edit'] );
		parent::common_edit ( $model, $id, $templateFile );
	}
	
	// 通用插件的增加模型
	public function add($model = null) {
		is_array ( $model ) || $model = $this->getModel ( $model );
		$templateFile = $this->getAddonTemplate ( $model ['template_add'] );
		
		parent::common_add ( $model, $templateFile );
	}
	
	// 通用插件的删除模型
	public function del($model = null, $ids = null) {
		parent::common_del ( $model, $ids );
	}
	
	// 通用设置插件模型
	public function config() {
		$this->getModel ();
		if (IS_POST) {
			$flag = D ( 'Common/AddonConfig' )->set ( _ADDONS, I ( 'config' ) );
			
			if ($flag !== false) {
				$this->success ( '保存成功', Cookie ( '__forward__' ) );
			} else {
				$this->error ( '保存失败' );
			}
		}
		
		$map ['name'] = _ADDONS;
		$addon = M ( 'Addons' )->where ( $map )->find ();
		if (! $addon)
			$this->error ( '插件未安装' );
		$addon_class = get_addon_class ( $addon ['name'] );
		if (! class_exists ( $addon_class ))
			trace ( "插件{$addon['name']}无法实例化,", 'ADDONS', 'ERR' );
		$data = new $addon_class ();
		$addon ['addon_path'] = $data->addon_path;
		$addon ['custom_config'] = $data->custom_config;
		$this->meta_title = '设置插件-' . $data->info ['title'];
		$db_config = D ( 'Common/AddonConfig' )->get ( _ADDONS );
		$addon ['config'] = include $data->config_file;
		if ($db_config) {
			foreach ( $addon ['config'] as $key => $value ) {
				if ($value ['type'] != 'group') {
					! isset ( $db_config [$key] ) || $addon ['config'] [$key] ['value'] = $db_config [$key];
				} else {
					foreach ( $value ['options'] as $gourp => $options ) {
						foreach ( $options ['options'] as $gkey => $value ) {
							! isset ( $db_config [$key] ) || $addon ['config'] [$key] ['options'] [$gourp] ['options'] [$gkey] ['value'] = $db_config [$gkey];
						}
					}
				}
			}
		}
		$this->assign ( 'data', $addon );
		// dump($addon);
		if ($addon ['custom_config'])
			$this->assign ( 'custom_config', $this->fetch ( $addon ['addon_path'] . $addon ['custom_config'] ) );
		$this->display ();
	}
	
	// 没有管理页面和配置页面的插件的通用提示页面
	function nulldeal() {
		$this->display ( T ( 'home/Addons/nulldeal' ) );
	}
	function mobileForm() {
		defined ( '_ACTION' ) or define ( '_ACTION', 'mobileForm' );
		
		$model = $this->getModel ( $model );
		
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
			$id = I ( 'id' );
			$data = M ( get_table_name ( $model ['id'] ) )->find ( $id );
			
			$this->assign ( 'fields', $fields );
			$this->assign ( 'data', $data );
			$this->meta_title = '编辑' . $model ['title'];
			$this->display ( './Application/Home/View/default/Addons/mobileForm.html' );
		}
	}
}
