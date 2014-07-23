<?php

namespace Addons\Suggestions\Controller;

use Home\Controller\AddonsController;

class SuggestionsController extends AddonsController {
	function lists() {
		// 获取模型信息
		$model = $this->getModel ();
		
		$map ['token'] = get_token ();
		session ( 'common_condition', $map );
		
		// 获取模型列表数据
		$list_data = $this->_get_model_list ( $model );
		
		// 获取相关的用户信息
		$uids = getSubByKey ( $list_data ['list_data'], 'uid' );
		$uids = array_filter ( $uids );
		$uids = array_unique ( $uids );
		if (! empty ( $uids )) {
			$map ['id'] = array (
					'in',
					$uids 
			);
			$members = M ( 'follow' )->where ( $map )->field ( 'id,nickname,mobile' )->select ();
			foreach ( $members as $m ) {
				$user [$m ['id']] = $m;
			}
			
			foreach ( $list_data ['list_data'] as &$vo ) {
				empty ( $vo ['mobile'] ) || $vo ['mobile'] = $user [$vo ['uid']] ['mobile'];
				empty ( $vo ['nickname'] ) || $vo ['nickname'] = $user [$vo ['uid']] ['nickname'];
			}
		}
		
		$this->assign ( $list_data );
		$this->assign ( 'add_url', U ( 'suggest' ) );
		
		$this->display ( $model ['template_list'] );
	}
	function suggest() {
		$config = getAddonConfig ( 'Suggestions' );
		$this->assign ( $config );
		// dump ( $config );
		
		$map ['id'] = $data ['uid'] = $this->mid;
		$user = get_followinfo ( $this->mid );
		$this->assign ( 'user', $user );
		
		if (IS_POST) {
			// 保存用户信息
			$nickname = I ( 'nickname' );
			if ($config ['need_nickname'] && ! empty ( $nickname )) {
				$data ['nickname'] = $nickname;
			}
			$mobile = I ( 'mobile' );
			if ($config ['need_mobile'] && ! empty ( $mobile )) {
				$data ['mobile'] = $mobile;
			}
			
			// 保存内容
			$data ['cTime'] = time ();
			$data ['content'] = I ( 'content' );
			$data ['token'] = get_token ();
			
			$res = M ( 'suggestions' )->add ( $data );
			if ($res) {
				// 增加积分
				add_credit ( 'suggestions' );
				
				$this->success ( '增加成功，谢谢您的反馈' );
			} else
				$this->error ( '增加失败，请稍后再试' );
		} else {
			$this->display ();
		}
	}
}
