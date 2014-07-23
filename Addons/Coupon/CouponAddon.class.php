<?php

namespace Addons\Coupon;
use Common\Controller\Addon;

/**
 * 优惠券插件
 * @author 凡星
 */

    class CouponAddon extends Addon{

        public $info = array(
            'name'=>'Coupon',
            'title'=>'优惠券',
            'description'=>'配合粉丝圈子，打造粉丝互动的运营激励基础',
            'status'=>1,
            'author'=>'凡星',
            'version'=>'0.1',
            'has_adminlist'=>1,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/Coupon/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/Coupon/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }