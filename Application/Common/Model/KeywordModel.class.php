<?php

namespace Common\Model;

use Think\Model;

/**
 * 关键词操作
 */
class KeywordModel extends Model {
	/**
	 * 保存关键词
	 * 注意：在aim_id==0的情况下编辑关键词时，由于无法定位到具体的关键词，所以插件需要自行删除旧的关键词
	 */
	function set($keyword, $addon, $aim_id, $keyword_type = 0, $extra_text = '', $extra_int = '') {
		$data ['addon'] = $addon;
		$data ['aim_id'] = intval ( $aim_id );
		$data ['extra_text'] = $extra_text;
		$data ['extra_int'] = intval ( $extra_int );		
		$data ['aim_id'] == 0 || $this->where ( $data )->delete ();

		$data ['token'] = get_token ();
		$data ['cTime'] = time ();
		$data ['keyword_type'] = intval ( $keyword_type );
		
		$keyword = preg_split ( '/[\s,;]+/', $keyword ); // 以空格tab逗号分号分割关键词
		foreach ( $keyword as $key ) {
			$data ['keyword'] = trim ( $key );
			$data ['keyword_length'] = strlen ( $data ['keyword'] );
			$res = $this->add ( $data );
		}

		return $res;
	}
	/**
	 * 通过关键词获取相关插件信息
	 */
	function get($keyword, $keyword_type = 0) {
		$map ['keyword'] = $keyword;
		$map ['token'] = get_token ();
		$map ['keyword_type'] = $keyword_type;
		return $this->where ( $map )->find ();
	}
	/**
	 * 删除关键词
	 */
	function del($addon, $aim_id, $keyword_type = NULL) {
		$data ['addon'] = $addon;
		$data ['aim_id'] = intval ( $aim_id );
		$keyword_type === NULL || $data ['keyword_type'] = $keyword_type;
		return $this->where ( $data )->delete ();
	}
	/**
	 * 按目标条件统一更新关键词
	 */
	function upateByAddon($addon, $aim_id = 0, $save = array()) {
		$map ['addon'] = $addon;
		if ($aim_id) {
			$map ['aim_id'] = intval ( $aim_id );
		}
		
		return $this->where ( $map )->save ( $save );
	}
}
?>
