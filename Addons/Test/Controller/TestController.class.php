<?php

namespace Addons\Test\Controller;

use Home\Controller\AddonsController;

class TestController extends AddonsController {
	function lists() {
		$normal_tips = '微测试适用两种场景：<br>
一是题目的每个选项对应着不同的得分，最后把用户的所有得分加起来看在哪个分数段，然后给出这个分类段的评价语<br>
二是设置选择不同的选项跳转到不同的问题，前面的问题不设置分数，只有最后选择的问题设置总分，这个分数在哪个分数段，就给出这个分类段的评价语<br>';
		$this->assign ( 'normal_tips', $normal_tips );
		
		parent::lists ();
	}
	function add() {
		$this->_tip ();
		
		parent::add ();
	}
	function edit() {
		$this->_tip ();
		
		parent::edit ();
	}
	function _tip() {
		$normal_tips = '评论语输入格式是：[最小分数-最大分数]对应的评论语，例如下面的评价语：<br/>
[0-59]你是一个不合格的孩子，不想成吊丝的话就好好努力吧<br/>
[60-79]你是一个达到合格标准的孩子，但有很大发展空间<br/>
[80-89]你的修养良好，值得保持下去<br/>
[90-100]高富帅/美富白，咱们交个朋友吧';
		$this->assign ( 'normal_tips', $normal_tips );
	}
	function test_question() {
		$param ['test_id'] = I ( 'id', 0, 'intval' );
		$url = addons_url ( 'Test://Question/lists', $param );
		// dump($url);
		redirect ( $url );
	}
	function test_answer() {
		$param ['test_id'] = I ( 'id', 0, 'intval' );
		$url = addons_url ( 'Test://Answer/lists', $param );
		// dump($url);
		redirect ( $url );
	}
	function preview() {
		$param ['test_id'] = I ( 'id', 0, 'intval' );
		$url = addons_url ( 'Test://Test/show', $param );
		// dump($url);
		redirect ( $url );
	}
	function show($html = 'show') {
		$map ['id'] = $test_id = I ( 'test_id', 0, 'intval' );
		$map ['token'] = get_token ();
		$info = M ( 'test' )->where ( $map )->find ();
		$this->assign ( 'info', $info );
		
		unset ( $map );
		$map ['uid'] = $this->mid;
		$map ['test_id'] = $test_id;
		$score = M ( 'test_answer' )->where ( $map )->getField ( 'sum(score) as total' );
		$this->assign ( 'score', $score );
		
		// 评语
		preg_match_all ( '/\[([0-9]*-[0-9]*)\]/', $info ['finish_tip'], $matches );
		
		$tip = '无相关评语';
		if (! empty ( $matches [0] )) {
			$info ['finish_tip'] = str_replace ( $matches [0], '[评论分段]', $info ['finish_tip'] );
			$tipArr = explode ( '[评论分段]', $info ['finish_tip'] );
			$tipArr = array_map ( 'trim', $tipArr );
			
			$interval = $matches [1];
			$flat = 0;
			foreach ( $interval as $k => $vo ) {
				$arr = explode ( '-', $vo );
				$arr = array_map ( 'intval', $arr );
				if ($score >= $arr [0] && $score <= $arr [1])
					$flat = $k + 1;
			}
			
			isset ( $tipArr [$flat] ) && $tip = $tipArr [$flat];
		}
		$this->assign ( 'tip', $tip );
		
		$this->display ( $html );
	}
	function profile() {
		$map ['id'] = $this->mid;
		$info = M ( 'follow' )->where ( $map )->find ();
		$this->assign ( 'info', $info );
		
		if (IS_POST) {
			if (! empty ( $_POST ['nickname'] ) && $_POST ['nickname'] != $info ['nickname']) {
				$data ['nickname'] = I ( 'post.nickname' );
			}
			if (! empty ( $_POST ['mobile'] ) && $_POST ['mobile'] != $info ['mobile']) {
				$data ['mobile'] = I ( 'post.mobile' );
			}
			
			if (! empty ( $data )) {
				$res = M ( 'follow' )->where ( $map )->save ( $data );
			}
			
			redirect ( U ( 'test', 'test_id=' . $_REQUEST ['test_id'] ) );
			exit ();
		}
		
		$this->display ();
	}
	function test() {
		$map ['test_id'] = intval ( $_REQUEST ['test_id'] );
		$map ['token'] = get_token ();
		$test = M ( 'test' )->where ( $map )->find ();
		
		$now = time ();
		$start = intval ( $test ['start_time'] );
		$end = intval ( $test ['end_time'] );
		if (($start > 0 && $now < $start) || ($end > 0 && $now > $end)) {
			redirect ( U ( 'show', 'test_id=' . $map ['test_id'] ) );
		}
		
		$list = M ( 'test_question' )->where ( $map )->order ( 'sort asc, id asc' )->select ();
		foreach ( $list as $vo ) {
			$question_list [$vo ['id']] = $vo;
		}
		
		if (IS_POST) {
			$map ['uid'] = $this->mid;
			$map ['question_id'] = I ( 'post.question_id', 0, 'intval' );
			$answer = M ( 'test_answer' )->where ( $map )->find ();
			
			$data ['cTime'] = time ();
			$data ['answer'] = serialize ( $_POST ['answer'] );
			$data ['score'] = intval ( $_POST ['score'] );
			if ($answer) {
				M ( 'test_answer' )->where ( $map )->save ( $data );
			} else {
				$data ['test_id'] = $map ['test_id'];
				$data ['token'] = $map ['token'];
				$data ['question_id'] = $map ['question_id'];
				$data ['uid'] = $map ['uid'];
				$data ['openid'] = get_openid ();
				M ( 'test_answer' )->add ( $data );
			}
		}
		
		$question_id = I ( 'post.next_id', 0, 'intval' );
		if ($question_id == '-1') {
			redirect ( U ( 'finish', 'test_id=' . $map ['test_id'] ) );
		}
		
		if (empty ( $question_id )) {
			$question = $list [0];
			$next_id = isset ( $list [1] ['id'] ) ? $list [1] ['id'] : '-1';
		} else {
			foreach ( $list as $k => $vo ) {
				if ($vo ['id'] == $question_id) {
					$question = $vo;
					$next_id = isset ( $list [$k + 1] ['id'] ) ? $list [$k + 1] ['id'] : '-1';
				}
			}
		}
		
		$extra = parse_config_attr ( $question ['extra'] );
		foreach ( $extra as $k => $vo ) {
			$score = 0;
			$n_id = 0;
			preg_match ( '/\[\+([^\]]*)\]+/', $vo, $matches );
			if (isset ( $matches [1] )) {
				$score = intval ( $matches [1] );
				$vo = str_replace ( $matches [0], '', $vo );
			}
			
			unset ( $matches );
			preg_match ( '/\[@([^\]]*)\]+/', $vo, $matches );
			
			if (isset ( $matches [1] )) {
				$n_id = intval ( $matches [1] );
				$vo = str_replace ( $matches [0], '', $vo );
			}
			
			$extraArr [$k] ['title'] = $vo;
			$extraArr [$k] ['score'] = $score;
			$extraArr [$k] ['next_id'] = $n_id != 0 && isset ( $question_list [$n_id] ) ? $n_id : $next_id;
		}
		// dump ( $extraArr );
		
		$this->assign ( 'test', $test );
		$this->assign ( 'question', $question );
		$this->assign ( 'next_id', $next_id );
		$this->assign ( 'extra', $extraArr );
		
		$this->display ();
	}
	function finish() {
		$test_id = intval ( $_REQUEST ['test_id'] );
		$this->assign ( 'event_url', event_url ( '微测试', $test_id ) );
		// dump ( $event_url );
		
		// 增加积分
		add_credit ( 'test' );
		
		$this->show ( 'finish' );
	}
}
