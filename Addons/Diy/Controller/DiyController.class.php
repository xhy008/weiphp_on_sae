<?php

namespace Addons\Diy\Controller;

use Home\Controller\AddonsController;

class DiyController extends AddonsController {
	function lists($page = 0) {
		// 获取模型信息
		$model = $this->getModel ();
		
		$map ['module'] = _ADDONS;
		session ( 'common_condition', $map );
		
		$list_data = $this->_get_model_list ( $model, $page );
		$this->assign ( $list_data );
		
		$this->display ();
	}
	
	// DIY排版页面
	function diy() {
		if (IS_POST) {
			$map ['id'] = intval ( $_REQUEST ['id'] );
			$map ['token'] = get_token ();
			
			$page = M ( 'diy' )->where ( $map )->find ();
			$olds = unserialize ( htmlspecialchars_decode ( $page ['layout'] ) );
			foreach ( $olds as $old ) {
				foreach ( $old ['widgets'] as $w ) {
					$old_widgets [$w ['widget_id']] = $w;
				}
			}
			
			$param_cache = S ( 'diy_' . $map ['id'] );
			$layouts = json_decode ( $_POST ['layouts'], true );
			// dump ( $layouts );
			foreach ( $layouts as &$layout ) {
				foreach ( $layout ['widgets'] as &$widget ) {
					$widget_id = ( string ) $widget ['widget_id'];
					$param = isset ( $param_cache [$widget_id] ) ? $param_cache [$widget_id] : $old_widgets [$widget_id];
					
					$widget = array_merge ( $widget, $param );
				}
			}
			// dump ( $layouts );
			$data ['layout'] = htmlspecialchars ( serialize ( $layouts ) );
			$data ['mTime'] = time ();
			// exit ();
			$res = M ( 'diy' )->where ( $map )->save ( $data );
			if ($res) {
				$url = U ( 'preview', $map );
				$this->success ( '保存成功', $url );
			} else {
				$this->error ( '配置失败' );
			}
		}
		$this->_getPage ( $_REQUEST ['id'] );
		
		// 获取全部widget列表
		$widget_dir = SITE_PATH . '/Addons/Diy/Widget/';
		$dirs = array_map ( 'basename', glob ( $widget_dir . '*', GLOB_ONLYDIR ) );
		foreach ( $dirs as $name ) {
			$path = $widget_dir . $name . '/DealController.class.php';
			if (! file_exists ( $path ))
				continue;
			
			require_once $path;
			$class = 'Addons\Diy\Widget\\' . $name . '\DealController';
			$action = new $class ();
			
			if (! method_exists ( $action, 'info' ))
				continue;
			
			$info = $action->info ();
			if ($info ['hidden'] == 1)
				continue;
				
				// 初始化默认值
			$url_param ['widget'] = $name;
			$info ['name'] = $name;
			empty ( $info ['icon'] ) && $info ['icon'] = C ( 'TMPL_PARSE_STRING.__IMG__' ) . '/m/icon_pic.png';
			empty ( $info ['set'] ) && $info ['set'] = addons_url ( "Diy://Diy/param", $url_param );
			empty ( $info ['save'] ) && $info ['save'] = addons_url ( "Diy://widget/saveTemplateCode" );
			empty ( $info ['html'] ) && $info ['html'] = addons_url ( "Diy://Widget/getTemplateCode" );
			
			$widget_list [] = $info;
		}
		$this->assign ( 'widget_list', $widget_list );
		
		S ( 'diy_' . $_REQUEST ['id'], null );
		
		$this->display ();
	}
	
	// 模块参数配置
	function param() {
		if (IS_POST) {
			echo $this->_get_widget_html ( $_POST );
			// dump($_POST);
			// dump($this->_get_widget_html ( $_POST ));
			// 缓存参数
			if (! $_REQUEST ['preview']) {
				$param_cache = S ( 'diy_' . $_REQUEST ['page_id'] );
				$param_cache [$_REQUEST ['widget_id']] = $_POST;
				S ( 'diy_' . $_REQUEST ['page_id'], $param_cache );
			}
			exit ();
		}
		// 初始化数据
		$page_id = I ( 'get.page_id', 0, 'intval' );
		$widget_id = I ( 'get.widget_id' );
		if (! empty ( $page_id ) && ! empty ( $widget_id )) {
			// 先从缓存里取
			$param_cache = S ( 'diy_' . $page_id );
			$param = $param_cache [$widget_id];
			
			// 没有缓存则从数据库里取
			if (empty ( $param )) {
				$map ['id'] = $page_id;
				$map ['token'] = get_token ();
				$page = M ( 'diy' )->where ( $map )->find ();
				
				$layouts = unserialize ( htmlspecialchars_decode ( $page ['layout'] ) );
				unset ( $page ['layout'] );
				
				foreach ( $layouts as $layout ) {
					foreach ( $layout ['widgets'] as $widget ) {
						if ($widget ['widget_id'] == $widget_id) {
							$widget ['html'] = $this->_get_widget_html ( $widget );
							$param = $widget;
							break;
						}
					}
				}
			}
			// dump($param);
			$this->assign ( $param );
		}
		
		// 配置模板
		$widget = ucfirst ( I ( 'get.widget' ) );
		$templateFile = ONETHINK_ADDON_PATH . 'Diy/Widget/' . $widget . '/param.html';
		
		$this->display ( $templateFile );
	}
	
	// 万能页面显示
	function show($id = null) {
		$id || $id = intval ( $_REQUEST ['id'] );
		$this->_getPage ( $id,  true);
		
		$this->display ( T ( 'Addons://Diy@Diy/show' ) );
	}
	function _getPage($id, $admin=false) {
		$map ['id'] = intval ( $id );
		$map ['token'] = get_token ();
		$page = M ( 'diy' )->where ( $map )->find ();
		if (! $page) {
			$this->error ( '该页面不存在' );
		}
		if($admin){
			if ($page['is_close']==1) {
				$this->error ( '该页面已关闭' );
			}
			if ($page['need_login']==1 && !($this->mid>0)) {
				$this->error ( '该页面禁止游客访问' );
			}	
		}
		M ( 'diy' )->where ( $map )->setInc ( 'view_count' );
		
		$layouts = unserialize ( htmlspecialchars_decode ( $page ['layout'] ) );
		unset ( $page ['layout'] );
		$this->assign ( 'page', $page );
		
		foreach ( $layouts as &$layout ) {
			foreach ( $layout ['widgets'] as &$widget ) {
				$widget ['html'] = $this->_get_widget_html ( $widget );
			}
		}
		// dump($layouts);
		
		$this->assign ( 'layouts', $layouts );
	}
	// 获取Widget处理后展示的HTML
	private function _get_widget_html($widget) {
		static $_widget;
		$name = $widget ['widget_name'];
		
		$path = ONETHINK_ADDON_PATH . 'Diy/Widget/' . $name . '/DealController.class.php';
		
		if (isset ( $_widget [$name] )) {
			$action = $_widget [$name];
		} elseif (file_exists ( $path )) {
			require_once ONETHINK_ADDON_PATH . 'Diy/Widget/' . $name . '/DealController.class.php';
			$class = 'Addons\Diy\Widget\\' . $name . '\DealController';
			$_widget [$name] = $action = new $class ();
		}
		
		if (method_exists ( $action, 'show' )) {
			return $action->show ( $widget );
		} else {
			return '';
		}
	}
	function preview() {
		$this->_getPage ( $_REQUEST ['id'] );
		
		$this->display ();
	}
	function show1() {
		$this->display ();
	}
}
