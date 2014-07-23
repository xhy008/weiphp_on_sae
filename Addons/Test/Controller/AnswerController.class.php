<?php

namespace Addons\Test\Controller;

use Home\Controller\AddonsController;

class AnswerController extends AddonsController {
	var $model;
	var $test_id;
	function _initialize() {
		parent::_initialize();
		
		$this->model = $this->getModel ( 'test_answer' );
		
		$param ['test_id'] = $this->test_id = intval ( $_REQUEST ['test_id'] );
		
		$res ['title'] = '微测试';
		$res ['url'] = addons_url ( 'Test://Test/lists' );
		$res ['class'] = '';
		$nav [] = $res;
		
		$res ['title'] = '数据管理';
		$res ['url'] = addons_url ( 'Test://Answer/lists', $param );
		$res ['class'] = 'current';
		$nav [] = $res;
		
		$this->assign ( 'nav', $nav );
	}
	// 通用插件的列表模型
	public function lists() {
		$this->assign ( 'add_button', false );
		$this->assign ( 'search_button', false );
		$this->assign ( 'del_button', false );
		$this->assign ( 'check_all', false );
		
		// 解析列表规则
		$data = $this->_list_grid ( $this->model );
		//dump ( $data );
		
		$this->assign ( $data );
		
		// 搜索条件
		$map = $this->_search_map ( $this->model, $data ['fields'] );
		$data ['fields'] [] = 'sum(score) as total';
		
		$name = parse_name ( get_table_name ( $this->model ['id'] ), true );
		$list = M ( $name )->where ( $map )->field ( $data ['fields'] )->order ( 'id DESC' )->group ( 'uid' )->selectPage ();
		foreach ( $list ['list_data'] as &$vo ) {
			$member = get_memberinfo ( $vo ['uid'] );
			$vo ['truename'] = $member ['truename'];
			$vo ['mobile'] = $member ['mobile'];
			$vo ['score'] = $vo ['total'];
		}
		
		$this->assign ( $list );
		
		$this->display ();
	}
	function detail() {
		$this->assign ( 'add_button', false );
		$this->assign ( 'search_button', false );
		$this->assign ( 'del_button', false );
		$this->assign ( 'check_all', false );
		
		// 解析列表规则
		$fields [] = 'question';
		$fields [] = 'answer';
		
		$girds ['field'] [0] = 'question';
		$girds ['title'] = '题目';
		$list_data ['list_grids'] [] = $girds;
		
		$girds ['field'] [0] = 'answer';
		$girds ['title'] = '回答内容';
		$list_data ['list_grids'] [] = $girds;
		
		$list_data ['fields'] = $fields;
		$this->assign ( $list_data );
		
		$map ['test_id'] = intval ( $_REQUEST ['test_id'] );
		$questions = M ( 'test_question' )->where ( $map )->select ();
		foreach ( $questions as $q ) {
			$title [$q ['id']] = $q ['title'];
			$type [$q ['id']] = $q ['type'];
			$extra [$q ['id']] = parse_config_attr ( $q ['extra'] );
		}
		
		$map ['uid'] = intval ( $_REQUEST ['uid'] );
		$answers = M ( 'test_answer' )->where ( $map )->select ();
		foreach ( $answers as $a ) {
			$qid = $a ['question_id'];
			$data ['question'] = $title [$qid];
			$value = unserialize ( $a ['answer'] );
			switch ($type [$qid]) {
				case 'radio' :
					$data ['answer'] = $extra [$qid] [$value];
					break;
				case 'checkbox' :
					foreach ( $value as $v ) {
						$data ['answer'] [] = $extra [$qid] [$v];
					}
					$data ['answer'] = implode ( ',', $data ['answer'] );
					break;
				default :
					$data ['answer'] = $value;
			}
			$list [] = $data;
			unset ( $data );
		}
		$this->assign ( 'list_data', $list );
		
		$this->display ( T ( 'lists' ) );
	}
	
	// 通用插件的删除模型
	public function del() {
		parent::common_del ( $this->model );
	}
}
