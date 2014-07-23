<?php

namespace Addons\CustomMenu\Model;

use Home\Model\WeixinModel;

/**
 * CustomMenu的微信模型
 */
class WeixinAddonModel extends WeixinModel {
	function reply($dataArr, $keywordArr = array()) {
		$config = getAddonConfig ( 'CustomMenu' ); // 获取后台插件的配置参数
			                                           // dump($config);
	}
	
	// 关注公众号事件
	public function subscribe() {
		return true;
	}
	
	// 取消关注公众号事件
	public function unsubscribe() {
		return true;
	}
	
	// 扫描带参数二维码事件
	public function scan() {
		return true;
	}
	
	// 上报地理位置事件
	public function location() {
		return true;
	}
	
	// 自定义菜单关键词事件
	public function click() {
		return true;
	}
	// 自定义菜单连接事件
	public function view($data) {
		redirect ( $data ['EventKey'] );
	}
}