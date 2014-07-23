<?php

namespace Addons\Extensions;
use Common\Controller\Addon;

/**
 * 融合第三方插件
 * @author 凡星
 */

    class ExtensionsAddon extends Addon{

        public $info = array(
            'name'=>'Extensions',
            'title'=>'融合第三方',
            'description'=>'第三方功能扩展',
            'status'=>1,
            'author'=>'凡星',
            'version'=>'0.1',
            'has_adminlist'=>1,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/Extensions/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/Extensions/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }