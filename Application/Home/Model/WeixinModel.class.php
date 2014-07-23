<?php

namespace Home\Model;

use Think\Model;

/**
 * 微信基础模型
 */
class WeixinModel extends Model {
	var $data = array ();
	public function __construct() {
		if ($_REQUEST ['doNotInit'])
			return true;
		
		$content = file_get_contents ( 'php://input' );
		! empty ( $content ) || die ( '这是微信请求的接口地址，直接在浏览器里无效' );
		$data = new \SimpleXMLElement ( $content );
		// $data || die ( '参数获取失败' );
		foreach ( $data as $key => $value ) {
			$this->data [$key] = strval ( $value );
		}
	}
	/* 获取微信平台请求的信息 */
	public function getData() {
		return $this->data;
	}
	/* ========================发送被动响应消息 begin================================== */
	/* 回复文本消息 */
	public function replyText($content) {
		$msg ['Content'] = $content;
		$this->_replyData ( $msg, 'text' );
	}
	/* 回复图片消息 */
	public function replyImage($media_id) {
		$msg ['Image'] ['MediaId'] = $media_id;
		$this->_replyData ( $msg, 'image' );
	}
	/* 回复语音消息 */
	public function replyVoice($media_id) {
		$msg ['Voice'] ['MediaId'] = $media_id;
		$msg ['Voice'] ['MediaId'] = $media_id;
		$this->_replyData ( $msg, 'voice' );
	}
	/* 回复视频消息 */
	public function replyVideo($media_id, $title = '', $description = '') {
		$msg ['Video'] ['MediaId'] = $media_id;
		$msg ['Video'] ['Title'] = $title;
		$msg ['Video'] ['Description'] = $description;
		$this->_replyData ( $msg, 'video' );
	}
	/* 回复音乐消息 */
	public function replyMusic($media_id, $title = '', $description = '', $music_url, $HQ_music_url) {
		$msg ['Music'] ['ThumbMediaId'] = $media_id;
		$msg ['Music'] ['Title'] = $title;
		$msg ['Music'] ['Description'] = $description;
		$msg ['Music'] ['MusicURL'] = $music_url;
		$msg ['Music'] ['HQMusicUrl'] = $HQ_music_url;
		$this->_replyData ( $msg, 'music' );
	}
	/*
	 * 回复图文消息 articles array 格式如下： array( array('Title'=>'','Description'=>'','PicUrl'=>'','Url'=>''), array('Title'=>'','Description'=>'','PicUrl'=>'','Url'=>'') );
	 */
	public function replyNews($articles) {
		$msg ['ArticleCount'] = count ( $articles );
		$msg ['Articles'] = $articles;
		
		$this->_replyData ( $msg, 'news' );
	}
	/* 发送回复消息到微信平台 */
	private function _replyData($msg, $msgType) {
		$msg ['ToUserName'] = $this->data ['FromUserName'];
		$msg ['FromUserName'] = $this->data ['ToUserName'];
		$msg ['CreateTime'] = NOW_TIME;
		$msg ['MsgType'] = $msgType;
		
		$xml = new \SimpleXMLElement ( '<xml></xml>' );
		$this->_data2xml ( $xml, $msg );
		$str = $xml->asXML ();
		
		// 记录日志
		addWeixinLog ( $str, '_replyData' );
		
		echo ( $str );
	}
	/* 组装xml数据 */
	public function _data2xml($xml, $data, $item = 'item') {
		foreach ( $data as $key => $value ) {
			is_numeric ( $key ) && ($key = $item);
			if (is_array ( $value ) || is_object ( $value )) {
				$child = $xml->addChild ( $key );
				$this->_data2xml ( $child, $value, $item );
			} else {
				if (is_numeric ( $value )) {
					$child = $xml->addChild ( $key, $value );
				} else {
					$child = $xml->addChild ( $key );
					$node = dom_import_simplexml ( $child );
					$node->appendChild ( $node->ownerDocument->createCDATASection ( $value ) );
				}
			}
		}
	}
	/* ========================发送被动响应消息 end================================== */
	/* 上传多媒体文件 */
	public function uploadFile($file, $type = 'image', $acctoken = '') {
		$post_data ['type'] = $type; // 媒体文件类型，分别有图片（image）、语音（voice）、视频（video）和缩略图（thumb）
		$post_data ['media'] = $file;
		
		$url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=$acctoken&type=image";
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_data );
		ob_start ();
		curl_exec ( $ch );
		$result = ob_get_contents ();
		ob_end_clean ();
		
		return $result;
	}
	/* 下载多媒体文件 */
	public function downloadFile($media_id, $acctoken = '') {
		// TODO
	}
	/**
	 * GET 请求
	 *
	 * @param string $url        	
	 */
	private function http_get($url) {
		$oCurl = curl_init ();
		if (stripos ( $url, "https://" ) !== FALSE) {
			curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYPEER, FALSE );
			curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYHOST, FALSE );
		}
		curl_setopt ( $oCurl, CURLOPT_URL, $url );
		curl_setopt ( $oCurl, CURLOPT_RETURNTRANSFER, 1 );
		$sContent = curl_exec ( $oCurl );
		$aStatus = curl_getinfo ( $oCurl );
		curl_close ( $oCurl );
		if (intval ( $aStatus ["http_code"] ) == 200) {
			return $sContent;
		} else {
			return false;
		}
	}
	
	/**
	 * POST 请求
	 *
	 * @param string $url        	
	 * @param array $param        	
	 * @return string content
	 */
	private function http_post($url, $param) {
		$oCurl = curl_init ();
		if (stripos ( $url, "https://" ) !== FALSE) {
			curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYPEER, FALSE );
			curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYHOST, false );
		}
		if (is_string ( $param )) {
			$strPOST = $param;
		} else {
			$aPOST = array ();
			foreach ( $param as $key => $val ) {
				$aPOST [] = $key . "=" . urlencode ( $val );
			}
			$strPOST = join ( "&", $aPOST );
		}
		curl_setopt ( $oCurl, CURLOPT_URL, $url );
		curl_setopt ( $oCurl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $oCurl, CURLOPT_POST, true );
		curl_setopt ( $oCurl, CURLOPT_POSTFIELDS, $strPOST );
		$sContent = curl_exec ( $oCurl );
		$aStatus = curl_getinfo ( $oCurl );
		curl_close ( $oCurl );
		if (intval ( $aStatus ["http_code"] ) == 200) {
			return $sContent;
		} else {
			return false;
		}
	}
}