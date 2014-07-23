<?php

namespace Addons\Card;
use Common\Controller\Addon;

/**
 * 会员卡插件
 * @author 无名
 */

    class CardAddon extends Addon{

        public $info = array(
            'name'=>'Card',
            'title'=>'会员卡',
            'description'=>'提供会员卡基本功能：会员卡制作、会员管理、通知发布、优惠券发布等功能，用户可在此基础上扩展自己的具体业务需求，如积分、充值、签到等',
            'status'=>1,
            'author'=>'凡星',
            'version'=>'0.1',
            'has_adminlist'=>0,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/Card/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/Card/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }