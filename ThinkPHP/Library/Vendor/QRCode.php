<?php
class QRCode {
	private $token;
	private $appID;
	private $appSecret;
	private $accessToken;
	public function __CONSTRUCT() {
		$this->token = TOKEN;
		$this->appID = APP_ID;
		$this->appSecret = APP_SECRET;
		$this->accessToken = RUNTIME_PATH . TOKEN . '_access_token';
		if (! file_exists ( $this->accessToken )) {
			$this->AccessTokenGet ();
		}
	}
	/* 创建二维码 @param - $qrcodeID传递的参数，$qrcodeType二维码类型 默认为临时二维码 @return - 返回二维码图片地址 */
	public function QrcodeCreate($qrcodeID, $qrcodeType = 0) {
		if ($qrcodeType == 0) {
			$qrcodeType = 'QR_SCENE';
		} else {
			$qrcodeType = 'QR_LIMIT_SCENE';
		}
		$tempJson = '{"expire_seconds": 1800, "action_name": "' . $qrcodeType . '", "action_info": {"scene": {"scene_id": ' . $qrcodeID . '}}}';
		$access_token = file_get_contents ( $this->accessToken );
		$url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=" . $access_token;
		$tempArr = json_decode ( $this->JsonPost ( $url, $tempJson ), true );
		if (@array_key_exists ( 'ticket', $tempArr )) {
			return 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . $tempArr ['ticket'];
		} else {
			$this->ErrorLogger ( 'qrcode create falied.' );
			return false;
		}
	}
	
	/* 从微信服务器获取access_token并写入配置文件 */
	private function AccessTokenGet() {
		$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $this->appID . '&secret=' . $this->appSecret;
		$tempArr = json_decode ( file_get_contents ( $url ), true );
		if (@array_key_exists ( 'access_token', $tempArr )) {
			$tempWriter = fopen ( $this->accessToken, 'w' );
			fwrite ( $tempWriter, $tempArr ['access_token'] );
		} else {
			$this->ErrorLogger ( 'access_token get falied.' );
			exit ();
		}
	}
	/* 用户分组查询 */
	public function GroupsQuery() {
		$access_token = file_get_contents ( $this->accessToken );
		$url = 'https://api.weixin.qq.com/cgi-bin/groups/get?access_token=' . $access_token;
		$tempArr = json_decode ( file_get_contents ( $url ), true );
		if (@array_key_exists ( 'groups', $tempArr )) {
			return $tempArr ['groups']; // 返回数组格式的分组信息
		} else {
			$this->ErrorLogger ( 'groups query falied.' );
			$this->AccessTokenGet ();
			$this->GroupsQuery ();
		}
	}
	
	// 工具函数 //
	/* 使用curl来post一个json数据 */
	// CURLOPT_SSL_VERIFYPEER,CURLOPT_SSL_VERIFYHOST - 在做https中要用到
	// CURLOPT_RETURNTRANSFER - 不以文件流返回，带1
	private function JsonPost($url, $jsonData) {
		$curl = curl_init ();
		curl_setopt ( $curl, CURLOPT_URL, $url );
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
		curl_setopt ( $curl, CURLOPT_POST, 1 );
		curl_setopt ( $curl, CURLOPT_POSTFIELDS, $jsonData );
		curl_setopt ( $curl, CURLOPT_TIMEOUT, 30 );
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
		$result = curl_exec ( $curl );
		if (curl_errno ( $curl )) {
			$this->ErrorLogger ( 'curl falied. Error Info: ' . curl_error ( $curl ) );
		}
		curl_close ( $curl );
		return $result;
	}
	/* 错误日志记录 */
	private function ErrorLogger($errMsg) {
		$logger = fopen ( './ErrorLog.txt', 'a+' );
		fwrite ( $logger, date ( 'Y-m-d H:i:s' ) . " Error Info : " . $errMsg . "\r\n" );
	}
}
?>