<?php

namespace Addons\Survey\Controller;

use Home\Controller\AddonsController;

class SurveyController extends AddonsController {
	function survey_question() {
		$param ['survey_id'] = I ( 'id', 0, 'intval' );
		$url = addons_url ( 'Survey://Question/lists', $param );
		// dump($url);
		redirect ( $url );
	}
	function survey_answer() {
		$param ['survey_id'] = I ( 'id', 0, 'intval' );
		$url = addons_url ( 'Survey://Answer/lists', $param );
		// dump($url);
		redirect ( $url );
	}
	function preview() {
		$param ['survey_id'] = I ( 'id', 0, 'intval' );
		$url = addons_url ( 'Survey://Survey/show', $param );
		// dump($url);
		redirect ( $url );
	}
	function show() {
		$map ['id'] = I ( 'survey_id', 0, 'intval' );
		$map ['token'] = get_token ();
		$info = M ( 'survey' )->where ( $map )->find ();
		$this->assign ( 'info', $info );
		
		$this->display ();
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
			
			redirect ( U ( 'survey', 'survey_id=' . $_REQUEST ['survey_id'] ) );
			exit ();
		}
		
		$this->display ();
	}
	function survey() {
		$map ['survey_id'] = intval ( $_REQUEST ['survey_id'] );
		$map ['token'] = get_token ();
		$survey = M ( 'survey' )->where ( $map )->find ();
		$list = M ( 'survey_question' )->where ( $map )->order ( 'sort asc, id asc' )->select ();
		
		if (IS_POST) {
			$map ['uid'] = $this->mid;
			$map ['question_id'] = I ( 'post.question_id', 0, 'intval' );
			$answer = M ( 'survey_answer' )->where ( $map )->find ();
			
			$data ['cTime'] = time ();
			$data ['answer'] = serialize ( $_POST ['answer'] );
			if ($answer) {
				M ( 'survey_answer' )->where ( $map )->save ( $data );
			} else {
				$data ['survey_id'] = $map ['survey_id'];
				$data ['token'] = $map ['token'];
				$data ['question_id'] = $map ['question_id'];
				$data ['uid'] = $map ['uid'];
				$data ['openid'] = get_openid ();
				M ( 'survey_answer' )->add ( $data );
			}
		}
		
		$question_id = I ( 'post.next_id', 0, 'intval' );
		if ($question_id == '-1') {
			redirect ( U ( 'finish', 'survey_id=' . $map ['survey_id'] ) );
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
		
		$this->assign ( 'survey', $survey );
		$this->assign ( 'question', $question );
		$this->assign ( 'next_id', $next_id );
		$this->assign ( 'extra', $extra );
		
		$this->display ();
	}
	function finish() {
		$map ['id'] = I ( 'survey_id', 0, 'intval' );
		$map ['token'] = get_token ();
		$info = M ( 'survey' )->where ( $map )->find ();
		$this->assign ( 'info', $info );
		
		// 增加积分
		add_credit ( 'survey' );
		
		$this->display ();
	}
}
