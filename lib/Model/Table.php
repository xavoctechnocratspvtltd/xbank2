<?php

class Model_Table extends SQL_Model {
	
	function init(){
		parent::init();

		// Log Editing Entries

		$this->addHook('beforeSave',function($model){

			if($model->loaded()){
				
				$old_m = $model->newInstance()->load($model->id);
				$changes=array();
				foreach ($model->dirty as $dirty_field=>$changed) {
					$changes[$dirty_field]=array('from'=>$old_m[$dirty_field],'to'=>$model[$dirty_field]);
				}

				$log = $model->add('Model_Log');
				$log['model_class'] = get_class($model);
				$log['pk_id'] = $model->id;
				$log['name'] = json_encode($changes);
				$log->save();
			}
		});

	}
}