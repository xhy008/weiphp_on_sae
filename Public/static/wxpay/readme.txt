使用说明：
(1) 在WxPay.config.php里面设置好appid，appkey，partnerkey
(2) include WxPayHelper.php 就可以生成相应的支付请求

demo说明：
(1) 原生支付
nativecall.php      微信后台调用输出xml
createnativeurl.php 生成拉起原生支付的url
(2) jsapi支付
jsapicall.php  jsapi支付
(3) app支付
example.php 里面 $wxPayHelper->create_app_package("test"); 可以生成对应的预先支付package
genappprepayid.php 整个生成prepayid的过程，第三方app可以在用户选择商品后，通过自己后端的协议直接生成prepayid，然后采用微信客户端的协议来调起微信支付。