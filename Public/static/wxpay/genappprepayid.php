<?php
include_once("WxPayHelper.php");



//get access token

$ch = curl_init();//初始化curl
curl_setopt($ch,CURLOPT_URL,'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.APPID.'&secret='.APPSERCERT);
curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
$data = curl_exec($ch);//运行curl
curl_close($ch);
$jsondata = json_decode($data);


// gen app package
$commonUtil = new CommonUtil();
$wxPayHelper = new WxPayHelper();
$wxPayHelper->setParameter("bank_type", "WX");
$wxPayHelper->setParameter("body", "test");
$wxPayHelper->setParameter("partner", "1900000109");
$wxPayHelper->setParameter("out_trade_no", $commonUtil->create_noncestr());
$wxPayHelper->setParameter("total_fee", "1");
$wxPayHelper->setParameter("fee_type", "1");
$wxPayHelper->setParameter("notify_url", "htttp://www.baidu.com");
$wxPayHelper->setParameter("spbill_create_ip", "127.0.0.1");
$wxPayHelper->setParameter("input_charset", "GBK");

$curlPost = $wxPayHelper->create_app_package("test");


//get prepay id
$ch2 = curl_init();//初始化curl
curl_setopt($ch2,CURLOPT_URL,'https://api.weixin.qq.com/pay/genprepay?access_token='.$jsondata->access_token);
curl_setopt($ch2, CURLOPT_HEADER, 0);//设置header
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
curl_setopt($ch2, CURLOPT_POST, 1);//post提交方式
curl_setopt($ch2, CURLOPT_POSTFIELDS, $curlPost);
$data2 = curl_exec($ch2);//运行curl
curl_close($ch2);
//$jsondata2 = json_decode($data2);



echo $data2;



?>