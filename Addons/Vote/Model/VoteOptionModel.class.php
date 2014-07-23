<?php

namespace Addons\Vote\Model;

use Think\Model;

/**
 * Vote模型
 */
class VoteOptionModel extends Model {
	function set($vote_id, $post) {
		$opt_data ['vote_id'] = $vote_id;
		foreach ( $post ['name'] as $key => $opt ) {
			if (empty ( $opt ))
				continue;
			
			$opt_data ['name'] = $opt;
			$opt_data ['image'] = $post ['image'] [$key];
			$opt_data ['order'] = intval ( $post ['order'] [$key] );
			if ($key > 0) {
				// 更新选项
				$optIds [] = $map ['id'] = $key;
				$this->where ( $map )->save ( $opt_data );
			} else {
				// 增加新选项
				$optIds [] = $this->add ( $opt_data );
			}
			//dump(M()->getLastSql());
		}
		// 删除旧选项
		$map2 ['id'] = array (
				'not in',
				$optIds 
		);
		$map2 ['vote_id'] = $opt_data ['vote_id'];
		$this->where ( $map2 )->delete ();
	}
}
