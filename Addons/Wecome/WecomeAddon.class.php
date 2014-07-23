<?php

namespace Addons\Wecome;
use Common\Controller\Addon;

/**
 * 欢迎语插件
 * @author 凡星
 */

    class WecomeAddon extends Addon{
		
		public $custom_config = 'config.html';

        public $info = array(
            'name'=>'Wecome',
            'title'=>'欢迎语',
            'description'=>'用户关注公众号时发送的欢迎信息，支持文本，图片，图文的信息',
            'status'=>1,
            'author'=>'凡星',
            'version'=>'0.1'
        );

        public function install(){
            return true;
        }

        public function uninstall(){
            return true;
        }

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }