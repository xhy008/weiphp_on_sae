<?php

namespace Addons\WeiSite\Controller;

use Addons\WeiSite\Controller\BaseController;

class TemplateController extends BaseController {
	function _initialize() {
		parent::_initialize ();
		
		// 子导航
		$action = strtolower ( _ACTION );
		
		$res ['title'] = '首页模板';
		$res ['url'] = addons_url ( 'WeiSite://template/index' );
		$res ['class'] = $action == 'index' ? 'cur' : '';
		$nav [] = $res;
		
		$res ['title'] = '图文列表模板';
		$res ['url'] = addons_url ( 'WeiSite://template/lists' );
		$res ['class'] = $action == 'lists' ? 'cur' : '';
		$nav [] = $res;
		
		$res ['title'] = '图文内容模板';
		$res ['url'] = addons_url ( 'WeiSite://template/detail' );
		$res ['class'] = $action == 'detail' ? 'cur' : '';
		$nav [] = $res;
		
		$res ['title'] = '底部菜单模板';
		$res ['url'] = addons_url ( 'WeiSite://template/footer' );
		$res ['class'] = $action == 'footer' ? 'cur' : '';
		$nav [] = $res;
		
		$this->assign ( 'sub_nav', $nav );
	}
	
	// 首页模板
	function index() {
		// 使用提示
		$normal_tips = '点击选中下面模板即可实时切换模板，请慎重点击。选择后可点击<a target="_blank" href="' . addons_url ( 'WeiSite://WeiSite/index' ) . '">这里</a>进行预览';
		$this->assign ( 'normal_tips', $normal_tips );
		
		$this->_getTemplateByDir ();
		
		$this->display ();
	}
	// 分类列表模板
	function lists() {
		$this->_getTemplateByDir ( 'TemplateLists' );
		
		$this->display ( 'index' );
	}
	// 详情模板
	function detail() {
		$this->_getTemplateByDir ( 'TemplateDetail' );
		
		$this->display ( 'index' );
	}
	// 底部菜单模板
	function footer() {
		// 使用提示
		$normal_tips = '底部菜单的数据请在上面的“底部导航”的页面里增加';
		$this->assign ( 'normal_tips', $normal_tips );
		
		$this->_getTemplateByDir ( 'TemplateFooter' );
		
		$this->display ( 'index' );
	}
	
	// 保存切换的模板
	function save() {
		$act = I ( 'post.type' );
		$this->config ['template_' . $act] = I ( 'post.template' );
		D ( 'Common/AddonConfig' )->set ( _ADDONS, $this->config );
	}
	
	// 获取目录下的所有模板
	function _getTemplateByDir($type = 'TemplateIndex') {
		$action = strtolower ( _ACTION );
		$default = $this->config ['template_' . $action];
		
		$dir = ONETHINK_ADDON_PATH . _ADDONS . '/View/default/' . $type;
		$url = SITE_URL . '/Addons/' . _ADDONS . '/View/default/' . $type;
		
		$dirObj = opendir ( $dir );
		while ( $file = readdir ( $dirObj ) ) {
			if ($file === '.' || $file == '..' || $file == '.svn' || is_file ( $dir . '/' . $file ))
				continue;
			
			$res ['dirName'] = $res ['title'] = $file;
			
			// 获取配置文件
			if (file_exists ( $dir . '/' . $file . '/info.php' )) {
				$info = require_once $dir . '/' . $file . '/info.php';
				$res = array_merge ( $res, $info );
			}
			
			// 获取效果图
			if (file_exists ( $dir . '/' . $file . '/info.php' )) {
				$res ['icon'] = __ROOT__ . '/Addons/WeiSite/View/default/' . $type . '/' . $file . '/icon.png';
			} else {
				$res ['icon'] = ADDON_PUBLIC_PATH . '/default.png';
			}
			
			// 默认选中
			if ($default == $file) {
				$res ['class'] = 'selected';
				$res ['checked'] = 'checked="checked"';
			}
			
			$tempList [] = $res;
			unset ( $res );
		}
		closedir ( $dir );
		
		// dump ( $tempList );
		
		$this->assign ( 'tempList', $tempList );
	}
}
