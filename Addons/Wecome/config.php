<?php
return array (
		'type' => array ( // 配置在表单中的键名 ,这个会是config[random]
				'title' => '类型:', // 表单的文字
				'type' => 'select', // 表单的类型：text、textarea、checkbox、radio、select等
				'options'=>array(
						'1'=>'文本',
						//'2'=>'图片',
						'3'=>'图文'
				),
				'value' => '1'  // 表单的默认值
				),
		'title' => array ( // 配置在表单中的键名 ,这个会是config[random]
				'title' => '标题:', // 表单的文字
				'type' => 'text', // 表单的类型：text、textarea、checkbox、radio、select等
				'value' => ''  // 表单的默认值
				),
		'description' => array (
				'title' => '内容:',
				'type' => 'textarea',
				'value' => '' 
		),
		'pic_url' => array (
				'title' => '图片:',
				'type' => 'text',
				'value' => '',
				'tip' => '图片链接，支持JPG、PNG格式，较好的效果为大图360*200，小图200*200' 
		),
		'url' => array (
				'title' => '链接:',
				'type' => 'text',
				'value' => '',
				'tip' => '点击图文消息跳转链接' 
		) 
);
					