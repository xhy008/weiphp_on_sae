<?php

namespace Addons\Exam;
use Common\Controller\Addon;

/**
 * 微考试插件
 * @author 凡星
 */

    class ExamAddon extends Addon{

        public $info = array(
            'name'=>'Exam',
            'title'=>'微考试',
            'description'=>'主要功能有试卷管理，题目录入管理，考生信息和考分汇总管理。',
            'status'=>1,
            'author'=>'凡星',
            'version'=>'0.1',
            'has_adminlist'=>1,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/Exam/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/Exam/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }