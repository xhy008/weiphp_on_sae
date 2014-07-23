<?php

namespace Addons\Survey\Model;

use Home\Model\WeixinModel;

/**
 * Survey的微信模型
 */
class WeixinAddonModel extends WeixinModel {
	function reply($dataArr, $keywordArr = array()) {
		$map ['token'] = get_token ();
		if (! empty ( $keywordArr ['aim_id'] )) {
			$map ['id'] = $keywordArr ['aim_id'];
		}
		
		$info = M ( 'survey' )->where ( $map )->order ( 'id desc' )->find ();
		if (! $info) {
			return false;
		}
		
		// 其中token和openid这两个参数一定要传，否则程序不知道是哪个微信用户进入了系统
		$param ['survey_id'] = $info ['id'];
		$param ['token'] = get_token ();
		$param ['openid'] = get_openid ();
		$url = addons_url ( 'Survey://Survey/show', $param );
		
		// 组装微信需要的图文数据，格式是固定的
		$articles [0] = array (
				'Title' => $info ['title'],
				'Description' => $info ['intro'],
				'PicUrl' => get_cover_url ( $info ['cover'] ),
				'Url' => $url 
		);
		
		$res = $this->replyNews ( $articles );
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
	
	// 自定义菜单事件
	public function click() {
		return true;
	}
}
        	