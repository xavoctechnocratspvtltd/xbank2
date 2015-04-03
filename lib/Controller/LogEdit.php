<?php

class Controller_LogEdit extends AbstractController {
	
	function init(){
		parent::init();

		if(!$this->owner instanceof SQL_Model) 
			throw $this->exception('Controller Must be added on SQL_Model');

		$this->owner->addHook('beforeSave',function($model){

			if($model->loaded()){
				
				$old_m = $model->newInstance()->load($model->id);
				$changes=array();
				foreach ($model->dirty as $dirty_field=>$changed) {
					$changes[$dirty_field]=array('from'=>$old_m[$dirty_field],'to'=>$model[$dirty_field]);
				}

				$log = $model->add('Model_Log');
				$log['account_id'] = $model instanceof Model_Account ?
											$model->id:
												$model instanceof Model_Transaction ?
													$model['reference_id']:
															$model instanceof Model_Member?
																$model->id : null;
				$log['name'] = json_encode($changes);
				$log->save();
			}
		});

	}
}