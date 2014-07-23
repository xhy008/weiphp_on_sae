<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace Admin\Controller;

use User\Api\UserApi as UserApi;

/**
 * 后台首页控制器
 *
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class IndexController extends AdminController {
	protected static $allow = array (
			'verify' 
	);
	
	/**
	 * 后台首页
	 *
	 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
	 */
	public function index() {
		if (UID) {
			$hide = M ( 'menu' )->where ( 'id=1' )->getField ( 'hide' );
			if ($hide) {
				$url = M ( 'menu' )->where ( 'pid=0 AND hide=0' )->order ( 'sort ASC' )->getField ( 'url' );
				redirect ( U ( $url ) );
			}
			$this->display ();
		} else {
			$this->redirect ( 'Public/login' );
		}
	}
}
