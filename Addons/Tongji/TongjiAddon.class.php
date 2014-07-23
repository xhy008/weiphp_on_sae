<?php

namespace Addons\Tongji;
use Common\Controller\Addon;

/**
 * 运营统计插件
 * @author 凡星
 */

    class TongjiAddon extends Addon{

        public $info = array(
            'name'=>'Tongji',
            'title'=>'运营统计',
            'description'=>'统计每个插件使用情况',
            'status'=>1,
            'author'=>'凡星',
            'version'=>'0.1',
            'has_adminlist'=>1,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/Tongji/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/Tongji/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }