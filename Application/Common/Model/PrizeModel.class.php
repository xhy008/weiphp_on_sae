<?php

namespace Common\Model;

use Think\Model;

/**
 * 插件配置操作集成
 */
class PrizeModel extends Model {
	/**
	 * 保存配置
	 */
	function set($target_id, $addon = 'Scratch') {
		$opt_data ['addon'] = $addon;
		$opt_data ['target_id'] = $target_id;
		
		foreach ( $_POST ['prize_title'] as $key => $opt ) {
			if (empty ( $opt ))
				continue;
			
			$opt_data ['prize_title'] = $opt;
			$opt_data ['prize_name'] = $_POST ['prize_name'] [$key];
			$opt_data ['prize_num'] = $_POST ['prize_num'] [$key];
			if ($key > 0) {
				$optIds [] = $key;
				$map ['id'] = $key;
				M ( 'prize' )->where ( $map )->save ( $opt_data );
			} else {
				$optIds [] = M ( 'prize' )->add ( $opt_data );
			}
		}
		
		// 删除旧数据
		$map2 ['id'] = array (
				'not in',
				$optIds 
		);
		$map2 ['target_id'] = $opt_data ['target_id'];
		$flag = M ( 'prize' )->where ( $map2 )->delete ();
		
		return $flag;
	}
}
?>
