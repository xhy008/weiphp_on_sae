<?php

namespace Addons\Juhe\Model;

use Home\Model\WeixinModel;

/**
 * Juhe的微信模型
 */
class WeixinAddonModel extends WeixinModel {
	function reply($dataArr, $keywordArr = array()) {
		$config = getAddonConfig ( 'Juhe' ); // 获取后台插件的配置参数
		if ($keywordArr ['keyword'] == '股票') {
			if ($dataArr ['Content'] == $keywordArr ['keyword']) {
				set_user_status ( 'Juhe', $keywordArr );
				
				$this->replyText ( '请输入股票编号，上海股市以sh开头，深圳股市以sz开头如：sh601009' );
			} else {
				$url = 'http://web.juhe.cn:8080/finance/stock/hs?gid=' . $dataArr ['Content'] . '&key=' . $config ['stock'];
				$content = file_get_contents ( $url );
				$content = json_decode ( $content, true );
				$content = $content ['result'] [0] ['dapandata'];
				if (empty ( $content )) {
					$this->replyText ( '股票编号不对' );
					return false;
				}
				
				$str = "名称: " . $content ['name'] . "\n当前点数: " . $content ['dot'] . "\n当前价格: " . $content ['nowPic'] . "\n涨跌率: " . $content ['rate'] . "\n成交量: " . $content ['traNumber'] . "手\n成交金额: " . $content ['traAmount'] . "万元\r\n";
				$str .= '如需查询其它股票，请在菜单再次点击"股票查询"重新进入查询';
				
				$this->replyText ( $str );
			}
		} elseif ($keywordArr ['keyword'] == '黄金') {
			if ($dataArr ['Content'] == $keywordArr ['keyword']) {
				$url = 'http://web.juhe.cn:8080/finance/gold/shgold?key=' . $config ['gold'];
				$content = file_get_contents ( $url );
				addWeixinLog ( $content, $url );
				$content = json_decode ( $content, true );
				$keywordArr ['content'] = $content ['result'] [0];
				
				$str = "请输入以下列表前的编号查询，如要查询品种为 Ag(T+D)的情况，回复：1\r\n";
				$accept ['type'] = 'array';
				foreach ( $keywordArr ['content'] as $key => $vo ) {
					$str .= $key . ': ' . $vo ['variety'] . "\n";
					$accept ['data'] [] = $key;
				}
				$keywordArr ['accept'] = $accept;
				
				set_user_status ( 'Juhe', $keywordArr );
				
				$this->replyText ( $str );
			} else {
				$vo = $keywordArr ['content'] [$dataArr ['Content']];
				$str = "品种: " . $vo ['variety'] . "\n最新价: " . $vo ['latestpri'] . "\n开盘价: " . $vo ['openpri'] . "\n最高价: " . $vo ['maxpri'] . "\n最低价: " . $vo ['minpri'] . "\n涨跌幅: " . $vo ['limit'] . "\n昨收价: " . $vo ['yespri'] . "\n总成交量: " . $vo ['totalvol'] . "\r\n";
				$str .= '如需查询其它黄金品种，请在菜单再次点击"黄金数据"重新进入查询';
				$this->replyText ( $str );
			}
		} elseif ($keywordArr ['keyword'] == '货币汇率') {
			$url = 'http://web.juhe.cn:8080/finance/exchange/rmbquot?key=' . $config ['exchange'];
			$content = file_get_contents ( $url );
			$content = json_decode ( $content, true );
			$content = $content ['result'] [0];
			
			$str = "货币 ： 中间价\r\n";
			foreach ( $content as $vo ) {
				$str .= $vo ['name'] . ' ：' . $vo ['bankConversionPri'] . "\n";
			}
			
			$this->replyText ( $str );
		}
	}
	
	// 关注公众号事件
	public function subscribe() {
		return true;
	}
	
	// 取消关注公众号事件
	public function unsubscribe() {
		return true;
	}
	
	// 扫描带参数二维码事件
	public function scan() {
		return true;
	}
	
	// 上报地理位置事件
	public function location() {
		return true;
	}
	
	// 自定义菜单事件
	public function click() {
		return true;
	}
}
        	