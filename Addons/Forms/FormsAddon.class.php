<?php

namespace Addons\Forms;
use Common\Controller\Addon;

/**
 * 通用表单插件
 * @author 凡星
 */

    class FormsAddon extends Addon{

        public $info = array(
            'name'=>'Forms',
            'title'=>'通用表单',
            'description'=>'管理员可以轻松地增加一个表单用于收集用户的信息，如活动报名、调查反馈、预约填单等',
            'status'=>1,
            'author'=>'凡星',
            'version'=>'0.1',
            'has_adminlist'=>1,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/Forms/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/Forms/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }