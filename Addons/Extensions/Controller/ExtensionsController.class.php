<?php

namespace Addons\Extensions\Controller;

use Home\Controller\AddonsController;

class ExtensionsController extends AddonsController {
	// 通用插件的编辑模型
	public function edit($model = null, $id = 0) {
		is_array ( $model ) || $model = $this->getModel ( $model );
		$id || $id = I ( 'id' );
		
		if (IS_POST) {
			$Model = D ( parse_name ( get_table_name ( $model ['id'] ), 1 ) );
			
			// 清空旧关键词
			$keyword = $Model->where ( 'id=' . $id )->getField ( 'keyword' );
			$keyword = preg_split ( '/[\s,;]+/', $keyword ); // 以空格tab逗号分号分割关键词
			$data ['addon'] = 'Extensions';
			foreach ( $keyword as $key ) {
				$data ['keyword'] = trim ( $key );
				$res = M ( 'keyword' )->where ( $data )->delete ();
			}
			
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $model ['id'] );
			if ($Model->create () && $Model->save ()) {
				
				$this->_saveKeyword ( $model, $id );
				
				$this->success ( '保存' . $model ['title'] . '成功！', U ( 'lists?model=' . $model ['name'] ) );
			} else {
				$this->error ( $Model->getError () );
			}
		} else {
			parent::edit ( $model, $id );
		}
	}
	public function del() {
		$model = $this->getModel ( 'Extensions' );
		
		$ids = I ( 'id' );
		if (empty ( $ids )) {
			$ids = array_unique ( ( array ) I ( 'ids', 0 ) );
		}
		if (empty ( $ids )) {
			$this->error ( '请选择要操作的数据!' );
		}
		// 删除关键词
		$map ['aim_id'] = array (
				'in',
				$ids 
		);
		$map ['addon'] = 'Extensions';
		$map ['token'] = get_token ();
		D ( 'Common/Keyword' )->where ( $map )->delete ();
		
		parent::common_del ( $model, $ids );
	}
}
