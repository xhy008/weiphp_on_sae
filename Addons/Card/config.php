<?php
$bg = array();
for ($i=1; $i<11;$i++){
	$bg[$i] = '背景'.$i;
}
$bg[$i] = '自定义';
return array (
		'background' => array ( // 配置在表单中的键名 ,这个会是config[random]
				'title' => '背景图:', // 表单的文字
				'type' => 'select', // 表单的类型：text、textarea、checkbox、radio、select等
				'options'=>$bg,	
				'value' => '1',
				'tip'=>''
				),

		'title' => array (
				'title' => '卡名:',
				'type' => 'text',
				'value' => '时尚美容美发店VIP会员卡',
				'tip'=>''
		) ,
		'length' => array ( 
				'title' => '卡号位数:', 
				'type' => 'select', 
				'options'=>array(
						'80001'=>'5',
						'800001'=>'6',
						'8000001'=>'7',
						'80000001'=>'8',
						'80000001'=>'9',
						'800000001'=>'10'
				),	
				'value' => '80001',
				'tip'=>''
		),
		'instruction' => array (
				'title' => '使用说明:',
				'type' => 'textarea',
				'value' => '1、恭喜您成为时尚美容美发店VIP会员;
2、结账时请出示此卡，凭此卡可享受会员优惠;
3、此卡最终解释权归时尚美容美发店所有',
				'tip'=>''
		),	
		'address' => array (
				'title' => '地址:',
				'type' => 'text',
				'value' => '',
				'tip'=>''
		) ,	
		'phone' => array (
				'title' => '电话:',
				'type' => 'text',
				'value' => '',
				'tip'=>''
		) ,	
		'url' => array (
				'title' => '网址:',
				'type' => 'text',
				'value' => '',
				'tip'=>''
		) ,
		'background_custom' => array ( // 配置在表单中的键名 ,这个会是config[random]
				'type' => 'hidden', // 表单的类型：text、textarea、checkbox、radio、select等
		),		
);
					