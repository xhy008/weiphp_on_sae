<?php

namespace Addons\Juhe\Controller;

use Home\Controller\AddonsController;

class JuheController extends AddonsController {
	function config() {
		$this->assign ( 'normal_tips', 'AppKey可在聚合数据的官网申请：http://www.juhe.cn' );
		parent::config ();
	}
}
