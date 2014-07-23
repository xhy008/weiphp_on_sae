<?php
include_once("WxPayHelper.php");



$wxPayHelper = new WxPayHelper();

echo $wxPayHelper->create_native_url("1234");
?>