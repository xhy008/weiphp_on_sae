<?php

namespace Addons\Extensions\Model;

use Home\Model\WeixinModel;

/**
 * Extensions的微信模型
 */
class WeixinAddonModel extends WeixinModel {
	function reply($dataArr, $keywordArr = array()) {
		$map ['id'] = $keywordArr ['aim_id'];
		$info = M ( 'extensions' )->where ( $map )->find ();
		
		if ($info ['output_format'] == 1) {
			if ($info ['keyword_filter']) {
				$dataArr ['Content'] = trim ( str_replace ( $keywordArr ['keyword'], '', $dataArr ['Content'] ) );
			}
			$post_data = json_encode ( $dataArr );
		} else {
			$post_data = $GLOBALS ['HTTP_RAW_POST_DATA'];
			if ($info ['keyword_filter']) {
				$content = trim ( str_replace ( $keywordArr ['keyword'], '', $dataArr ['Content'] ) );
				$post_data = str_replace ( '<Content><![CDATA[' . $dataArr ['Content'] . ']]></Content>', '<Content><![CDATA[' . $content . ']]></Content>', $post_data );
			}
		}
		
		// dump($post_data);
		// dump($info);
		$header [] = "Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0";
		$header [] = "Content-Type: text/xml; charset=utf-8"; // 定义content-type为xml
		$ch = curl_init (); // 初始化curl
		curl_setopt ( $ch, CURLOPT_URL, $info ['api_url'] ); // 设置链接
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 ); // 设置是否返回信息
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, $header ); // 设置HTTP头
		curl_setopt ( $ch, CURLOPT_POST, 1 ); // 设置为POST方式
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_data ); // POST数据
		$response = curl_exec ( $ch ); // 接收返回信息
		if (curl_errno ( $ch )) { // 出错则显示错误信息
			print curl_error ( $ch );
		}
		curl_close ( $ch );
		// dump($response);
		
		echo ($response);
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
        	