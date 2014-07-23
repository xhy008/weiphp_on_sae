<?php
return array (
		'need_bind' => array ( // 配置在表单中的键名 ,这个会是config[random]
				'title' => '是否开启绑定:', // 表单的文字
				'type' => 'radio', // 表单的类型：text、textarea、checkbox、radio、select等
				'options' => array ( // select 和radion、checkbox的子选项
						'1' => '是', // 值=>文字
						'0' => '否' 
				),
				'value' => '0'  // 表单的默认值
				) ,
		'bind_start' => array (
				'title' => '绑定触发条件:', 
				'type' => 'radio', 
				'options' => array ( 
				        '0' => '访问3G页面时', 
						'1' => '微信交互时',
						'2' => '以上全部'
				),
				'value' => '0',  
				'tip' => '什么时候要求用户绑定' 
				) ,				
		'jumpurl' => array ( 
				'title' => '绑定成功后默认跳转的地址:', 
				'type' => 'text', 
				'value' => ''  ,
				'tip' => '优先跳转回原来的页面，没有时跳转到此配置的地址，为空时跳转到微官网首页' 
				) 
);
					