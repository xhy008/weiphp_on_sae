<?php

namespace Addons\BaiduStatistics;
use Common\Controller\Addon;

/**
 * 百度统计插件
 * @author weiphp.cn
 */

    class BaiduStatisticsAddon extends Addon{

        public $info = array(
            'name'=>'BaiduStatistics',
            'title'=>'百度统计',
            'description'=>'这是百度统计功能，只要开启插件并设置统计代码，就可以使用统计功能了',
            'status'=>1,
            'author'=>'weiphp.cn',
            'version'=>'1.0'
        );

        public function install(){
            return true;
        }

        public function uninstall(){
            return true;
        }

        //实现的pageFooter钩子方法
        public function pageFooter($param){
        	$config = $this->getConfig();
        	echo '<div class="hidden" style="display: none !important;visibility: hidden !important;">'.$config['code'].'</div>';
        }

    }