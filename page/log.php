<?php 

class page_log extends Page {
	public $title="Log Checking";

	function init(){
		parent::init();
		
		$this->add('Controller_Acl',['default_view'=>false]);
		
		$model = $this->add('Model_Log');
		if($model_filter = $this->api->stickyGET('model')){
			$model->addCondition('model_class',$model_filter);
		}

		if($from_date_filter = $this->api->stickyGET('from_date')){
			$model->addCondition('created_at','>=',$from_date_filter);
		}

		if($to_date_filter = $this->api->stickyGET('to_date')){
			$model->addCondition('created_at','<',$this->api->nextDate($to_date_filter));
		}
		if($pk=$this->api->stickyGET('pk')){
			$model->addCondition('pk_id',$pk);
		}

		$model->setOrder('created_at','desc');
		
		$model_names = $this->add('Model_Log');
		$model_names->title_field = "model_class";
		$model_names->_dsql()->group('model_class');
		
		$mn_arr= array();

		foreach ($model_names as $mn) {
			$mn_arr[$mn['model_class']] = $mn['model_class'];
		}

		$form = $this->add('Form');
		$form->addField('DropDown','model')->setValueList($mn_arr)->setEmptyText("All")->set('All');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addField('line','primary_key');

		$form->addsubmit('Get Records');

		$grid  = $this->add('Grid');
		$grid->setModel($model);
		$grid->addPaginator(100);
		$grid->setFormatter('name','wrap');

		$grid->addMethod('format_account',function($grid,$field){
			if($grid->model['model_class']){
				try{
					$grid->current_row[$field] = $grid->add($grid->model['model_class'])->tryLoad($grid->model['pk_id'])->get('name');
				}catch(Exception $e){
					$grid->current_row[$field] = $e->getMessage();
				}
				$grid->setTDParam($field,'style/color','');
			}
			else{
				$grid->current_row[$field] = $grid->model['model_class'];
				$grid->setTDParam($field,'style/color','red');
			}
		});

		$grid->addColumn('account','account');

		if($form->isSubmitted()){
			$grid->js()->reload(array(
				'from_date' => $form['from_date']?:0,
				'to_date' => $form['to_date']?:0,
				'pk' => $form['primary_key']?:0,
				'model' => $form['model']?:0
				))->execute();
		}

	}
} 