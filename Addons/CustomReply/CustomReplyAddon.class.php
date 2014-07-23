<?php

namespace Addons\CustomReply;
use Common\Controller\Addon;

/**
 * 自定义回复插件
 * @author 凡星
 */

    class CustomReplyAddon extends Addon{

        public $info = array(
            'name'=>'CustomReply',
            'title'=>'自定义回复',
            'description'=>'支持图文回复、多图文回复、文本回复功能',
            'status'=>1,
            'author'=>'凡星',
            'version'=>'0.1',
            'has_adminlist'=>1,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/CustomReply/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/CustomReply/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }