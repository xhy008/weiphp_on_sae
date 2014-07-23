<?php

namespace Addons\Diy\Controller;

use Home\Controller\AddonsController;

class WidgetController extends AddonsController {
	// Widget必须重写show方法，否则返回一个空的HTML;
	function show($widget) {
		return '';
	}
	// 默认的参数配置页面
	function param($widget) {
		$this->assign ( 'widget', $widget );
		$this->display ();
	}
	// 获取模板完整地址
	function getWidgetTemplate($widget) {
		// 预定义模板
		$templateFile = ONETHINK_ADDON_PATH . 'Diy/Widget/' . $widget ['widget_name'] . '/' . $widget ['template'] . '.html';
		// dump ( $templateFile );
		// 如果预定义模板则获取自定义模板
		if (! file_exists ( $templateFile )) {
			$config = C ( 'ATTACHMENT_UPLOAD' );
			$templateFile = $config ['rootPath'] . 'Widget/' . $widget ['widget_name'] . '/' . $widget ['template'] . '.html';
		}
		// dump ( $templateFile );
		// 如果模板还不存在，直接返回
		if (! file_exists ( $templateFile ))
			$templateFile = ONETHINK_ADDON_PATH . 'Diy/Widget/' . $widget ['widget_name'] . '/simple.html';
		
		return $templateFile;
	}
	// 获取Widget渲染后的HTML
	function getWidgetHtml($widget) {
		$this->assign ( 'widget', $widget );
		$this->assign ( $widget ); // 兼容旧模式
		$templateFile = $this->getWidgetTemplate ( $widget );
		// dump($templateFile);
		
		$content = $this->fetch ( $templateFile );
		// dump ( $content );
		
		return $content;
	}
	
	// 获取模板代码
	function getTemplateCode() {
		$name = I ( 'widget_name' );
		$template = I ( 'template' );
		$templateFile = ONETHINK_ADDON_PATH . 'Diy/Widget/' . $name . '/' . $template . '.html';
		
		// 如果预定义模板则获取自定义模板
		if (! file_exists ( $templateFile )) {
			$config = C ( 'ATTACHMENT_UPLOAD' );
			$templateFile = $config ['rootPath'] . 'Widget/' . $name . '/' . $template . '.html';
		}
		
		// 如果模板还不存在，直接返回
		if (! file_exists ( $templateFile ))
			$templateFile = ONETHINK_ADDON_PATH . 'Diy/Widget/Picture/simple.html'; // TODO
		
		echo file_get_contents ( $templateFile );
	}
	// 保存自定义模板
	function saveTemplateCode() {
		$name = I ( 'widget_name' );
		$widget_id = I ( 'widget_id' );
		$code = $_POST ['code'];
		
		$config = C ( 'ATTACHMENT_UPLOAD' );
		$dir = $config ['rootPath'] . 'Widget/' . $name . '/';
		if (! is_dir ( $dir )) {
			mkdirs ( $dir );
		}
		
		$templateFile = $dir . $widget_id . '.html';
		
		if (! file_exists ( $templateFile ))
			unlink ( $templateFile );
		
		echo file_put_contents ( $templateFile, $code );
	}
}
