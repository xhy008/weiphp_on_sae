<?php

namespace Addons\HelloWorld;
use Common\Controller\Addon;

/**
 * HelloWorld插件
 * @author 凡星
 */

    class HelloWorldAddon extends Addon{

        public $info = array(
            'name'=>'HelloWorld',
            'title'=>'微信入门案例',
            'description'=>'这是一个简单的入门案例',
            'status'=>1,
            'author'=>'凡星',
            'version'=>'0.1',
            'has_adminlist'=>0,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/HelloWorld/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/HelloWorld/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }