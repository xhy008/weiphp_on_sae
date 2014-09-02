<?php

namespace Addons\Exam\Controller;

use Home\Controller\AddonsController;

class ExamController extends AddonsController {
	function exam_question() {
		$param ['exam_id'] = I ( 'id', 0, 'intval' );
		$url = addons_url ( 'Exam://Question/lists', $param );
		// dump($url);
		redirect ( $url );
	}
	function exam_answer() {
		$param ['exam_id'] = I ( 'id', 0, 'intval' );
		$url = addons_url ( 'Exam://Answer/lists', $param );
		// dump($url);
		redirect ( $url );
	}
	function preview() {
		$param ['exam_id'] = I ( 'id', 0, 'intval' );
		$url = addons_url ( 'Exam://Exam/show', $param );
		// dump($url);
		redirect ( $url );
	}
	function show($html = 'show') {
		$map ['id'] = $exam_id = I ( 'exam_id', 0, 'intval' );
		$map ['token'] = get_token ();
		$info = M ( 'exam' )->where ( $map )->find ();
		$this->assign ( 'info', $info );
		
		unset ( $map );
		$map ['uid'] = $this->mid;
		$map ['exam_id'] = $exam_id;
		$score = M ( 'exam_answer' )->where ( $map )->getField ( 'sum(score) as total' );
		$this->assign ( 'score', $score );
		
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
			
			redirect ( U ( 'exam', 'exam_id=' . $_REQUEST ['exam_id'] ) );
			exit ();
		}
		
		$this->display ();
	}
	function exam() {
		$map ['exam_id'] = intval ( $_REQUEST ['exam_id'] );
		$map ['token'] = get_token ();
		$exam = M ( 'exam' )->where ( $map )->find ();
		
		$now = time ();
		$start = intval ( $exam ['start_time'] );
		$end = intval ( $exam ['end_time'] );
		if (($start > 0 && $now < $start) || ($end > 0 && $now > $end)) {
			redirect ( U ( 'show', 'exam_id=' . $map ['exam_id'] ) );
		}
		
		$list = M ( 'exam_question' )->where ( $map )->order ( 'sort asc, id asc' )->select ();
		foreach ( $list as $vo ) {
			$question_list [$vo ['id']] = $vo;
		}
		
		if (IS_POST) {
			$map ['uid'] = $this->mid;
			$map ['question_id'] = I ( 'post.question_id', 0, 'intval' );
			$answer = M ( 'exam_answer' )->where ( $map )->find ();
			
			$data ['cTime'] = time ();
			$data ['answer'] = serialize ( $_POST ['answer'] );
			$data ['score'] = $this->_getScore ( $question_list [$map ['question_id']], $_POST ['answer'] );
			if ($answer) {
				M ( 'exam_answer' )->where ( $map )->save ( $data );
			} else {
				$data ['exam_id'] = $map ['exam_id'];
				$data ['token'] = $map ['token'];
				$data ['question_id'] = $map ['question_id'];
				$data ['uid'] = $map ['uid'];
				$data ['openid'] = get_openid ();
				M ( 'exam_answer' )->add ( $data );
			}
		}
		
		$question_id = I ( 'post.next_id', 0, 'intval' );
		if ($question_id == '-1') {
			redirect ( U ( 'finish', 'exam_id=' . $map ['exam_id'] ) );
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
		
		$this->assign ( 'exam', $exam );
		$this->assign ( 'question', $question );
		$this->assign ( 'next_id', $next_id );
		$this->assign ( 'extra', $extra );
		
		$this->display ();
	}
	function finish() {
		// 增加积分
		add_credit ( 'exam' );
		
		$this->show ( 'finish' );
	}
	
	// 判断答题得分
	function _getScore($question, $answer) {
		if (! is_array ( $answer )) {
			$answer = array (
					$answer 
			);
		}
		
		$answer = array_filter ( $answer );
		$answer = array_map ( 'trim', $answer );
		if (empty ( $answer )) {
			return 0;
		}
		
		$correct = preg_split ( '/[\s,;]+/', $question ['answer'] );
		$correct = array_filter ( $correct );
		$correct = array_map ( 'trim', $correct );
		
		$diff = array_diff ( $correct, $answer );
		$diff2 = array_diff ( $answer, $correct );
		return empty ( $diff ) && empty ( $diff2 ) ? $question ['score'] : 0;
	}
}
