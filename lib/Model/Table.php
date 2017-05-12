<?php

class Model_Table extends SQL_Model {
	
	function init(){
		parent::init();

		// Log Editing Entries

		$this->addHook('beforeSave',function($model){
			if(@$model->api->closing_running) return;
			
			if($model->loaded()){
				$old_m = $model->newInstance()->load($model->id);
				$changes=array();
				foreach ($model->dirty as $dirty_field=>$changed) {
					if($old_m[$dirty_field] != $model[$dirty_field])
						$changes[$dirty_field]=array('from'=>$old_m[$dirty_field],'to'=>$model[$dirty_field]);
				}

				if(!count($changes)) return;

				$log = $model->add('Model_Log');
				$log['model_class'] = get_class($model);
				$log['pk_id'] = $model->id;
				$log['name'] = json_encode($changes);
				$log['type'] = "Edit";
				$log->save();
			}
		});

		$this->addHook('beforeDelete',function($model){

				$log = $model->add('Model_Log');
				$log['model_class'] = get_class($model);
				$log['pk_id'] = $model->id;
				$log['name'] = json_encode($model->data);
				$log['type'] = "Delete";
				$log->save();
		});

	}

	function saveAs($model){
		$o = parent::saveAs($model);
		$this->data = $o->data;
		$this->id = $o->id;
	}
}