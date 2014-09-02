<?php

namespace Addons\Scratch\Controller;

use Home\Controller\AddonsController;

class ScratchController extends AddonsController {
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
	function preview() {
		$this->show ();
	}
	function show() {
		$id = $map ['target_id'] = I ( 'id' );
		
		$data = M ( 'scratch' )->find ( $id );
		$this->assign ( 'data', $data );
		// dump($data);
		
		// 奖项
		$map ['addon'] = 'Scratch';
		$prizes = M ( 'prize' )->where ( $map )->select ();
		$this->assign ( 'prizes', $prizes );
		
		// 抽奖记录
		$all_prizes = M ( 'sn_code' )->where ( $map )->order ( 'id desc' )->select ();
		// dump ( $all_prizes );
		foreach ( $all_prizes as $all ) {
			if ($all ['prize_id'] > 0) {
				$has [$all ['prize_id']] += 1; // 每个奖项已经中过的次数
				$new_prizes [] = $all; // 最新中奖记录
				$all ['uid'] == $this->mid && $my_prizes [] = $all; // 我的中奖记录
			} else {
				$no_count += 1; // 没有中奖的次数
			}
			
			// 记录我已抽奖的次数
			$all ['uid'] == $this->mid && $my_count += 1;
		}
		
		$this->assign ( 'new_prizes', $new_prizes );
		$this->assign ( 'my_prizes', $my_prizes );
		// dump ( $new_prizes );
		// dump ( $my_prizes );
		
		// 权限判断
		unset ( $map );
		$map ['token'] = get_token ();
		$map ['openid'] = get_openid ();
		$follow = M ( 'follow' )->where ( $map )->find ();
		$is_admin = is_login ();
		$error = '';
		if ($data ['end_time'] <= time ()) {
			$error = '活动已结束';
		} else if ($data ['max_num'] > 0 && $data ['max_num'] <= $my_count) {
			$error = '您的刮卡机会已用完啦';
		} else if ($data ['follower_condtion'] > intval ( $follow ['status'] ) && ! $is_admin) {
			switch ($data ['follower_condtion']) {
				case 1 :
					$error = '关注后才能参与';
					break;
				case 2 :
					$error = '用户绑定后才能参与';
					break;
				case 3 :
					$error = '领取会员卡后才能参与';
					break;
			}
		} else if ($data ['credit_conditon'] > intval ( $follow ['score'] ) && ! $is_admin) {
			$error = '您的财富值不足';
		} else if ($data ['credit_bug'] > intval ( $follow ['score'] ) && ! $is_admin) {
			$error = '您的财富值不够扣除';
		} else if (! empty ( $data ['addon_condition'] )) {
			addon_condition_check ( $data ['addon_condition'] ) || $error = '您没权限参与';
		}
		$this->assign ( 'error', $error );
		// 抽奖算法
		empty ( $error ) && $this->_lottery ( $data, $prizes, $new_prizes, $my_count, $has, $no_count );
		
		$this->display ( 'show' );
	}
	
	// 抽奖算法 中奖概率 = 奖品总数/(预估活动人数*每人抽奖次数)
	function _lottery($data, $prizes, $new_prizes, $my_count = 0, $has = array(), $no_count = 0) {
		$max_num = empty ( $data ['max_num'] ) ? 1 : $data ['max_num'];
		$count = $data ['predict_num'] * $max_num; // 总基数
		                                                    // 获取已经中过的奖
		foreach ( $prizes as $p ) {
			$prizesArr [$p ['id']] = $p;
			
			$prize_num = $p ['num'] - $has [$p ['id']];
			for($i = 0; $i < $prize_num; $i ++) {
				$rand [] = $p ['id']; // 中奖的记录，同时通过ID可以知道中的是哪个奖
			}
		}
		// dump ( $rand );
		// dump ( $prizesArr );
		
		if ($data ['predict_num'] != 1) {
			$remain = $count - count ( $rand ) - $no_count;
			$remain > 5000 && $remain = 5000; // 防止数组过大导致内存溢出
			for($i = 0; $i < $remain; $i ++) {
				$rand [] = 0; // 不中奖的记录
			}
		}
		if (empty ( $rand )) {
			$rand [] = - 1;
		}
		
		shuffle ( $rand ); // 所有记录随机排序
		$prize_id = $rand [0]; // 第一个记录作为当前用户的中奖记录
		$prize = array ();
		
		if ($prize_id > 0) {
			$prize = $prizesArr [$prize_id];
		} elseif ($prize_id == - 1) {
			$prize ['id'] = 0;
			$prize ['title'] = '奖品已抽完';
		} else {
			$prize ['id'] = 0;
			$prize ['title'] = '谢谢参与';
		}

		// 获取我的抽奖机会
		if (empty ( $data ['max_num'] )) {
			$prize ['count'] = 1;
		} else {
			$prize ['count'] = $max_num - $my_count - 1;
			$prize ['count'] < 0 && $prize ['count'] = 0;
		}
		
// 		dump ( $prize );
		$this->assign ( 'prize', $prize );
	}
	
	// 记录中奖数据到数据库
	function set_sn_code() {
		$data ['sn'] = uniqid ();
		$data ['uid'] = $this->mid;
		$data ['cTime'] = time ();
		$data ['addon'] = 'Scratch';
		$data ['target_id'] = I ( 'id' );
		
		$data ['prize_id'] = $map ['id'] = I ( 'prize_id' );
		
		$title = '';
		if (! empty ( $map ['id'] )) {
			$title = M ( 'prize' )->where ( $map )->getField ( 'title' );
			$title || $title = '';
		}
		$data ['prize_title'] = $title;
		// dump ( $data );
		
		$res = M ( 'sn_code' )->add ( $data );
		if ($res) {
			// 扣除积分
			$data = M ( 'scratch' )->find ( $data ['target_id'] );
			if (! empty ( $data ['credit_bug'] )) {
				$credit ['score'] = $data ['credit_bug'];
				$credit ['experience'] = 0;
				add_credit ( 'scratch_credit_bug', 5, $credit );
			}
		}
		echo $res;
	}
}
