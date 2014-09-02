<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace Home\Controller;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class IndexController extends HomeController {
	
	// 系统首页
	public function index() {
		redirect ( U ( 'home/index/main' ) );
	}
	// 系统介绍
	public function introduction() {
		$this->display ();
	}
	// 系统下载
	public function download() {
		$this->display ();
	}
	// 系统帮助
	public function help() {
		if (isset ( $_GET ['public_id'] )) {
			$map ['id'] = intval ( $_GET ['public_id'] );
			$info = M ( 'member_public' )->where ( $map )->find ();
			$this->assign ( 'token', $info ['token'] );
		} else {
			$this->assign ( 'token', '你的公众号的Token' );
		}
		$this->display ();
	}
	// 系统关于
	public function about() {
		$this->display ();
	}
	// 授权协议
	public function license() {
		$this->display ();
	}
	// 下载weiphp
	public function downloadFile() {
		M ( 'config' )->where ( 'name="DOWNLOAD_COUNT"' )->setInc ( 'value' );
		redirect ( 'http://down.weiphp.cn/weiphp.zip' );
	}
	// 远程获取最新版本号
	public function update_version() {
		die ( M ( 'update_version' )->getField ( "max(`version`)" ) );
	}
	// 远程获取升级包信息
	public function update_json() {
		$old_version = intval ( $_REQUEST ['version'] );
		$new_version = M ( 'update_version' )->getField ( "max(`version`)" );
		
		$res = array ();
		if ($old_version < $new_version) {
			$res = M ( 'update_version' )->field ( 'version,title,description,create_date' )->where ( 'version>' . $old_version )->select ();
		}
		
		die ( json_encode ( $res ) );
	}
	// 下载升级包
	public function download_update_package() {
		$map ['version'] = intval ( $_REQUEST ['version'] );
		$package = M ( 'update_version' )->where ( $map )->getField ( 'package' );
		if (empty ( $package )) {
			$this->error ( '下载的文件不存在或已被移除' );
		}
		M ( 'update_version' )->where ( $map )->setInc ( 'download_count' );
		
		redirect ( $package );
	}
	public function main() {
		if (! is_login ()) {
			Cookie ( '__forward__', $_SERVER ['REQUEST_URI'] );
			$url = U ( 'home/user/login' );
			redirect ( $url );
		}
		$page = I ( 'p', 1, 'intval' );
		
		// 关键字搜索
		$map ['type'] = 1;
		$key = 'title';
		if (isset ( $_REQUEST [$key] )) {
			$map [$key] = array (
					'like',
					'%' . $_REQUEST [$key] . '%' 
			);
			unset ( $_REQUEST [$key] );
		}
		
		$row = 15;
		
		$data = M ( 'addons' )->where ( $map )->order ( 'id DESC' )->page ( $page, $row )->select ();
		$token_status = D ( 'Common/AddonStatus' )->getList ( true );
		
		foreach ( $data as $k => &$vo ) {
			if ($token_status [$vo ['name']] === '-1') {
				unset ( $data [$k] );
				// $vo ['status_title'] = '无权限';
				// $vo ['action'] = '';
				// $vo ['color'] = '#CCC';
				// $vo ['status'] = 0;
			} elseif ($token_status [$vo ['name']] === 0) {
				$vo ['status_title'] = '已禁用';
				$vo ['action'] = '启用';
				$vo ['color'] = '#CCC';
				$vo ['status'] = 0;
			} else {
				$vo ['status_title'] = '已启用';
				$vo ['action'] = '禁用';
				$vo ['color'] = '';
				$vo ['status'] = 1;
			}
		}
		
		/* 查询记录总数 */
		$count = M ( 'addons' )->where ( $map )->count ();
		
		// 分页
		if ($count > $row) {
			$page = new \Think\Page ( $count, $row );
			$page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
			$this->assign ( '_page', $page->show () );
		}
		
		$this->assign ( 'list_data', $data );
		
		$res ['title'] = '插件管理';
		$res ['url'] = U ( 'main' );
		$res ['class'] = 'current';
		$nav [] = $res;
		
		$this->assign ( 'nav', $nav );
		$this->display ();
	}
	function setStatus() {
		$addon = I ( 'addon' );
		$token_status = D ( 'Common/AddonStatus' )->getList ();
		if ($token_status [$addon] === '-1') {
			$this->success ( '无权限设置' );
		}
		
		$status = 1 - I ( 'status' );
		$res = D ( 'Common/AddonStatus' )->set ( $addon, $status );
		$this->success ( '设置成功' );
	}
	
	// 宣传页面
	function leaflets() {
		$name = 'Leaflets';
		$config = array ();
		
		$map ['token'] = I ( 'get.token' );
		$addon_config = M ( 'member_public' )->where ( $map )->getField ( 'addon_config' );
		$addon_config = json_decode ( $addon_config, true );
		if (isset ( $addon_config [$name] )) {
			$config = $addon_config [$name];
			
			$config ['img'] = is_numeric ( $config ['img'] ) ? get_cover_url ( $config ['img'] ) : SITE_URL . '/Addons/Leaflets/View/default/Public/qrcode_default.jpg';
			$this->assign ( 'config', $config );
		} else {
			$this->error ( '请先保存宣传页的配置' );
		}
		define ( 'ADDON_PUBLIC_PATH', ONETHINK_ADDON_PATH . 'Leaflets/View/default/Public' );
		
		$this->display ( SITE_PATH . '/Addons/Leaflets/View/default/Leaflets/show.html' );
	}
	// 定时任务调用入口
	function cron() {
		D ( 'Home/Cron' )->run ();
		echo date ( 'Y-m-d H:i:s' ) . "\r\n";
	}
}