<?php

namespace Addons\Tongji\Controller;

use Home\Controller\AddonsController;

class TongjiController extends AddonsController {
	function _initialize() {
		$act = strtolower ( _ACTION );
		$nav = array ();
		$res ['title'] = '7日数据';
		$res ['url'] = U ( 'lists' );
		$res ['class'] = $act == 'lists' ? 'current' : '';
		$nav [] = $res;
		
		$res ['title'] = '月度数据';
		$res ['url'] = U ( 'month' );
		$res ['class'] = $act == 'month' ? 'current' : '';
		$nav [] = $res;
		
		$this->assign ( 'nav', $nav );
	}
	function lists() {
		$this->assign ( 'add_button', false );
		$this->assign ( 'del_button', false );
		$this->assign ( 'search_button', false );
		$this->assign ( 'check_all', false );
		
		// 插件列表
		$addonList = D ( 'Addons' )->getWeixinList ( false, array (), true );
		
		$grid ['field'] [0] = 'addon';
		$grid ['title'] = '插件';
		$list_data ["list_grids"] [] = $grid;
		
		for($i = 0; $i < 7; $i ++) {
			$days [] = $grid ['title'] = $grid ['field'] [0] = date ( "Ymd", strtotime ( "-$i day" ) );
			$list_data ["list_grids"] [] = $grid;
		}
		
		$map ['token'] = get_token ();
		$map ['day'] = array (
				'in',
				$days 
		);
		$data = M ( 'tongji' )->where ( $map )->select ();
		foreach ( $data as $vo ) {
			$content = unserialize ( $vo ['content'] );
			foreach ( $content as $addon => $count ) {
				$countArr [$addon] [$vo ['day']] = $count;
			}
		}
		foreach ( $addonList as $k => $a ) {
			if ($k == 'Tongji')
				continue;
			
			$res ['addon'] = $a ['title'];
			foreach ( $days as $d ) {
				$total [$d] += $res [$d] = intval ( $countArr [$k] [$d] );
			}
			$list [] = $res;
			unset ( $res );
		}
		
		$res ['addon'] = '共计';
		foreach ( $days as $d ) {
			$res [$d] = intval ( $total [$d] );
		}
		$list [] = $res;
		
		$list_data ['list_data'] = $list;
		
		$this->assign ( $list_data );
		// dump($list_data);
		
		$this->display ();
	}
	function month() {
		$this->assign ( 'add_button', false );
		$this->assign ( 'del_button', false );
		$this->assign ( 'search_button', false );
		$this->assign ( 'check_all', false );
		
		// 插件列表
		$addonList = D ( 'Addons' )->getWeixinList ( false, array (), true );
		
		$grid ['field'] [0] = 'addon';
		$grid ['title'] = '插件';
		$list_data ["list_grids"] [] = $grid;
		
		for($i = 0; $i < 6; $i ++) {
			$days [] = $grid ['title'] = $grid ['field'] [0] = date ( "Ym", strtotime ( "-$i month" ) );
			$list_data ["list_grids"] [] = $grid;
		}
		
		$map ['token'] = get_token ();
		$map ['month'] = array (
				'in',
				$days 
		);
		$data = M ( 'tongji' )->where ( $map )->select ();
		foreach ( $data as $vo ) {
			$content = unserialize ( $vo ['content'] );
			foreach ( $content as $addon => $count ) {
				$countArr [$addon] [$vo ['month']] += $count;
			}
		}
		
		foreach ( $addonList as $k => $a ) {
			if ($k == 'Tongji')
				continue;
			
			$res ['addon'] = $a ['title'];
			foreach ( $days as $d ) {
				$total [$d] += $res [$d] = intval ( $countArr [$k] [$d] );
			}
			$list [] = $res;
			unset ( $res );
		}
		
		$res ['addon'] = '共计';
		foreach ( $days as $d ) {
			$res [$d] = intval ( $total [$d] );
		}
		$list [] = $res;
		
		$list_data ['list_data'] = $list;
		
		$this->assign ( $list_data );
		// dump($list_data);
		
		$this->display ( 'lists' );
	}
}
