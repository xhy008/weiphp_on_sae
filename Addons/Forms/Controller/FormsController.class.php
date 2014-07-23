<?php

namespace Addons\Forms\Controller;

use Home\Controller\AddonsController;

class FormsController extends AddonsController {
	function forms_attribute() {
		$param ['forms_id'] = I ( 'id', 0, 'intval' );
		$url = addons_url ( 'Forms://FormsAttribute/lists', $param );
		// dump($url);
		redirect ( $url );
	}
	function forms_value() {
		$param ['forms_id'] = I ( 'id', 0, 'intval' );
		$url = addons_url ( 'Forms://FormsValue/lists', $param );
		// dump($url);
		redirect ( $url );
	}
	function forms_export() {
	}
	function preview() {
		$param ['forms_id'] = I ( 'id', 0, 'intval' );
		$url = addons_url ( 'Forms://FormsValue/add', $param );
		// dump($url);
		redirect ( $url );
	}	
}
