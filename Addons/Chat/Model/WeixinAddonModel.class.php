<?php

namespace Addons\Chat\Model;

use Home\Model\WeixinModel;

class WeixinAddonModel extends WeixinModel {
	var $config = array ();
	function reply($dataArr, $keywordArr = array()) {
		$this->config = getAddonConfig ( 'Chat' ); // 获取后台插件的配置参数	
		//dump($this->config);
			
		// 先尝试小九机器人
		$content = $this->_xiaojo ( $dataArr ['Content'] );
		
		// 再尝试小黄鸡
		if (empty ( $content )) {
			$content = $this->_simsim ( $dataArr ['Content'] );
		}
		
		// TODO 此处可继续增加其它API接口
		
		// 最后只能随机回复了
		if (empty ( $content )) {
			$content = $this->_rand ();
		}
		
		// 增加积分,每隔5分钟才加一次，5分钟内只记一次积分
		add_credit ( 'chat', 300 );
		
		$res = $this->replyText ( $content );
		return $res;
	}
	
	// 随机回复
	private function _rand() {
		$this->config ['rand_reply'] = array_map ( 'trim', explode ( "\n", $this->config ['rand_reply'] ) );
		$key = array_rand ( $this->config ['rand_reply'] );
		
		return $this->config ['rand_reply'] [$key];
	}
	
	// 小黄鸡
	private function _simsim($keyword) {
		$api_url = $this->config['simsim_url']."?key=" . $this->config['simsim_key'] . "&lc=ch&ft=0.0&text=" . $keyword;
		
		$result = file_get_contents ( $api_url );
		$result = json_decode ( $result, true );
		
		return $result ['response'];
	}
	
	// 小九机器人
	private function _xiaojo($keyword) {
		$curlPost ['chat'] = $keyword;
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $this->config['i9_url'] );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $curlPost );
		$data = curl_exec ( $ch );
		curl_close ( $ch );
		
		return $data;
	}
}
