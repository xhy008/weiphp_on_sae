<?php

namespace Home\Controller;

class TestController extends HomeController {
	function attr2() {
		$model_list = M ( 'model' )->field ( 'id,name' )->select ();
		foreach ( $model_list as $m ) {
			$mod [$m ['id']] = 'wp_' . $m ['name'];
		}
		
		$model_list = M ( 'model_copy' )->field ( 'id,name' )->select ();
		foreach ( $model_list as $m ) {
			$mod2 [$m ['id']] = 'wp_' . $m ['name'];
		}
		$list = M ( 'attribute_copy' )->field ( 'id,name,field,model_id' )->select ();
		foreach ( $list as $v ) {
			$table = $mod2 [$v ['model_id']];
			$name = $v ['name'];
			
			$att2 [$table] [$v ['id']] = $name;
		}
		
		$list = M ( 'attribute' )->field ( 'id,name,field,model_id' )->select ();
		foreach ( $list as $v ) {
			$table = $mod [$v ['model_id']];
			$name = $v ['name'];
			
			$att [$table] [$v ['id']] = $name;
		}
		
		foreach ( $att as $t => $a ) {
			$diff = array_diff ( $att2 [$t], $a );
			if ( !empty ( $diff )) {
				dump ( '===========' . $t . '===========' );
				dump ( $diff );
			}
		}
		
// 		dump ( $att );
// 		dump ( $att2 );
	}
	function attr() {
		$column_list = M ()->query ( "SELECT TABLE_NAME,COLUMN_NAME,COLUMN_TYPE,IS_NULLABLE FROM information_schema.`COLUMNS` WHERE TABLE_SCHEMA='update0825'" );
		foreach ( $column_list as $c ) {
			$null = $c ['IS_NULLABLE'] == 'NO' ? ' NOT NULL ' : ' NULL ';
			$col [$c ['TABLE_NAME']] [$c ['COLUMN_NAME']] = $c ['COLUMN_TYPE'] . $null;
		}
		// dump ( $col );
		
		$model_list = M ( 'model' )->field ( 'id,name' )->select ();
		foreach ( $model_list as $m ) {
			$mod [$m ['id']] = 'wp_' . $m ['name'];
		}
		// dump ( $mod );
		
		$list = M ( 'attribute' )->field ( 'id,name,field,model_id' )->select ();
		foreach ( $list as $v ) {
			$table = $mod [$v ['model_id']];
			$name = $v ['name'];
			
			if (trim ( $v ['field'] ) != trim ( $col [$table] [$name] )) {
				$save ['field'] = $col [$table] [$name];
				$res = M ( 'attribute' )->where ( 'id=' . $v ['id'] )->save ( $save );
				dump ( $res );
				lastsql ();
			}
		}
	}
	function token_replace() {
		$sql = "SELECT TABLE_NAME FROM information_schema.`COLUMNS` WHERE TABLE_SCHEMA='citic' AND COLUMN_NAME='token'";
		$list = M ()->query ( $sql );
		foreach ( $list as $vo ) {
			$table = $vo ['TABLE_NAME'];
			$sql = "UPDATE $table SET token='gh_a9f897b74c99' where token='gh_825cf61f1562'";
			$res = M ()->execute ( $sql );
			dump ( $sql );
			dump ( $res );
		}
	}
	function mylove() {
		// ALTER TABLE `wp_prize` ADD COLUMN `token` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Token' AFTER `img`;
		// ALTER TABLE `wp_sn_code` ADD COLUMN `token` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Token' AFTER `prize_title`;
		$_token = array ();
		$dao = M ( 'prize' );
		$list = $dao->select ();
		foreach ( $list as $v ) {
			$map ['id'] = $v ['id'];
			$key = $v ['addon'] . '_' . $v ['target_id'];
			if (isset ( $_token [$key] )) {
				$save ['token'] = $_token [$key];
			} else {
				$save ['token'] = $_token [$key] = M ( $v ['addon'] )->where ( 'id=' . $v ['target_id'] )->getField ( 'token' );
			}
			$res = $dao->where ( $map )->save ( $save );
			dump ( $res );
			lastsql ();
		}
		
		$dao = M ( 'sn_code' );
		$list = $dao->select ();
		foreach ( $list as $v ) {
			$map ['id'] = $v ['id'];
			$key = $v ['addon'] . '_' . $v ['target_id'];
			if (isset ( $_token [$key] )) {
				$save ['token'] = $_token [$key];
			} else {
				$save ['token'] = $_token [$key] = M ( $v ['addon'] )->where ( 'id=' . $v ['target_id'] )->getField ( 'token' );
			}
			$res = $dao->where ( $map )->save ( $save );
			dump ( $res );
			lastsql ();
		}
	}
	function jiamiFile() {
		// 取当前用户的网站信息
		$map ['uid'] = $this->mid;
		$info = M ( 'web_info' )->where ( $map )->find ();
		
		// 第一步：取文件内容
		$file = SITE_PATH . '/test.php';
		
		$get = trim ( file_get_contents ( $file ) );
		
		if ('<?php' == strtolower ( substr ( $get, 0, 5 ) )) {
			$get = substr ( $get, 5 );
		} else {
			$get = substr ( $get, 2 );
		}
		
		if ('?>' == substr ( $get, - 2 )) {
			$get = substr ( $get, 0, - 2 );
		}
		// 第二步：取加密的KEY
		$key = substr ( $info ['web_key'], 0, 5 );
		$license = substr ( $info ['web_key'], 5 );
		
		// 代码里插入判断授权的代码段
		$pre_code = <<<str
		\$ip = gethostbyname ( SITE_DOMAIN );
		\$fip = strtok ( \$ip, '.' );
		\$is_free = \$fip == '10' || \$fip == '127' || \$fip == '168' || \$fip == '192';
		if (! \$is_free) {
			\$license = C ( 'WEIPHP_STORE_LICENSE' );
			\$key = substr ( \$license, 0, 5 );
			\$license = substr ( \$license, 5 );			
			
			\$domain_str = md5 ( think_encrypt ( SITE_DOMAIN, \$key ) );
			\$ip_str = md5 ( think_encrypt ( \$ip, \$key ) );
			if (\$license != \$domain_str && \$license != \$ip_str) {
				header("Content-Type: text/html;charset=utf-8"); 
				echo '禁止访问未授权的应用';
				exit ();
			}
		}
str;
		$content = strip_whitespace ( $pre_code . $get );
		$content = think_encrypt ( $content, $key );
		$content = "<?php eval(code_decode('$content'));";
		dump ( $content );
		
		file_put_contents ( SITE_PATH . '/test2.php', $content );
	}
	function testFile() {
		require_once SITE_PATH . '/test2.php';
	}
}
