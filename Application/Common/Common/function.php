<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

// OneThink常量定义
const ONETHINK_VERSION = '1.0.131218';
const ONETHINK_ADDON_PATH = './Addons/';

/**
 * 系统公共库文件
 * 主要定义系统公共函数库
 */

/**
 * 检测用户是否登录
 *
 * @return integer 0-未登录，大于0-当前登录用户ID
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function is_login() {
	$user = session ( 'user_auth' );
	if (empty ( $user )) {
		return 0;
	} else {
		return session ( 'user_auth_sign' ) == data_auth_sign ( $user ) ? $user ['uid'] : 0;
	}
}

/**
 * 检测当前用户是否为管理员
 *
 * @return boolean true-管理员，false-非管理员
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function is_administrator($uid = null) {
	$uid = is_null ( $uid ) ? is_login () : $uid;
	return $uid && (intval ( $uid ) === C ( 'USER_ADMINISTRATOR' ));
}

/**
 * 字符串转换为数组，主要用于把分隔符调整到第二个参数
 *
 * @param string $str
 *        	要分割的字符串
 * @param string $glue
 *        	分割符
 * @return array
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function str2arr($str, $glue = ',') {
	return explode ( $glue, $str );
}

/**
 * 数组转换为字符串，主要用于把分隔符调整到第二个参数
 *
 * @param array $arr
 *        	要连接的数组
 * @param string $glue
 *        	分割符
 * @return string
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function arr2str($arr, $glue = ',') {
	return implode ( $glue, $arr );
}

/**
 * 字符串截取，支持中文和其他编码
 *
 * @access public
 * @param string $str
 *        	需要转换的字符串
 * @param string $start
 *        	开始位置
 * @param string $length
 *        	截取长度
 * @param string $charset
 *        	编码格式
 * @param string $suffix
 *        	截断显示字符
 * @return string
 */
