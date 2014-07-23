<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: yangweijie <yangweijiester@gmail.com> <code-tech.diandian.com>
// +----------------------------------------------------------------------
namespace Home\Model;

use Think\Model;

/**
 * 插件模型
 *
 * @author yangweijie <yangweijiester@gmail.com>
 */
class AddonsModel extends Model {
	/**
	 * 获取微信插件列表
	 *
	 * @param string $addon_dir        	
	 */
	public function getWeixinList($isAll = false, $token_status = array(), $is_admin=false) {
		$where ['status'] = 1;
		$where ['type'] = 1;
		$list = $this->where ( $where )->select ();
		$isAll || $token_status = D ( 'Common/AddonStatus' )->getList ($is_admin);
		foreach ( $list as $addon ) {
			if (! $isAll && isset ( $token_status [$addon ['name']] ) && $token_status [$addon ['name']] < 1)
				continue;
			
			if ($addon ['has_adminlist']) {
				$addon ['addons_url'] = addons_url ( $addon ['name'] . '://' . $addon ['name'] . '/lists' );
			} elseif (file_exists ( ONETHINK_ADDON_PATH . $addon ['name'] . '/config.php' )) {
				$addon ['addons_url'] = addons_url ( $addon ['name'] . '://' . $addon ['name'] . '/config' );
			} else {
				$addon ['addons_url'] = addons_url ( $addon ['name'] . '://' . $addon ['name'] . '/nulldeal' );
			}
			
			$addons [$addon ['name']] = $addon;
		}
		
		return $addons;
	}
}
