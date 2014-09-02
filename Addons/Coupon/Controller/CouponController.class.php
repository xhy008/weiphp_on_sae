<?php

namespace Addons\Coupon\Controller;

use Home\Controller\AddonsController;

class CouponController extends AddonsController {
	function edit() {
		$id = I ( 'id' );
		$model = $this->getModel ();
		
		if (IS_POST) {
			$Model = D ( parse_name ( get_table_name ( $model ['id'] ), 1 ) );
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $model ['id'] );
			if ($Model->create () && $Model->save ()) {
				$this->_saveKeyword ( $model, $id );
				
				$this->success ( '保存' . $model ['title'] . '成功！', U ( 'lists?model=' . $model ['name'] ) );
			} else {
				$this->error ( $Model->getError () );
			}
		} else {
			$fields = get_model_attribute ( $model ['id'] );
			
			// 获取数据
			$data = M ( get_table_name ( $model ['id'] ) )->find ( $id );
			$data || $this->error ( '数据不存在！' );
			
		$token = get_token ();
		if (isset ( $data ['token'] ) && $token != $data ['token'] && defined ( 'ADDON_PUBLIC_PATH' )) {
			$this->error ( '非法访问！' );
		}			
			
			$this->assign ( 'fields', $fields );
			$this->assign ( 'data', $data );
			$this->meta_title = '编辑' . $model ['title'];
			
			$this->_deal_data ();
			
			$this->display ();
		}
	}
	function add() {
		$model = $this->getModel ();
		if (IS_POST) {
			$Model = D ( parse_name ( get_table_name ( $model ['id'] ), 1 ) );
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $model ['id'] );
			if ($Model->create () && $id = $Model->add ()) {
				$this->_saveKeyword ( $model, $id );
				
				$this->success ( '添加' . $model ['title'] . '成功！', U ( 'lists?model=' . $model ['name'] ) );
			} else {
				$this->error ( $Model->getError () );
			}
		} else {
			$fields = get_model_attribute ( $model ['id'] );
			
			$this->assign ( 'fields', $fields );
			$this->meta_title = '新增' . $model ['title'];
			
			$this->_deal_data ();
			
			$this->display ();
		}
	}
	
	// 增加或者编辑时公共部分
	function _deal_data() {
		$normal_tips = '插件场景限制参数说明：格式：[插件名:id],如<br/>
				[投票:10]，表示对ID为10的投票投完对能领取<br/>
				[投票:*]，表示只要投过票就可以领取<br/>
				[微调研:15]，表示完成ID为15的调研就能领取<br/>
				[微考试:10]，表示完成ID为10的考试就能领取<br/>';
		$this->assign ( 'normal_tips', $normal_tips );
	}
	// 预览
	function preview() {
		$this->prev ();
	}
	// 开始领取页面
	function prev() {
		$this->_detail ();
		$this->display ( 'prev' );
	}
	// 过期提示页面
	function over() {
		$this->_detail ();
		$this->display ();
	}
	function show() {
		$sn_id = I ( 'sn_id', 0, 'intval' );
		$map2 ['target_id'] = I ( 'id', 0, 'intval' );
		$map2 ['uid'] = $this->mid;
		$list = M ( 'sn_code' )->where ( $map2 )->select ();
// 		dump($list);
		foreach ( $list as $vo ) {
			$my_count += 1;
			$vo ['id'] == $sn_id && $sn = $vo;
		}
		if (empty ( $sn_id ) || empty ( $sn )) {
			$this->error ( '非法访问' );
			exit ();
		}
		$this->assign ( 'sn', $sn );
// 		dump($sn);
		
		$this->_detail ( $my_count );
		
		$this->display ( 'show' );
	}
	function _detail($my_count = false) {
		$id = I ( 'id', 0, 'intval' );
		$data = M ( 'coupon' )->find ( $id );
		$this->assign ( 'data', $data );
		// dump ( $data );
		
		// 领取条件提示
		$follower_condtion [1] = '关注后才能领取';
		$follower_condtion [2] = '用户绑定后才能领取';
		$follower_condtion [3] = '领取会员卡后才能领取';
		$tips = condition_tips ( $data ['addon_condition'] );
		
		$condition = array ();
		$data ['max_num'] > 0 && $condition [] = '每人最多可领取' . $data ['max_num'] . '张';
		$data ['credit_conditon'] > 0 && $condition [] = '积分中财富值达到' . $data ['credit_conditon'] . '分才能领取';
		$data ['credit_bug'] > 0 && $condition [] = '领取后需扣除财富值' . $data ['credit_bug'] . '分';
		isset ( $follower_condtion [$data ['follower_condtion']] ) && $condition [] = $follower_condtion [$data ['follower_condtion']];
		empty ( $tips ) || $condition [] = $tips;
		
		$this->assign ( 'condition', $condition );
		// dump ( $condition );
		
		$this->_get_error ( $data, $my_count );
	}
	function _get_error($data, $my_count = false) {
		$error = '';
		
		// 抽奖记录
		if ($my_count === false) {
			$map2 ['target_id'] = $data ['id'];
			$map2 ['uid'] = $this->mid;
			$my_count = M ( 'sn_code' )->where ( $map2 )->count ();
		}
		
		// 权限判断
		$map ['token'] = get_token ();
		$map ['openid'] = get_openid ();
		$follow = M ( 'follow' )->where ( $map )->find ();
		$is_admin = is_login ();
		
		if (!empty($data ['end_time']) && $data ['end_time'] <= time ()) {
			$error = '您来晚啦';
		} else if ($data ['max_num'] > 0 && $data ['max_num'] <= $my_count) {
			$error = '您的领取名额已用完啦';
		} else if ($data ['follower_condtion'] > intval ( $follow ['status'] ) && ! $is_admin) {
			switch ($data ['follower_condtion']) {
				case 1 :
					$error = '关注后才能领取';
					break;
				case 2 :
					$error = '用户绑定后才能领取';
					break;
				case 3 :
					$error = '领取会员卡后才能领取';
					break;
			}
		} else if ($data ['credit_conditon'] > intval ( $follow ['score'] ) && ! $is_admin) {
			$error = '您的财富值不足';
		} else if ($data ['credit_bug'] > intval ( $follow ['score'] ) && ! $is_admin) {
			$error = '您的财富值不够扣除';
		} else if (! empty ( $data ['addon_condition'] )) {
			addon_condition_check ( $data ['addon_condition'] ) || $error = '权限不足';
		}
		$this->assign ( 'error', $error );
		// dump ( $error );
		
		return $error;
	}
	
	// 记录中奖数据到数据库
	function set_sn_code() {
		$id = I ( 'id', 0, 'intval' );
		$data = M ( 'coupon' )->find ( $id );
		
		$error = $this->_get_error ( $data );
		if (! empty ( $error )) {
			$this->display ( 'over' );
			exit ();
		}
		
		$data ['sn'] = uniqid ();
		$data ['uid'] = $this->mid;
		$data ['cTime'] = time ();
		$data ['addon'] = 'Coupon';
		$data ['target_id'] = $id;
		
		$data ['prize_id'] = 0;
		$data ['prize_title'] = '';
		unset($data['id']);
		// dump ( $data );
		
		$res = M ( 'sn_code' )->add ( $data );
		if ($res) {
			// 扣除积分
			if (! empty ( $data ['credit_bug'] )) {
				$credit ['score'] = $data ['credit_bug'];
				$credit ['experience'] = 0;
				add_credit ( 'coupon_credit_bug', 5, $credit );
			}
			
			$param ['id'] = $id;
			$param ['sn_id'] = $res;
			redirect ( U ( 'show', $param ) );
		} else {
			$this->error ( '领取失败，请稍后再试' );
		}
	}
}