function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true) {
	if (function_exists ( "mb_substr" ))
		$slice = mb_substr ( $str, $start, $length, $charset );
	elseif (function_exists ( 'iconv_substr' )) {
		$slice = iconv_substr ( $str, $start, $length, $charset );
		if (false === $slice) {
			$slice = '';
		}
	} else {
		$re ['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
		$re ['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
		$re ['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
		$re ['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
		preg_match_all ( $re [$charset], $str, $match );
		$slice = join ( "", array_slice ( $match [0], $start, $length ) );
	}
	return $suffix ? $slice . '...' : $slice;
}
/**
 * 方法增强，根据$length自动判断是否应该显示...
 * 字符串截取，支持中文和其他编码
 * QQ:125682133
 *
 * @access public
 * @param string $str
 *        	需要转换的字符串
 * @param string $start
 *        	开始位置
 * @param string $length
 *        	截取长度
 * @param string $charset
 *        	编码格式
 * @param string $suffix
 *        	截断显示字符
 * @return string
 */
function msubstr_local($str, $start = 0, $length, $charset = "utf-8") {
	if (function_exists ( "mb_substr" ))
		$slice = mb_substr ( $str, $start, $length, $charset );
	elseif (function_exists ( 'iconv_substr' )) {
		$slice = iconv_substr ( $str, $start, $length, $charset );
		if (false === $slice) {
			$slice = '';
		}
	} else {
		$re ['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
		$re ['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
		$re ['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
		$re ['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
		preg_match_all ( $re [$charset], $str, $match );
		
		$slice = join ( "", array_slice ( $match [0], $start, $length ) );
	}
	return (strlen ( $str ) > strlen ( $slice )) ? $slice . '...' : $slice;
}
/**
 * 系统加密方法
 *
 * @param string $data
 *        	要加密的字符串
 * @param string $key
 *        	加密密钥
 * @param int $expire
 *        	过期时间 单位 秒
 * @return string
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function think_encrypt($data, $key = '', $expire = 0) {
	$key = md5 ( empty ( $key ) ? C ( 'DATA_AUTH_KEY' ) : $key );
	$data = base64_encode ( $data );
	$x = 0;
	$len = strlen ( $data );
	$l = strlen ( $key );
	$char = '';
	
	for($i = 0; $i < $len; $i ++) {
		if ($x == $l)
			$x = 0;
		$char .= substr ( $key, $x, 1 );
		$x ++;
	}
	
	$str = sprintf ( '%010d', $expire ? $expire + time () : 0 );
	
	for($i = 0; $i < $len; $i ++) {
		$str .= chr ( ord ( substr ( $data, $i, 1 ) ) + (ord ( substr ( $char, $i, 1 ) )) % 256 );
	}
	return str_replace ( array (
			'+',
			'/',
			'=' 
	), array (
			'-',
			'_',
			'' 
	), base64_encode ( $str ) );
}

/**
 * 系统解密方法
 *
 * @param string $data
 *        	要解密的字符串 （必须是think_encrypt方法加密的字符串）
 * @param string $key
 *        	加密密钥
 * @return string
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function think_decrypt($data, $key = '') {
	$key = md5 ( empty ( $key ) ? C ( 'DATA_AUTH_KEY' ) : $key );
	$data = str_replace ( array (
			'-',
			'_' 
	), array (
			'+',
			'/' 
	), $data );
	$mod4 = strlen ( $data ) % 4;
	if ($mod4) {
		$data .= substr ( '====', $mod4 );
	}
	$data = base64_decode ( $data );
	$expire = substr ( $data, 0, 10 );
	$data = substr ( $data, 10 );
	
	if ($expire > 0 && $expire < time ()) {
		return '';
	}
	$x = 0;
	$len = strlen ( $data );
	$l = strlen ( $key );
	$char = $str = '';
	
	for($i = 0; $i < $len; $i ++) {
		if ($x == $l)
			$x = 0;
		$char .= substr ( $key, $x, 1 );
		$x ++;
	}
	
	for($i = 0; $i < $len; $i ++) {
		if (ord ( substr ( $data, $i, 1 ) ) < ord ( substr ( $char, $i, 1 ) )) {
			$str .= chr ( (ord ( substr ( $data, $i, 1 ) ) + 256) - ord ( substr ( $char, $i, 1 ) ) );
		} else {
			$str .= chr ( ord ( substr ( $data, $i, 1 ) ) - ord ( substr ( $char, $i, 1 ) ) );
		}
	}
	return base64_decode ( $str );
}

/**
 * 数据签名认证
 *
 * @param array $data
 *        	被认证的数据
 * @return string 签名
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function data_auth_sign($data) {
	// 数据类型检测
	if (! is_array ( $data )) {
		$data = ( array ) $data;
	}
	ksort ( $data ); // 排序
	$code = http_build_query ( $data ); // url编码并生成query字符串
	$sign = sha1 ( $code ); // 生成签名
	return $sign;
}

/**
 * 对查询结果集进行排序
 *
 * @access public
 * @param array $list
 *        	查询结果
 * @param string $field
 *        	排序的字段名
 * @param array $sortby
 *        	排序类型
 *        	asc正向排序 desc逆向排序 nat自然排序
 * @return array
 *
 */
function list_sort_by($list, $field, $sortby = 'asc') {
	if (is_array ( $list )) {
		$refer = $resultSet = array ();
		foreach ( $list as $i => $data )
			$refer [$i] = &$data [$field];
		switch ($sortby) {
			case 'asc' : // 正向排序
				asort ( $refer );
				break;
			case 'desc' : // 逆向排序
				arsort ( $refer );
				break;
			case 'nat' : // 自然排序
				natcasesort ( $refer );
				break;
		}
		foreach ( $refer as $key => $val )
			$resultSet [] = &$list [$key];
		return $resultSet;
	}
	return false;
}

/**
 * 把返回的数据集转换成Tree
 *
 * @param array $list
 *        	要转换的数据集
 * @param string $pid
 *        	parent标记字段
 * @param string $level
 *        	level标记字段
 * @return array
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0) {
	// 创建Tree
	$tree = array ();
	if (is_array ( $list )) {
		// 创建基于主键的数组引用
		$refer = array ();
		foreach ( $list as $key => $data ) {
			$refer [$data [$pk]] = & $list [$key];
		}
		foreach ( $list as $key => $data ) {
			// 判断是否存在parent
			$parentId = $data [$pid];
			if ($root == $parentId) {
				$tree [] = & $list [$key];
			} else {
				if (isset ( $refer [$parentId] )) {
					$parent = & $refer [$parentId];
					$parent [$child] [] = & $list [$key];
				}
			}
		}
	}
	return $tree;
}

/**
 * 将list_to_tree的树还原成列表
 *
 * @param array $tree
 *        	原来的树
 * @param string $child
 *        	孩子节点的键
 * @param string $order
 *        	排序显示的键，一般是主键 升序排列
 * @param array $list
 *        	过渡用的中间数组，
 * @return array 返回排过序的列表数组
 * @author yangweijie <yangweijiester@gmail.com>
 */
function tree_to_list($tree, $child = '_child', $order = 'id', &$list = array()) {
	if (is_array ( $tree )) {
		$refer = array ();
		foreach ( $tree as $key => $value ) {
			$reffer = $value;
			if (isset ( $reffer [$child] )) {
				unset ( $reffer [$child] );
				tree_to_list ( $value [$child], $child, $order, $list );
			}
			$list [] = $reffer;
		}
		$list = list_sort_by ( $list, $order, $sortby = 'asc' );
	}
	return $list;
}

/**
 * 格式化字节大小
 *
 * @param number $size
 *        	字节数
 * @param string $delimiter
 *        	数字和单位分隔符
 * @return string 格式化后的带单位的大小
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function format_bytes($size, $delimiter = '') {
	$units = array (
			'B',
			'KB',
			'MB',
			'GB',
			'TB',
			'PB' 
	);
	for($i = 0; $size >= 1024 && $i < 5; $i ++)
		$size /= 1024;
	return round ( $size, 2 ) . $delimiter . $units [$i];
}

/**
 * 设置跳转页面URL
 * 使用函数再次封装，方便以后选择不同的存储方式（目前使用cookie存储）
 *
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function set_redirect_url($url) {
	cookie ( 'redirect_url', $url );
}

/**
 * 获取跳转页面URL
 *
 * @return string 跳转页URL
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_redirect_url() {
	$url = cookie ( 'redirect_url' );
	return empty ( $url ) ? __APP__ : $url;
}

/**
 * 处理插件钩子
 *
 * @param string $hook
 *        	钩子名称
 * @param mixed $params
 *        	传入参数
 * @return void
 */
function hook($hook, $params = array()) {
	\Think\Hook::listen ( $hook, $params );
}

/**
 * 获取插件类的类名
 *
 * @param strng $name
 *        	插件名
 */
function get_addon_class($name) {
	$class = "Addons\\{$name}\\{$name}Addon";
	return $class;
}

/**
 * 获取插件类的配置文件数组
 *
 * @param string $name
 *        	插件名
 */
function get_addon_config($name) {
	$class = get_addon_class ( $name );
	if (class_exists ( $class )) {
		$addon = new $class ();
		return $addon->getConfig ();
	} else {
		return array ();
	}
}

/**
 * 插件显示内容里生成访问插件的url
 *
 * @param string $url
 *        	url
 * @param array $param
 *        	参数
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function addons_url($url, $param = array()) {
	// 凡星：修复如user_center://user_center/add 识别错误的问题
	$urlArr = explode ( '://', $url );
	if (stripos ( $urlArr [0], '_' ) !== false) {
		$addons = $urlArr [0];
		$url = 'http://' . $urlArr [1];
	}
	$url = parse_url ( $url );
	$case = C ( 'URL_CASE_INSENSITIVE' );
	! $addons || $url ['scheme'] = $addons;
	$addons = $case ? parse_name ( $url ['scheme'] ) : $url ['scheme'];
	$controller = $case ? parse_name ( $url ['host'] ) : $url ['host'];
	$action = trim ( $case ? strtolower ( $url ['path'] ) : $url ['path'], '/' );
	
	/* 解析URL带的参数 */
	if (isset ( $url ['query'] )) {
		parse_str ( $url ['query'], $query );
		$param = array_merge ( $query, $param );
	}
	
	/* 基础参数 */
	$params = array (
			'_addons' => ucfirst ( $addons ),
			'_controller' => ucfirst ( $controller ),
			'_action' => $action 
	);
	$params = array_merge ( $params, $param ); // 添加额外参数
	
	return U ( 'Home/Addons/execute', $params );
	// $qurl = $addons == $controller ? "/ad/$addons/$action" : "/ad/$addons/$controller/$action";
	// return U ($qurl , $param );
}

/**
 * 时间戳格式化
 *
 * @param int $time        	
 * @return string 完整的时间显示
 * @author huajie <banhuajie@163.com>
 */
function time_format($time = NULL, $format = 'Y-m-d H:i') {
	if (empty ( $time ))
		return '';
	
	$time = $time === NULL ? NOW_TIME : intval ( $time );
	return date ( $format, $time );
}
function day_format($time = NULL) {
	return time_format ( $time, 'Y-m-d' );
}
/**
 * 根据用户ID获取用户名
 *
 * @param integer $uid
 *        	用户ID
 * @return string 用户名
 */
function get_username($uid = 0) {
	static $list;
	if (! ($uid && is_numeric ( $uid ))) { // 获取当前登录用户名
		return session ( 'user_auth.username' );
	}
	
	/* 获取缓存数据 */
	if (empty ( $list )) {
		$list = S ( 'sys_active_user_list' );
	}
	
	/* 查找用户信息 */
	$key = "u{$uid}";
	if (isset ( $list [$key] )) { // 已缓存，直接使用
		$name = $list [$key];
	} else { // 调用接口获取用户信息
		$User = new User\Api\UserApi ();
		$info = $User->info ( $uid );
		if ($info && isset ( $info [1] )) {
			$name = $list [$key] = $info [1];
			/* 缓存用户 */
			$count = count ( $list );
			$max = C ( 'USER_MAX_CACHE' );
			while ( $count -- > $max ) {
				array_shift ( $list );
			}
			S ( 'sys_active_user_list', $list );
		} else {
			$name = '';
		}
	}
	return $name;
}

/**
 * 根据用户ID获取用户昵称
 *
 * @param integer $uid
 *        	用户ID
 * @return string 用户昵称
 */
function get_nickname($uid = 0) {
	$info = get_mult_userinfo ( $uid );
	return $info ['nickname'];
}
function get_truename($uid) {
	$info = D ( 'Home/Member' )->getMemberInfo ( $uid );
	return $info ['truename'];
}
function get_memberinfo($uid) {
	return D ( 'Home/Member' )->getMemberInfo ( $uid );
}
function get_followinfo($id) {
	return D ( 'Common/Follow' )->getFollowInfo ( $id );
}
function get_mult_userinfo($uid) {
	$info = get_followinfo ( $uid );
	if (! $info) {
		$info = get_memberinfo ( $uid );
	}
	return $info;
}
function get_mult_username($uids) {
	is_array ( $uids ) || $uids = explode ( ',', $uids );
	
	$uids = array_filter ( $uids );
	if (empty ( $uids )) {
		return;
	}
	
	foreach ( $uids as $uid ) {
		$name = get_truename ( $uid );
		if ($name) {
			$nameArr [] = $name;
		}
	}
	
	return implode ( ', ', $nameArr );
}
/**
 * 获取分类信息并缓存分类
 *
 * @param integer $id
 *        	分类ID
 * @param string $field
 *        	要获取的字段名
 * @return string 分类信息
 */
function get_category($id, $field = null) {
	static $list;
	
	/* 非法分类ID */
	if (empty ( $id ) || ! is_numeric ( $id )) {
		return '';
	}
	
	/* 读取缓存数据 */
	if (empty ( $list )) {
		$list = S ( 'sys_category_list' );
	}
	
	/* 获取分类名称 */
	if (! isset ( $list [$id] )) {
		$cate = M ( 'Category' )->find ( $id );
		if (! $cate || 1 != $cate ['status']) { // 不存在分类，或分类被禁用
			return '';
		}
		$list [$id] = $cate;
		S ( 'sys_category_list', $list ); // 更新缓存
	}
	return is_null ( $field ) ? $list [$id] : $list [$id] [$field];
}

/* 根据ID获取分类标识 */
function get_category_name($id) {
	return get_category ( $id, 'name' );
}

/* 根据ID获取分类名称 */
function get_category_title($id) {
	return get_category ( $id, 'title' );
}

/**
 * 获取顶级模型信息
 */
function get_top_model($model_id=null){
    $map   = array('status' => 1, 'extend' => 0);
    if(!is_null($model_id)){
        $map['id']  =   array('neq',$model_id);
    }
    $model = M('Model')->where($map)->field(true)->select();
    foreach ($model as $value) {
        $list[$value['id']] = $value;
    }
    return $list;
}

/**
 * 获取文档模型信息
 *
 * @param integer $id
 *        	模型ID
 * @param string $field
 *        	模型字段
 * @return array
 */
function get_document_model($id = null, $field = null) {
	static $list;
	
	/* 非法分类ID */
	if (! (is_numeric ( $id ) || is_null ( $id ))) {
		return '';
	}
	
	/* 读取缓存数据 */
	if (empty ( $list )) {
		$list = S ( 'DOCUMENT_MODEL_LIST' );
	}
	
	/* 获取模型名称 */
	if (empty ( $list )) {
		$map = array (
				'status' => 1,
				'extend' => 1 
		);
		$model = M ( 'Model' )->where ( $map )->field ( true )->select ();
		foreach ( $model as $value ) {
			$list [$value ['id']] = $value;
		}
		S ( 'DOCUMENT_MODEL_LIST', $list ); // 更新缓存
	}
	
	/* 根据条件返回数据 */
	if (is_null ( $id )) {
		return $list;
	} elseif (is_null ( $field )) {
		return $list [$id];
	} else {
		return $list [$id] [$field];
	}
}

/**
 * 解析UBB数据
 *
 * @param string $data
 *        	UBB字符串
 * @return string 解析为HTML的数据
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function ubb($data) {
	// TODO: 待完善，目前返回原始数据
	return $data;
}

/**
 * 记录行为日志，并执行该行为的规则
 *
 * @param string $action
 *        	行为标识
 * @param string $model
 *        	触发行为的模型名
 * @param int $record_id
 *        	触发行为的记录id
 * @param int $user_id
 *        	执行行为的用户id
 * @return boolean
 * @author huajie <banhuajie@163.com>
 */
function action_log($action = null, $model = null, $record_id = null, $user_id = null) {
	
	// 参数检查
	if (empty ( $action ) || empty ( $model ) || empty ( $record_id )) {
		return '参数不能为空';
	}
	if (empty ( $user_id )) {
		$user_id = is_login ();
	}
	
	// 查询行为,判断是否执行
	$action_info = M ( 'Action' )->getByName ( $action );
	if ($action_info ['status'] != 1) {
		return '该行为被禁用或删除';
	}
	
	// 插入行为日志
	$data ['action_id'] = $action_info ['id'];
	$data ['user_id'] = $user_id;
	$data ['action_ip'] = ip2long ( get_client_ip () );
	$data ['model'] = $model;
	$data ['record_id'] = $record_id;
	$data ['create_time'] = NOW_TIME;
	
	// 解析日志规则,生成日志备注
	if (! empty ( $action_info ['log'] )) {
		if (preg_match_all ( '/\[(\S+?)\]/', $action_info ['log'], $match )) {
			$log ['user'] = $user_id;
			$log ['record'] = $record_id;
			$log ['model'] = $model;
			$log ['time'] = NOW_TIME;
			$log ['data'] = array (
					'user' => $user_id,
					'model' => $model,
					'record' => $record_id,
					'time' => NOW_TIME 
			);
			foreach ( $match [1] as $value ) {
				$param = explode ( '|', $value );
				if (isset ( $param [1] )) {
					$replace [] = call_user_func ( $param [1], $log [$param [0]] );
				} else {
					$replace [] = $log [$param [0]];
				}
			}
			$data ['remark'] = str_replace ( $match [0], $replace, $action_info ['log'] );
		} else {
			$data ['remark'] = $action_info ['log'];
		}
	} else {
		// 未定义日志规则，记录操作url
		$data ['remark'] = '操作url：' . $_SERVER ['REQUEST_URI'];
	}
	
	M ( 'ActionLog' )->add ( $data );
	
	if (! empty ( $action_info ['rule'] )) {
		// 解析行为
		$rules = parse_action ( $action, $user_id );
		
		// 执行行为
		$res = execute_action ( $rules, $action_info ['id'], $user_id );
	}
}

/**
 * 解析行为规则
 * 规则定义 table:$table|field:$field|condition:$condition|rule:$rule[|cycle:$cycle|max:$max][;......]
 * 规则字段解释：table->要操作的数据表，不需要加表前缀；
 * field->要操作的字段；
 * condition->操作的条件，目前支持字符串，默认变量{$self}为执行行为的用户
 * rule->对字段进行的具体操作，目前支持四则混合运算，如：1+score*2/2-3
 * cycle->执行周期，单位（小时），表示$cycle小时内最多执行$max次
 * max->单个周期内的最大执行次数（$cycle和$max必须同时定义，否则无效）
 * 单个行为后可加 ； 连接其他规则
 *
 * @param string $action
 *        	行为id或者name
 * @param int $self
 *        	替换规则里的变量为执行用户的id
 * @return boolean array: ， 成功返回规则数组
 * @author huajie <banhuajie@163.com>
 */
function parse_action($action = null, $self) {
	if (empty ( $action )) {
		return false;
	}
	
	// 参数支持id或者name
	if (is_numeric ( $action )) {
		$map = array (
				'id' => $action 
		);
	} else {
		$map = array (
				'name' => $action 
		);
	}
	
	// 查询行为信息
	$info = M ( 'Action' )->where ( $map )->find ();
	if (! $info || $info ['status'] != 1) {
		return false;
	}
	
	// 解析规则:table:$table|field:$field|condition:$condition|rule:$rule[|cycle:$cycle|max:$max][;......]
	$rules = $info ['rule'];
	$rules = str_replace ( '{$self}', $self, $rules );
	$rules = explode ( ';', $rules );
	$return = array ();
	foreach ( $rules as $key => &$rule ) {
		$rule = explode ( '|', $rule );
		foreach ( $rule as $k => $fields ) {
			$field = empty ( $fields ) ? array () : explode ( ':', $fields );
			if (! empty ( $field )) {
				$return [$key] [$field [0]] = $field [1];
			}
		}
		// cycle(检查周期)和max(周期内最大执行次数)必须同时存在，否则去掉这两个条件
		if (! array_key_exists ( 'cycle', $return [$key] ) || ! array_key_exists ( 'max', $return [$key] )) {
			unset ( $return [$key] ['cycle'], $return [$key] ['max'] );
		}
	}
	
	return $return;
}

/**
 * 执行行为
 *
 * @param array $rules
 *        	解析后的规则数组
 * @param int $action_id
 *        	行为id
 * @param array $user_id
 *        	执行的用户id
 * @return boolean false 失败 ， true 成功
 * @author huajie <banhuajie@163.com>
 */
function execute_action($rules = false, $action_id = null, $user_id = null) {
	if (! $rules || empty ( $action_id ) || empty ( $user_id )) {
		return false;
	}
	
	$return = true;
	foreach ( $rules as $rule ) {
		
		// 检查执行周期
		$map = array (
				'action_id' => $action_id,
				'user_id' => $user_id 
		);
		$map ['create_time'] = array (
				'gt',
				NOW_TIME - intval ( $rule ['cycle'] ) * 3600 
		);
		$exec_count = M ( 'ActionLog' )->where ( $map )->count ();
		if ($exec_count > $rule ['max']) {
			continue;
		}
		
		// 执行数据库操作
		$Model = M ( ucfirst ( $rule ['table'] ) );
		$field = $rule ['field'];
		$res = $Model->where ( $rule ['condition'] )->setField ( $field, array (
				'exp',
				$rule ['rule'] 
		) );
		
		if (! $res) {
			$return = false;
		}
	}
	return $return;
}

// 基于数组创建目录和文件
function create_dir_or_files($files) {
	foreach ( $files as $key => $value ) {
		if (substr ( $value, - 1 ) == '/') {
			mkdir ( $value );
		} else {
			@file_put_contents ( $value, '' );
		}
	}
}

if (! function_exists ( 'array_column' )) {
	function array_column(array $input, $columnKey, $indexKey = null) {
		$result = array ();
		if (null === $indexKey) {
			if (null === $columnKey) {
				$result = array_values ( $input );
			} else {
				foreach ( $input as $row ) {
					$result [] = $row [$columnKey];
				}
			}
		} else {
			if (null === $columnKey) {
				foreach ( $input as $row ) {
					$result [$row [$indexKey]] = $row;
				}
			} else {
				foreach ( $input as $row ) {
					$result [$row [$indexKey]] = $row [$columnKey];
				}
			}
		}
		return $result;
	}
}

/**
 * 获取表名（不含表前缀）
 *
 * @param string $model_id        	
 * @return string 表名
 * @author huajie <banhuajie@163.com>
 */
function get_table_name($model_id = null) {
	if (empty ( $model_id )) {
		return false;
	}
	$Model = M ( 'Model' );
	$name = '';
	$info = $Model->getById ( $model_id );
	if ($info ['extend'] != 0) {
		$name = $Model->getFieldById ( $info ['extend'], 'name' ) . '_';
	}
	$name .= $info ['name'];
	return $name;
}

/**
 * 获取属性信息并缓存
 *
 * @param integer $id
 *        	属性ID
 * @param string $field
 *        	要获取的字段名
 * @return string 属性信息
 */
function get_model_attribute($model_id, $group = true) {
	static $list;
	
	/* 非法ID */
	if (empty ( $model_id ) || ! is_numeric ( $model_id )) {
		return '';
	}
	
	
	/* 获取属性 */
	if (! isset ( $list [$model_id] )) {
		$map = array (
				'model_id' => $model_id 
		);
		$extend = M ( 'Model' )->getFieldById ( $model_id, 'extend' );
		
		if ($extend) {
			$map = array (
					'model_id' => array (
							"in",
							array (
									$model_id,
									$extend 
							) 
					) 
			);
		}
		$info = M ( 'Attribute' )->where ( $map )->select ();
		$list [$model_id] = $info;
	}
	
	$attr = array ();
	foreach ( $list [$model_id] as $value ) {
		$attr [$value ['name']] = $value;
	}
	
	if ($group) {
		$sort = M ( 'Model' )->getFieldById ( $model_id, 'field_sort' );
		
		if (empty ( $sort )) { // 未排序
			$group = array (
					1 => array_merge ( $attr ) 
			);
		} else {
			$group = json_decode ( $sort, true );
			
			$keys = array_keys ( $group );
			foreach ( $group as &$value ) {
				foreach ( $value as $key => $val ) {
					$value [$key] = $attr [$val];
					unset ( $attr [$val] );
				}
			}
			
			if (! empty ( $attr )) {
				$group [$keys [0]] = array_merge ( $group [$keys [0]], $attr );
			}
		}
		$attr = $group;
	}
	return $attr;
}

/**
 * 调用系统的API接口方法（静态方法）
 * api('User/getName','id=5'); 调用公共模块的User接口的getName方法
 * api('Admin/User/getName','id=5'); 调用Admin模块的User接口
 *
 * @param string $name
 *        	格式 [模块名]/接口名/方法名
 * @param array|string $vars
 *        	参数
 */
function api($name, $vars = array()) {
	$array = explode ( '/', $name );
	$method = array_pop ( $array );
	$classname = array_pop ( $array );
	$module = $array ? array_pop ( $array ) : 'Common';
	$callback = $module . '\\Api\\' . $classname . 'Api::' . $method;
	if (is_string ( $vars )) {
		parse_str ( $vars, $vars );
	}
	return call_user_func_array ( $callback, $vars );
}

/**
 * 根据条件字段获取指定表的数据
 *
 * @param mixed $value
 *        	条件，可用常量或者数组
 * @param string $condition
 *        	条件字段
 * @param string $field
 *        	需要返回的字段，不传则返回整个数据
 * @param string $table
 *        	需要查询的表
 * @author huajie <banhuajie@163.com>
 */
function get_table_field($value = null, $condition = 'id', $field = null, $table = null) {
	if (empty ( $value ) || empty ( $table )) {
		return false;
	}
	
	// 拼接参数
	$map [$condition] = $value;
	$info = M ( ucfirst ( $table ) )->where ( $map );
	if (empty ( $field )) {
		$info = $info->field ( true )->find ();
	} else {
		$info = $info->getField ( $field );
	}
	return $info;
}

/**
 * 获取链接信息
 *
 * @param int $link_id        	
 * @param string $field        	
 * @return 完整的链接信息或者某一字段
 * @author huajie <banhuajie@163.com>
 */
function get_link($link_id = null, $field = 'url') {
	$link = '';
	if (empty ( $link_id )) {
		return $link;
	}
	$link = M ( 'Url' )->getById ( $link_id );
	if (empty ( $field )) {
		return $link;
	} else {
		return $link [$field];
	}
}

/**
 * 获取文档封面图片
 *
 * @param int $cover_id        	
 * @param string $field        	
 * @return 完整的数据 或者 指定的$field字段值
 * @author huajie <banhuajie@163.com>
 */
function get_cover($cover_id, $field = null) {
	if (empty ( $cover_id ))
		return false;
	
	$map ['status'] = 1;
	$picture = M ( 'Picture' )->where ( $map )->getById ( $cover_id );
	
	if (empty ( $picture ))
		return '';
	
	return empty ( $field ) ? $picture : $picture [$field];
}
function get_cover_url($cover_id) {
	$url = get_cover ( $cover_id, 'path' );
	if (empty ( $url ))
		return '';

	//return SITE_URL . $url;
	if (strstr($url, 'http://') or strstr($url, 'https://')) {
		return $url;
	} else {
		return SITE_URL . $url;
	}
}
// 兼容旧方法
function get_picture_url($cover_id) {
	return get_cover_url ( $cover_id );
}
function get_img_html($cover_id) {
	$url = get_cover_url ( $cover_id );
	
	if (empty ( $url ))
		return '';
	
	return '<img class="list_img" src="' . $url . '" >';
}
/**
 * 检查$pos(推荐位的值)是否包含指定推荐位$contain
 *
 * @param number $pos
 *        	推荐位的值
 * @param number $contain
 *        	指定推荐位
 * @return boolean true 包含 ， false 不包含
 * @author huajie <banhuajie@163.com>
 */
function check_document_position($pos = 0, $contain = 0) {
	if (empty ( $pos ) || empty ( $contain )) {
		return false;
	}
	
	// 将两个参数进行按位与运算，不为0则表示$contain属于$pos
	$res = $pos & $contain;
	if ($res !== 0) {
		return true;
	} else {
		return false;
	}
}

/**
 * 获取数据的所有子孙数据的id值
 *
 * @author 朱亚杰 <xcoolcc@gmail.com>
 */
function get_stemma($pids, Model &$model, $field = 'id') {
	$collection = array ();
	
	// 非空判断
	if (empty ( $pids )) {
		return $collection;
	}
	
	if (is_array ( $pids )) {
		$pids = trim ( implode ( ',', $pids ), ',' );
	}
	$result = $model->field ( $field )->where ( array (
			'pid' => array (
					'IN',
					( string ) $pids 
			) 
	) )->select ();
	$child_ids = array_column ( ( array ) $result, 'id' );
	
	while ( ! empty ( $child_ids ) ) {
		$collection = array_merge ( $collection, $result );
		$result = $model->field ( $field )->where ( array (
				'pid' => array (
						'IN',
						$child_ids 
				) 
		) )->select ();
		$child_ids = array_column ( ( array ) $result, 'id' );
	}
	return $collection;
}

/**
 * 判断关键词是否唯一
 *
 * @author weiphp
 */
function keyword_unique($keyword) {
	if (empty ( $keyword ))
		return false;
	
	$map ['keyword'] = $keyword;
	$info = M ( 'keyword' )->where ( $map )->find ();
	return empty ( $info );
}
// 分析枚举类型配置值 格式 a:名称1,b:名称2
// weiphp 该函数是从admin的function的文件里提取这到里
function parse_config_attr($string) {
	$array = preg_split ( '/[;\r\n]+/', trim ( $string, ",;\r\n" ) );
	if (strpos ( $string, ':' )) {
		$value = array ();
		foreach ( $array as $val ) {
			list ( $k, $v ) = explode ( ':', $val );
			$value [$k] = $v;
		}
	} else {
		$value = $array;
	}
	foreach ( $value as &$vo ) {
		$vo = clean_hide_attr ( $vo );
	}
	// dump($value);
	return $value;
}
function clean_hide_attr($str) {
	$arr = explode ( '|', $str );
	return $arr [0];
}
function get_hide_attr($str) {
	$arr = explode ( '|', $str );
	return $arr [1];
}
// 分析枚举类型字段值 格式 a:名称1,b:名称2
// 暂时和 parse_config_attr功能相同
// 但请不要互相使用，后期会调整
function parse_field_attr($string) {
	if (0 === strpos ( $string, ':' )) {
		// 采用函数定义
		return eval ( substr ( $string, 1 ) . ';' );
	}
	$array = preg_split ( '/[;\r\n]+/', trim ( $string, ",;\r\n" ) );
	// dump($array);
	if (strpos ( $string, ':' )) {
		$value = array ();
		foreach ( $array as $val ) {
			list ( $k, $v ) = explode ( ':', $val );
			empty ( $v ) && $v = $k;
			$k = clean_hide_attr ( $k );
			$value [$k] = $v;
		}
	} else {
		$value = $array;
	}
	// dump($value);
	return $value;
}
/* 解析列表定义规则 */
function get_list_field($data, $grid, $model) {
	// 获取当前字段数据
	foreach ( $grid ['field'] as $field ) {
		$array = explode ( '|', $field );
		$array [0] = trim ( $array [0] );
		$temp = $data [$array [0]];
		// 函数支持
		if (isset ( $array [1] )) {
			if ($array [1] == 'get_name_by_status') {
				$temp = get_name_by_status ( $temp, $array [0], $model ['id'] );
			} else {
				$temp = call_user_func ( $array [1], $temp );
			}
		}
		$data2 [$array [0]] = $temp;
	}
	if (! empty ( $grid ['format'] )) {
		$value = preg_replace_callback ( '/\[([a-z_]+)\]/', function ($match) use($data2) {
			return $data2 [$match [1]];
		}, $grid ['format'] );
	} else {
		$value = implode ( ' ', $data2 );
	}
	
	// 链接支持
	if (! empty ( $grid ['href'] )) {
		$links = explode ( ',', $grid ['href'] );
		foreach ( $links as $link ) {
			$array = explode ( '|', $link );
			$href = $array [0];
			if (preg_match ( '/^\[([a-z_]+)\]$/', $href, $matches )) {
				$val [] = $data2 [$matches [1]];
			} else {
				$show = isset ( $array [1] ) ? $array [1] : $value;
				// 增加跳转方式处理 weiphp
				$target = '_self';
				if (preg_match ( '/target=(\w+)/', $href, $matches )) {
					$target = $matches [1];
					$href = str_replace ( '&' . $matches [0], '', $href );
				}
				
				// 替换系统特殊字符串
				$href = str_replace ( array (
						'[DELETE]',
						'[EDIT]',
						'[MODEL]' 
				), array (
						'del?id=[id]&model=[MODEL]',
						'edit?id=[id]&model=[MODEL]',
						$model ['id'] 
				), $href );
				
				// 替换数据变量
				$href = preg_replace_callback ( '/\[([a-z_]+)\]/', function ($match) use($data) {
					return $data [$match [1]];
				}, $href );
				
				// 兼容多种写法
				if (strpos ( $href, '?' ) === false && strpos ( $href, '&' ) !== false) {
					$href = preg_replace ( "/&/i", "?", $href, 1 );
				}
				if ($show == '删除') {
					$val [] = '<a class="confirm"   href="' . urldecode ( U ( $href ) ) . '">' . $show . '</a>';
				} else {
					$val [] = '<a  target="' . $target . '" href="' . urldecode ( U ( $href ) ) . '">' . $show . '</a>';
				}
			}
		}
		$value = implode ( ' ', $val );
	}
	return $value;
}
/**
 * 获取状态值对应的标题
 *
 * @author weiphp
 */
function get_name_by_status($val, $name, $model_id) {
	static $_name = array ();
	if (! isset ( $_name [$model_id] )) {
		$_name [$model_id] = array ();
		$map ['extra'] = array (
				'EXP',
				'!=""' 
		);
		$map ['model_id'] = $model_id;
		$list = M ( 'attribute' )->where ( $map )->select ();
		foreach ( $list as $attr ) {
			if (empty ( $attr ['extra'] ))
				continue;
			
			$extra = parse_config_attr ( $attr ['extra'] );
			if (is_array ( $extra ) && ! empty ( $extra )) {
				$_name [$model_id] [$attr ['name']] ['value'] = $extra;
				$_name [$model_id] [$attr ['name']] ['type'] = $attr ['type'];
			}
		}
	}
	
	if ($_name [$model_id] [$name] ['type'] == 'checkbox') {
		$valArr = explode ( ',', $val );
		foreach ( $valArr as $v ) {
			$res [] = empty ( $_name [$model_id] [$name] ['value'] [$v] ) ? $v : $_name [$model_id] [$name] ['value'] [$v];
		}
		
		return implode ( ', ', $res );
	} else {
		return empty ( $_name [$model_id] [$name] ['value'] [$val] ) ? $val : $_name [$model_id] [$name] ['value'] [$val];
	}
}
function addWeixinLog($data, $data_post = '') {
	$log ['cTime'] = time ();
	$log ['cTime_format'] = date ( 'Y-m-d H:i:s', $log ['cTime'] );
	$log ['data'] = is_array ( $data ) ? serialize ( $data ) : $data;
	$log ['data_post'] = $data_post;
	M ( 'weixin_log' )->add ( $log );
}
/**
 * 取一个二维数组中的每个数组的固定的键知道的值来形成一个新的一维数组
 *
 * @param $pArray 一个二维数组        	
 * @param $pKey 数组的键的名称        	
 * @return 返回新的一维数组
 */
function getSubByKey($pArray, $pKey = "", $pCondition = "") {
	$result = array ();
	if (is_array ( $pArray )) {
		foreach ( $pArray as $temp_array ) {
			if (is_object ( $temp_array )) {
				$temp_array = ( array ) $temp_array;
			}
			if (("" != $pCondition && $temp_array [$pCondition [0]] == $pCondition [1]) || "" == $pCondition) {
				$result [] = ("" == $pKey) ? $temp_array : isset ( $temp_array [$pKey] ) ? $temp_array [$pKey] : "";
			}
		}
		return $result;
	} else {
		return false;
	}
}
// 判断是否是在微信浏览器里
function isWeixinBrowser() {
	$agent = $_SERVER ['HTTP_USER_AGENT'];
	if (! strpos ( $agent, "icroMessenger" )) {
		return false;
	}
	return true;
}
// php获取当前访问的完整url地址
function GetCurUrl() {
	$url = 'http://';
	if (isset ( $_SERVER ['HTTPS'] ) && $_SERVER ['HTTPS'] == 'on') {
		$url = 'https://';
	}
	if ($_SERVER ['SERVER_PORT'] != '80') {
		$url .= $_SERVER ['HTTP_HOST'] . ':' . $_SERVER ['SERVER_PORT'] . $_SERVER ['REQUEST_URI'];
	} else {
		$url .= $_SERVER ['HTTP_HOST'] . $_SERVER ['REQUEST_URI'];
	}
	// 兼容后面的参数组装
	if (stripos ( $url, '?' ) === false) {
		$url .= '?t=' . time ();
	}
	return $url;
}
// 获取当前用户的OpenId
function get_openid($openid = NULL) {
	if ($openid !== NULL) {
		session ( 'openid', $openid );
	} elseif (! empty ( $_REQUEST ['openid'] )) {
		session ( 'openid', $_REQUEST ['openid'] );
	}
	$openid = session ( 'openid' );
	
	$isWeixinBrowser = isWeixinBrowser ();
	if (empty ( $openid ) && $isWeixinBrowser) {
		$callback = GetCurUrl ();
		OAuthWeixin ( $callback );
	}
	
	if (empty ( $openid )) {
		return - 1;
	}
	
	return $openid;
}
// 获取当前用户的Token
function get_token($token = NULL) {
	if ($token !== NULL) {
		session ( 'token', $token );
	} elseif (! empty ( $_REQUEST ['token'] )) {
		session ( 'token', $_REQUEST ['token'] );
	}
	$token = session ( 'token' );
	
	if (empty ( $token )) {
		return - 1;
	}
	
	return $token;
}
// 获取当前用户的UID,方便在模型里的自动填充功能使用
function get_mid() {
	return session ( 'mid' );
}
// 通过openid获取微信用户基本信息,此功能只有认证的服务号才能用
function getWeixinUserInfo($openid, $token) {
	$access_token = get_access_token ( $token );
	if (empty ( $access_token )) {
		return false;
	}
	
	$param2 ['access_token'] = $access_token;
	$param2 ['openid'] = $openid;
	$param2 ['lang'] = 'zh_CN';
	
	$url = 'https://api.weixin.qq.com/cgi-bin/user/info?' . http_build_query ( $param2 );
	$content = file_get_contents ( $url );
	$content = json_decode ( $content, true );
	return $content;
}
// 获取公众号的信息
function get_token_appinfo($token = '') {
	empty ( $token ) && $token = get_token ();
	$map ['token'] = $token;
	$info = M ( 'member_public' )->where ( $map )->find ();
	return $info;
}
// 判断公众号的类型：是订阅号还是服务号
function get_token_type($token = '') {
	$info = get_token_appinfo ( $token );
	return intval ( $info ['type'] );
}
// 获取access_token，自动带缓存功能
function get_access_token($token = '') {
	empty ( $token ) && $token = get_token ();
	$key = 'access_token_' . $token;
	$res = S ( $key );
	if ($res !== false)
		return $res;
	
	$info = get_token_appinfo ( $token );
	if (empty ( $info ['appid'] ) || empty ( $info ['secret'] )) {
		S ( $key, 0, 7200 );
		return 0;
	}
	
	$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $info ['appid'] . '&secret=' . $info ['secret'];
	$tempArr = json_decode ( file_get_contents ( $url ), true );
	if (@array_key_exists ( 'access_token', $tempArr )) {
		S ( $key, $tempArr ['access_token'], 7200 );
		return $tempArr ['access_token'];
	} else {
		return 0;
	}
}
function OAuthWeixin($callback) {
	$isWeixinBrowser = isWeixinBrowser ();
	$info = get_token_appinfo ();
	if (! $isWeixinBrowser || empty ( $info ['appid'] )) {
		redirect ( $callback . '&openid=-1' );
	}
	$param ['appid'] = $info ['appid'];
	
	if (! isset ( $_GET ['getOpenId'] )) {
		$param ['redirect_uri'] = $callback . '&getOpenId=1';
		$param ['response_type'] = 'code';
		$param ['scope'] = 'snsapi_base';
		$param ['state'] = 123;
		$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?' . http_build_query ( $param ) . '#wechat_redirect';
		redirect ( $url );
	} elseif ($_GET ['state']) {
		$param ['secret'] = $info ['secret'];
		$param ['code'] = I ( 'code' );
		$param ['grant_type'] = 'authorization_code';
		
		$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?' . http_build_query ( $param );
		$content = file_get_contents ( $url );
		$content = json_decode ( $content, true );
		redirect ( $callback . '&openid=' . $content ['openid'] );
	}
}
/**
 * 执行SQL文件
 */
function execute_sql_file($sql_path) {
	// 读取SQL文件
	$sql = file_get_contents ( $sql_path );
	$sql = str_replace ( "\r", "\n", $sql );
	$sql = explode ( ";\n", $sql );
	
	// 替换表前缀
	$orginal = 'wp_';
	$prefix = C ( 'DB_PREFIX' );
	$sql = str_replace ( "{$orginal}", "{$prefix}", $sql );
	
	// 开始安装
	foreach ( $sql as $value ) {
		$value = trim ( $value );
		if (empty ( $value ))
			continue;
		
		$res = M ()->execute ( $value );
		// dump($res);
		// dump(M()->getLastSql());
	}
}
// 设置微信关联聊天中用到的用户状态
function set_user_status($addon, $keywordArr = array()) {
	// 设置用户状态
	$user_status ['addon'] = $addon;
	$user_status ['keywordArr'] = $keywordArr;
	
	$openid = get_openid ();
	return S ( 'user_status_' . $openid, $user_status );
}

// 获取公众号等级名
function get_public_group_name($group_id) {
	static $_public_group_name;
	
	$group_id = intval ( $group_id );
	if (! isset ( $_public_group_name [$group_id] )) {
		$group_list = M ( 'member_public_group' )->field ( 'id, title' )->select ();
		foreach ( $group_list as $g ) {
			$_public_group_name [$g ['id']] = $g ['title'];
		}
		$_public_group_name [0] = '无';
	}
	
	return $_public_group_name [$group_id];
}

// 截取内容
function getShort($str, $length = 40, $ext = '') {
	$str = htmlspecialchars ( $str );
	$str = strip_tags ( $str );
	$str = htmlspecialchars_decode ( $str );
	$strlenth = 0;
	$out = '';
	preg_match_all ( "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/", $str, $match );
	foreach ( $match [0] as $v ) {
		preg_match ( "/[\xe0-\xef][\x80-\xbf]{2}/", $v, $matchs );
		if (! empty ( $matchs [0] )) {
			$strlenth += 1;
		} elseif (is_numeric ( $v )) {
			$strlenth += 0.5; // 字符字节长度比例 汉字为1
		} else {
			$strlenth += 0.5; // 字符字节长度比例 汉字为1
		}
		
		if ($strlenth > $length) {
			$output .= $ext;
			break;
		}
		
		$output .= $v;
	}
	return $output;
}

// 防超时的file_get_contents改造函数
function wp_file_get_contents($url) {
	$context = stream_context_create ( array (
			'http' => array (
					'timeout' => 30  // 超时时间，单位为秒
						) 
	) );
	
	return file_get_contents ( $url, 0, $context );
}

// 全局的安全过滤函数
function safe($text, $type = 'html') {
	// 无标签格式
	$text_tags = '';
	// 只保留链接
	$link_tags = '<a>';
	// 只保留图片
	$image_tags = '<img>';
	// 只存在字体样式
	$font_tags = '<i><b><u><s><em><strong><font><big><small><sup><sub><bdo><h1><h2><h3><h4><h5><h6>';
	// 标题摘要基本格式
	$base_tags = $font_tags . '<p><br><hr><a><img><map><area><pre><code><q><blockquote><acronym><cite><ins><del><center><strike>';
	// 兼容Form格式
	$form_tags = $base_tags . '<form><input><textarea><button><select><optgroup><option><label><fieldset><legend>';
	// 内容等允许HTML的格式
	$html_tags = $base_tags . '<meta><ul><ol><li><dl><dd><dt><table><caption><td><th><tr><thead><tbody><tfoot><col><colgroup><div><span><object><embed><param>';
	// 全HTML格式
	$all_tags = $form_tags . $html_tags . '<!DOCTYPE><html><head><title><body><base><basefont><script><noscript><applet><object><param><style><frame><frameset><noframes><iframe>';
	// 过滤标签
	$text = html_entity_decode ( $text, ENT_QUOTES, 'UTF-8' );
	$text = strip_tags ( $text, ${$type . '_tags'} );
	
	// 过滤攻击代码
	if ($type != 'all') {
		// 过滤危险的属性，如：过滤on事件lang js
		while ( preg_match ( '/(<[^><]+)(ondblclick|onclick|onload|onerror|unload|onmouseover|onmouseup|onmouseout|onmousedown|onkeydown|onkeypress|onkeyup|onblur|onchange|onfocus|background|codebase|dynsrc|lowsrc)([^><]*)/i', $text, $mat ) ) {
			$text = str_ireplace ( $mat [0], $mat [1] . $mat [3], $text );
		}
		while ( preg_match ( '/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i', $text, $mat ) ) {
			$text = str_ireplace ( $mat [0], $mat [1] . $mat [3], $text );
		}
	}
	return $text;
}
// 创建多级目录
function mkdirs($dir) {
	if (! is_dir ( $dir )) {
		if (! mkdirs ( dirname ( $dir ) )) {
			return false;
		}
		if (! mkdir ( $dir, 0777 )) {
			return false;
		}
	}
	return true;
}
// 组装查询条件
function getIdsForMap($ids, $map = array(), $field = 'id') {
	$ids = safe ( $ids );
	$ids = preg_split ( '/[\s,;]+/', $ids ); // 支持以空格tab逗号分号分割ID
	$ids = array_filter ( $ids );
	if (empty ( $ids ))
		return $map;
	
	$map [$field] = array (
			'in',
			$ids 
	);
	
	return $map;
}
// 获取通用分类级联菜单的标题，改方法仅支持级联的数据源配置成数据表common_category的情况，其它情况需要使用下面的getCascadeTitle方法
function getCommonCategoryTitle($ids) {
	$extra = 'type=db&table=common_category';
	
	return getCascadeTitle ( $ids, $extra );
}
// 获取级联菜单的标题的通用处理方法
function getCascadeTitle($ids, $extra) {
	$idArr = explode ( ',', $ids );
	$idArr = array_filter ( $idArr );
	if (empty ( $idArr ))
		return '';
	
	parse_str ( $extra, $arr );
	if ($arr ['type'] == 'db') {
		$table = $arr ['table'];
		unset ( $arr ['type'], $arr ['table'] );
		
		$arr ['token'] = get_token ();
		$arr ['id'] = array (
				'in',
				$idArr 
		);
		$list = M ( $table )->where ( $arr )->field ( 'title' )->select ();
		$titleArr = getSubByKey ( $list, 'title' );
	} else {
		$str = str_replace ( '，', ',', $extra );
		$str = str_replace ( '【', '[', $str );
		$str = str_replace ( '】', ']', $str );
		$str = str_replace ( '：', ':', $str );
		
		$arr = StringToArray ( $str );
		$str = '';
		foreach ( $arr as $v ) {
			if ($v == '[' || $v == ']' || $v == ',') {
				if ($str) {
					$block = explode ( ':', trim ( $str ) );
					if (in_array ( $block [0], $idArr )) {
						$titleArr [] = isset ( $block [1] ) ? $block [1] : $block [0];
					}
				}
				$str = '';
			} else {
				$str .= $v;
			}
		}
	}
	return implode ( ' > ', $titleArr );
}
// 把字符串转成数组，支持汉字，只能是utf-8格式的
function StringToArray($str) {
	$result = array ();
	$len = strlen ( $str );
	$i = 0;
	while ( $i < $len ) {
		$chr = ord ( $str [$i] );
		if ($chr == 9 || $chr == 10 || (32 <= $chr && $chr <= 126)) {
			$result [] = substr ( $str, $i, 1 );
			$i += 1;
		} elseif (192 <= $chr && $chr <= 223) {
			$result [] = substr ( $str, $i, 2 );
			$i += 2;
		} elseif (224 <= $chr && $chr <= 239) {
			$result [] = substr ( $str, $i, 3 );
			$i += 3;
		} elseif (240 <= $chr && $chr <= 247) {
			$result [] = substr ( $str, $i, 4 );
			$i += 4;
		} elseif (248 <= $chr && $chr <= 251) {
			$result [] = substr ( $str, $i, 5 );
			$i += 5;
		} elseif (252 <= $chr && $chr <= 253) {
			$result [] = substr ( $str, $i, 6 );
			$i += 6;
		}
	}
	return $result;
}
/**
 * 增加用户积分函数
 *
 * @param string $name
 *        	积分标识名
 * @param int $lock_time
 *        	解锁时间，即多长时间内才能重复加积分，为0时不作控制
 * @param array $credit
 *        	自定义积分值，格式：array('score'=>财富值,'experience'=>经验值);为空时默认取管理中心积分管理里的配置值
 * @param int $admin_uid
 *        	管理员UID，用于管理员给用户手工加积分时的场景
 */
function add_credit($name, $lock_time = 5, $credit = array(), $admin_uid = 0) {
	if (empty ( $name ))
		return false;
	
	if ($lock_time > 0) {
		$key = 'credit_lock_' . get_token () . '_' . get_openid () . '_' . $name;
		if (S ( $key ))
			return false;
		
		S ( $key, 1, $lock_time );
	}
	
	$data ['credit_name'] = $name;
	$data ['admin_uid'] = $admin_uid;
	$data = array_merge ( $data, $credit );
	$credit = D ( 'Common/Credit' )->addCredit ( $data );
}
// 判断用户最大可创建的公众号数
function getPublicMax($uid) {
	$map ['uid'] = $uid;
	$public_count = M ( 'member' )->where ( $map )->getField ( 'public_count' );
	if ($public_count === NULL) {
		$public_count = C ( 'DEFAULT_PUBLIC_CREATE_MAX_NUMB' );
	}
	return intval ( $public_count );
}
function diyPage($keyword) {
	$map ['keyword'] = $keyword;
	$map ['token'] = get_token ();
	$page = M ( 'diy' )->where ( $map )->find ();
	
	if (! $page) {
		$map ['token'] = '0';
		$page = M ( 'diy' )->where ( $map )->find ();
	}
	// dump($page);
	if (! $page) {
		return false;
	}
	
	$model = A ( 'Addons://Diy/Diy' );
	// dump($model);exit;
	$model->show ( $page ['id'] );
}
// 各插件获取关联抽奖活动的地址 暂只支持刮刮卡
function event_url($addon_title, $id = '0') {
	$map ['token'] = get_token ();
	$map ['addon_condition'] = array (
			'exp',
			"='[{$addon_title}:*]' or addon_condition='[{$addon_title}:{$id}]'" 
	);
	
	$event = M ( 'Scratch' )->where ( $map )->order ( 'id desc' )->find ();
	$event_url = '';
	if ($event) {
		$param ['token'] = get_token ();
		$param ['openid'] = get_openid ();
		$param ['id'] = $event ['id'];
		$event_url = addons_url ( 'Scratch://Scratch/show', $param );
	}
	return $event_url;
}
// 抽奖或者优惠券领取的插件条件判断
function addon_condition_check($addon_condition) {
	preg_match_all ( "/\[([\s\S]*):([\*,\d]*)\]/i", $addon_condition, $match );
	if (empty ( $match [1] [0] ) || empty ( $match [2] [0] )) {
		return true;
	}
	
	$conditon ['token'] = get_token ();
	$conditon ['uid'] = get_mid ();
	switch ($match [1] [0]) {
		case '投票' :
			$match [2] [0] != '*' && $match [2] [0] > 0 && $conditon ['vote_id'] = $match [2] [0];
			$conditon ['user_id'] = get_mid ();
			unset ( $conditon ['uid'] );
			$res = M ( 'vote_log' )->where ( $conditon )->find ();
			break;
		case '通用表单' :
			$match [2] [0] != '*' && $match [2] [0] > 0 && $conditon ['forms_id'] = $match [2] [0];
			$res = M ( 'forms_value' )->where ( $conditon )->find ();
			break;
		case '微考试' :
			$match [2] [0] != '*' && $match [2] [0] > 0 && $conditon ['exam_id'] = $match [2] [0];
			$res = M ( 'exam_answer' )->where ( $conditon )->find ();
			break;
		case '微测试' :
			$match [2] [0] != '*' && $match [2] [0] > 0 && $conditon ['test_id'] = $match [2] [0];
			$res = M ( 'test_answer' )->where ( $conditon )->find ();
			break;
		case '微调研' :
			$match [2] [0] != '*' && $match [2] [0] > 0 && $conditon ['survey_id'] = $match [2] [0];
			$res = M ( 'survey_answer' )->where ( $conditon )->find ();
			break;
		default :
			$match [2] [0] != '*' && $match [2] [0] > 0 && $conditon ['id'] = $match [2] [0];
			$res = M ( $match [1] [0] )->where ( $conditon )->find ();
	}
	// dump ( $res );
	// dump ( M ()->getLastSql () );
	
	return ! empty ( $res );
}
// 抽奖或者优惠券领取的插件条件提示
function condition_tips($addon_condition) {
	if (empty ( $addon_condition ))
		return '';
	
	preg_match_all ( "/\[([\s\S]*):([\*,\d]*)\]/i", $addon_condition, $match );
	if (empty ( $match [1] [0] ) || empty ( $match [2] [0] )) {
		return '';
	}
	
	$conditon ['token'] = get_token ();
	$conditon ['id'] = $match [2] [0];
	$title = '';
	$has_title = $conditon ['id'] != '*' && $conditon ['id'] > 0;
	
	switch ($match [1] [0]) {
		case '投票' :
			$has_title && $title = M ( 'vote' )->where ( $conditon )->getField ( 'title' );
			break;
		case '通用表单' :
			$has_title && $title = M ( 'forms' )->where ( $conditon )->getField ( 'title' );
			break;
		case '微考试' :
			$has_title && $title = M ( 'exam' )->where ( $conditon )->getField ( 'title' );
			break;
		case '微测试' :
			$has_title && $title = M ( 'test' )->where ( $conditon )->getField ( 'title' );
			break;
		case '微调研' :
			$has_title && $title = M ( 'survey' )->where ( $conditon )->getField ( 'title' );
			break;
		default :
			$has_title && $title = M ( $match [1] [0] )->where ( $conditon )->getField ( 'title' );
	}
	$result = '需要参与' . $title . $match [1] [0] . '后才能领取';
	
	return $result;
}
function lastsql() {
	dump ( M ()->getLastSql () );
}
// 商业代码解密
function code_decode($text) {
	$key = substr ( C ( 'WEIPHP_STORE_LICENSE' ), 0, 5 );
	return think_decrypt ( $text, $key );
}
function outExcel($dataArr, $fileName = '', $sheet = false) {
	require_once VENDOR_PATH . 'download-xlsx.php';
	export_csv ( $dataArr, $fileName, $sheet );
	unset ( $sheet );
	unset ( $dataArr );
}
// 获取通用分类表的分类标题
function category_title($cate_id) {
	static $_category_title = array ();
	if (isset ( $_category_title [$cate_id] )) {
		return $_category_title [$cate_id];
	}
	
	$map ['token'] = get_token ();
	$list = M ( 'common_category' )->where ( $map )->field ( 'id,title' )->select ();
	foreach ( $list as $v ) {
		$_category_title [$v ['id']] = $v ['title'];
	}
	if (! isset ( $_category_title [$cate_id] )) {
		$_category_title [$cate_id] = '';
	}
	return $_category_title [$cate_id];
}
function get_lecturer_name($lecturer_id) {
	static $_lecturer_name = array ();
	if (isset ( $_lecturer_name [$lecturer_id] )) {
		return $_lecturer_name [$lecturer_id];
	}
	
	$map ['token'] = get_token ();
	$list = M ( 'classes_lecturer' )->where ( $map )->field ( 'id,name' )->select ();
	foreach ( $list as $v ) {
		$_lecturer_name [$v ['id']] = $v ['name'];
	}
	if (! isset ( $_lecturer_name [$lecturer_id] )) {
		$_lecturer_name [$lecturer_id] = '';
	}
	return $_lecturer_name [$lecturer_id];
}
function check_token_purview($table, $id, $field = 'token') {
	$token = get_token ();
	$map ['id'] = $id;
	$info = M ( $table )->where ( $map )->field ( $field )->find ();
	if ($info === false || $info [$field] == $token)
		return true; // 没有这个字段或者没有这个记录直接返回
	
	exit ( '非法访问' );
}
// weiphp专用分割函数，同时支持常见的按空格、逗号、分号、换行进行分割
function wp_explode($string, $delimiter = "\s,;\r\n") {
	if (empty ( $string ))
		return array ();
		
		// 转换中文符号
	$string = iconv ( 'utf-8', 'gbk', $string );
	$string = preg_replace ( '/\xa3([\xa1-\xfe])/e', 'chr(ord(\1)-0x80)', $string );
	$string = iconv ( 'gbk', 'utf-8', $string );
	
	$arr = preg_split ( '/[' . $delimiter . ']+/', $string );
	return array_unique ( array_filter ( $arr ) );
}
function get_code_img($qr_code) {
	if (! $qr_code)
		return '';
	
	$html = '<img src="' . $qr_code . '" width="50" height="50" />';
	return $html;
}
function get_file_title($attach_ids) {
	$ids = wp_explode ( $attach_ids );
	if (empty ( $ids ))
		return '';
	
	$map ['id'] = array (
			'in',
			$ids 
	);
	$names = M ( 'file' )->where ( $map )->getFields ( 'name' );
	
	return implode ( ', ', $names );
}
// 阿拉伯数字转中文表述，如101转成一百零一
function num2cn($number) {
	$number = intval ( $number );
	$capnum = array (
			"零",
			"一",
			"二",
			"三",
			"四",
			"五",
			"六",
			"七",
			"八",
			"九" 
	);
	$capdigit = array (
			"",
			"十",
			"百",
			"千",
			"万" 
	);
	
	$data_arr = str_split ( $number );
	$count = count ( $data_arr );
	for($i = 0; $i < $count; $i ++) {
		$d = $capnum [$data_arr [$i]];
		$arr [] = $d != '零' ? $d . $capdigit [$count - $i - 1] : $d;
	}
	$cncap = implode ( "", $arr );
	
	$cncap = preg_replace ( "/(零)+/", "0", $cncap ); // 合并连续“零”
	$cncap = trim ( $cncap, '0' );
	$cncap = str_replace ( "0", "零", $cncap ); // 合并连续“零”
	$cncap == '一十' && $cncap = '十';
	$cncap == '' && $cncap = '零';
	// echo ( $data.' : '.$cncap.' <br/>' );
	return $cncap;
}

/**
 * select返回的数组进行整数映射转换
 *
 * @param array $map
 *        	映射关系二维数组 array(
 *        	'字段名1'=>array(映射关系数组),
 *        	'字段名2'=>array(映射关系数组),
 *        	......
 *        	)
 * @author 朱亚杰 <zhuyajie@topthink.net>
 * @return array array(
 *         array('id'=>1,'title'=>'标题','status'=>'1','status_text'=>'正常')
 *         ....
 *         )
 *        
 */
function int_to_string(&$data, $map = array('status'=>array(1=>'正常',-1=>'删除',0=>'禁用',2=>'未审核',3=>'草稿'))) {
	if ($data === false || $data === null) {
		return $data;
	}
	$data = ( array ) $data;
	foreach ( $data as $key => $row ) {
		foreach ( $map as $col => $pair ) {
			if (isset ( $row [$col] ) && isset ( $pair [$row [$col]] )) {
				$data [$key] [$col . '_text'] = $pair [$row [$col]];
			}
		}
	}
	return $data;
}
function importFormExcel($attach_id, $column) {
	$res = array (
			'status' => 0,
			'data' => '' 
	);
	if (empty ( $attach_id ) || ! is_numeric ( $attach_id )) {
		$res ['data'] = '上传文件ID无效！';
		return $res;
	}
	$file = M ( 'file' )->where ( 'id=' . $attach_id )->find ();
	$root = C ( 'DOWNLOAD_UPLOAD.rootPath' );
	$filename = SITE_PATH . '/Uploads/Download/' . $file ['savepath'] . $file ['savename'];
	if (! file_exists ( $filename )) {
		$res ['data'] = '上传的文件失败';
		return $res;
	}
	$extend = $file ['ext'];
	if (! ($extend == 'xls' || $extend == 'xlsx')) {
		$res ['data'] = '文件格式不对，请上传xls,xlsx格式的文件';
		return $res;
	}
	
	vendor ( 'PHPExcel' );
	vendor ( 'PHPExcel.PHPExcel_IOFactory' );
	vendor ( 'PHPExcel.Reader.Excel5' );
	
	$format = strtolower ( $extend ) == 'xls' ? 'Excel5' : 'excel2007';
	$objReader = \PHPExcel_IOFactory::createReader ( $format );
	$objPHPExcel = $objReader->load ( $filename );
	$objPHPExcel->setActiveSheetIndex ( 0 );
	$sheet = $objPHPExcel->getSheet ( 0 );
	$highestRow = $sheet->getHighestRow (); // 取得总行数
	
	for($j = 2; $j <= $highestRow; $j ++) {
		$addData = array ();
		foreach ( $column as $k => $v ) {
			$addData [$v] = trim ( ( string ) $objPHPExcel->getActiveSheet ()->getCell ( $k . $j )->getValue () );
		}
		
		$isempty = true;
		foreach ( $column as $v ) {
			$isempty && $isempty = empty ( $addData [$v] );
		}
		
		if (! $isempty)
			$result [$j] = $addData;
	}
	
	$res ['status'] = 1;
	$res ['data'] = $result;
	return $res;
}
function showNewIcon($time, $day = 3) {
	$img = '';
	if (NOW_TIME < ($time + 86400 * $day)) {
		$img = '<img src="' . C ( 'TMPL_PARSE_STRING.__IMG__' ) . '/new.png"/>';
	}
	return $img;
}
function replace_url($content) {
	$param ['token'] = get_token ();
	$param ['openid'] = get_openid ();
	
	$sreach = array (
			'[follow]',
			'[website]',
			'[token]',
			'[openid]' 
	);
	$replace = array (
			addons_url ( 'UserCenter://UserCenter/bind', $param ),
			addons_url ( 'WeiSite://WeiSite/index', $param ),
			$param ['token'],
			$param ['openid'] 
	);
	$content = str_replace ( $sreach, $replace, $content );
	
	return $content;
}
/**
 * 验证分类是否允许发布内容
 * @param  integer $id 分类ID
 * @return boolean     true-允许发布内容，false-不允许发布内容
 */
function check_category($id){
    if (is_array($id)) {
        $type = get_category($id['category_id'], 'type');
        $type = explode(",", $type);
        return in_array($id['type'], $type);
    } else {
        $publish = get_category($id, 'allow_publish');
        return $publish ? true : false;
    }
}

/**
 * 检测分类是否绑定了指定模型
 * @param  array $info 模型ID和分类ID数组
 * @return boolean     true-绑定了模型，false-未绑定模型
 */
function check_category_model($info){
    $cate   =   get_category($info['category_id']);
    $array  =   explode(',', $info['pid'] ? $cate['model_sub'] : $cate['model']);
    return in_array($info['model_id'], $array);
}