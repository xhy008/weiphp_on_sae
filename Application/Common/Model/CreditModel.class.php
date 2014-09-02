<?php

namespace Common\Model;

use Think\Model;

/**
 * 积分操作
 */
class CreditModel extends Model {
	protected $tableName = 'credit_data';
	// 增加积分
	function addCredit($data) {
		if (empty ( $data ) || empty ( $data ['credit_name'] ))
			return false;
		
		$credit = $this->getCreditByName ( $data ['credit_name'] );
		if (! $credit)
			return false;
		
		empty ( $data ['uid'] ) && $data ['uid'] = session ( 'mid' );
		empty ( $data ['cTime'] ) && $data ['cTime'] = time ();
		$data ['token'] = get_token ();
		
		isset ( $data ['experience'] ) || $data ['experience'] = $credit ['experience'];
		isset ( $data ['score'] ) || $data ['score'] = $credit ['score'];
		
		$res = $this->add ( $data );
		if ($res) {
			$this->updateFollowTotalCredit ( $data ['uid'] );
		}
		
		return $res;
	}
	// 通过积分标识获取积分配置值
	function getCreditByName($credit_name = null) {
		$token = get_token ();
		$list = M ( 'credit_config' )->where ( 'token="0" or token="' . $token . '"' )->select ();
		
		$admin_config = $public_config = array ();
		foreach ( $list as $vo ) {
			if ($vo ['token'] == 0) {
				$admin_config [$vo ['name']] = $vo; // 后台的配置
			} else {
				$public_config [$vo ['name']] = $vo; // 公众号的配置
			}
		}
		$config = array_merge ( $admin_config, $public_config ); // 公众号的配置优化于后台的配置
		
		if ($credit_name)
			return $config [$credit_name];
		
		return $config;
	}
	// 更新个人总积分
	function updateFollowTotalCredit($uid) {
		$map ['uid'] = $map2 ['id'] = $uid;
		$map ['token'] = get_token ();
		$info = $this->where ( $map )->field ( 'sum(score) as score, sum(experience) as experience' )->find ();
		
		$res = M ( 'follow' )->where ( $map2 )->save ( $info );
	}
}
?>
