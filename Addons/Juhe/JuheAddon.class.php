<?php

namespace Addons\Juhe;
use Common\Controller\Addon;

/**
 * 聚合数据插件
 * @author 凡星
 */

    class JuheAddon extends Addon{

        public $info = array(
            'name'=>'Juhe',
            'title'=>'聚合数据',
            'description'=>'集成聚合数据（http://www.juhe.cn）平台的功能',
            'status'=>1,
            'author'=>'凡星',
            'version'=>'0.1',
            'has_adminlist'=>0,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/Juhe/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/Juhe/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }