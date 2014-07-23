<?php

namespace Addons\Forms\Controller;

use Home\Controller\AddonsController;

function get_forms_id() {
	return $_REQUEST ['forms_id'];
}
class BaseController extends AddonsController {
}
