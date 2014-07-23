<?php

namespace Addons\Robot;
use Common\Controller\Addon;

/**
 * 机器人聊天插件
 * @author 凡星
 */

    class RobotAddon extends Addon{

        public $info = array(
            'name'=>'Robot',
            'title'=>'机器人聊天',
            'description'=>'实现的效果如下
用户输入：“机器人学习时间”
微信回复：“你的问题是？”
用户输入：“这个世界上谁最美？”
微信回复： “你的答案是？”
用户回复： “当然是你啦！”
微信回复：“我明白啊，不信你可以问问我”
用户回复：“这个世界上谁最美？”
微信回复：“当然是你啦！”',
            'status'=>1,
            'author'=>'凡星',
            'version'=>'0.1'
        );

	public function install() {
		$install_sql = './Addons/Robot/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/Robot/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }