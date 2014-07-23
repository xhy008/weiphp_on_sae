<?php

namespace Addons\CustomReply\Model;

use Home\Model\WeixinModel;

/**
 * CustomReply的微信模型
 */
class WeixinAddonModel extends WeixinModel {
	function reply($dataArr, $keywordArr = array()) {
		$map ['id'] = $keywordArr ['aim_id'];
		$param ['token'] = get_token ();
		$param ['openid'] = get_openid ();
		
		if ($keywordArr ['extra_text'] == 'custom_reply_mult') {
			// 多图文回复
			$mult = M ( 'custom_reply_mult' )->where ( $map )->find ();
			$map_news ['id'] = array (
					'in',
					$mult ['mult_ids'] 
			);
			$list = M ( 'custom_reply_news' )->where ( $map_news )->select ();
			
			foreach ( $list as $k => $info ) {
				if ($k > 8)
					continue;
				
				$articles [] = array (
						'Title' => $info ['title'],
						'Description' => $info ['intro'],
						'PicUrl' => get_cover_url ( $info ['cover'] ),
						'Url' => $this->_getNewsUrl ( $info, $param ) 
				);
			}
			
			$res = $this->replyNews ( $articles );
		} elseif ($keywordArr ['extra_text'] == 'custom_reply_news') {
			// 单条图文回复
			$info = M ( 'custom_reply_news' )->where ( $map )->find ();
			
			// 组装微信需要的图文数据，格式是固定的
			$articles [0] = array (
					'Title' => $info ['title'],
					'Description' => $info ['intro'],
					'PicUrl' => get_cover_url ( $info ['cover'] ),
					'Url' => $this->_getNewsUrl ( $info, $param ) 
			);
			
			$res = $this->replyNews ( $articles );
		} else {
			// 增加积分
			add_credit ( 'custom_reply', 300 );
			
			// 文本回复
			$info = M ( 'custom_reply_text' )->where ( $map )->find ();
			$this->replyText ( htmlspecialchars_decode ( $info ['content'] ) );
		}
	}
	function _getNewsUrl($info, $param) {
		if (! empty ( $info ['jump_url'] )) {
			$url = $info ['jump_url'];
		} else {
			$param ['id'] = $info ['id'];
			$url = addons_url ( 'CustomReply://CustomReply/detail', $param );
		}
		return $url;
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
        	