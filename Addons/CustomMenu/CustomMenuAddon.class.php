<?php

namespace Addons\CustomMenu;
use Common\Controller\Addon;

/**
 * 自定义菜单插件
 * @author 凡星
 */

    class CustomMenuAddon extends Addon{

        public $info = array(
            'name'=>'CustomMenu',
            'title'=>'自定义菜单',
            'description'=>'自定义菜单能够帮助公众号丰富界面，让用户更好更快地理解公众号的功能',
            'status'=>1,
            'author'=>'凡星',
            'version'=>'0.1',
            'has_adminlist'=>1,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/CustomMenu/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/CustomMenu/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }