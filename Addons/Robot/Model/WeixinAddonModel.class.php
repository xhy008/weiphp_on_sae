<?php

namespace Addons\Robot\Model;

use Home\Model\WeixinModel;

/**
 * Robot的微信模型
 */
class WeixinAddonModel extends WeixinModel {
	public function reply($dataArr, $keywordArr = array()) {
		// 进入学习模式
		if ($dataArr ['Content'] == '机器人学习时间') {
			// 设置用户状态
			$keywordArr ['step'] = 'set_question';
			set_user_status ( 'Robot', $keywordArr );
			
			$this->replyText ( '你的问题是？' );
			return true;
		}
		
		// 机器人学习中
		if (isset ( $keywordArr ['step'] )) {
			switch ($keywordArr ['step']) {
				case 'set_question' :
					// 判断问题是否已经存在
					$map ['keyword'] = $dataArr ['Content'];
					$map ['addon'] = 'Robot';
					$info = D ( 'Common/Keyword' )->where ( $map )->find ();
					
					if ($info) {
						$keywordArr ['step'] = 'answer_exist';
						$keywordArr ['question'] = $dataArr ['Content'];
						set_user_status ( 'Robot', $keywordArr );
						
						$this->replyText ( '你的问题已经有了答案：“' . $info ['extra_text'] . '”，是否需要更新答案？回复”是“更新答案，回答”否“退出学习模式' );
					} else {
						$keywordArr ['step'] = 'set_answer';
						$keywordArr ['question'] = $dataArr ['Content'];
						set_user_status ( 'Robot', $keywordArr );
						
						$this->replyText ( '你的答案是？' );
					}
					break;
				case 'answer_exist' :
					if ($dataArr ['Content'] == '是') {
						$keywordArr ['step'] = 'set_answer';
						set_user_status ( 'Robot', $keywordArr );
						
						$this->replyText ( '你的新答案是？' );
					} else {
						$this->replyText ( '已经退出学习模式' );
					}
				
				case 'set_answer' :
					// 把问题和答案保存关键词表中
					D ( 'Common/Keyword' )->set ( $keywordArr ['question'], 'Robot', 0, 0, $dataArr ['Content'] );
					
					$this->replyText ( '我明白啊，不信你可以问问我' );
					break;
			}
		}
		
		// 机器人回复
		$this->replyText ( $keywordArr ['extra_text'] );
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
        	