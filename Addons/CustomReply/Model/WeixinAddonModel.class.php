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

			// 根据用户选择的id顺序来显示结果
			$this->sortByMultIds($list, $mult ['mult_ids']);

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
			$contetn = replace_url ( htmlspecialchars_decode ( $info ['content'] ) );
			$this->replyText ( $contetn );
		}
	}

	function sortByMultIds(&$list, $ids) {
		$idArray = explode ( ',', $ids );
		foreach ($idArray as $i => $id) {
			foreach ( $list as $k => $info ) {
				if ($info['id'] == $id) {
					if ($i != $k) {
						$tmp = $list[$i];
						$list[$i] = $list[$k];
						$list[$k] = $tmp;
					}
					break;
				}
			}
		}
	}

	function _getNewsUrl($info, $param) {
		if (! empty ( $info ['jump_url'] )) {
			$url = replace_url ( $info ['jump_url'] );
		} else {
			$param ['id'] = $info ['id'];
			$url = addons_url ( 'CustomReply://CustomReply/detail', $param );
		}
		return $url;
	}
	// 上报地理位置事件 感谢网友【blue7wings】和【strivi】提供的方案
	public function location($dataArr) {
		$latitude = $dataArr ['Location_X'];
		$longitude = $dataArr ['Location_Y'];
		$pos = file_get_contents ( 'http://lbs.juhe.cn/api/getaddressbylngb?lngx=' . $latitude . '&lngy=' . $longitude );
		$pos_ar = json_decode ( $pos, true );
		$this->replyText ( htmlspecialchars_decode ( $pos_ar ['row'] ['result'] ['formatted_address'] ) );
		return true;
	}
}
        	