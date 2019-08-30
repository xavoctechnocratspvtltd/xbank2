<?php

class page_noclog_report extends Page {
	public $title='NOC Report';

	function init(){
		parent::init();
			
		$this->add('Controller_Acl');

		$report_type_list = [
						'noc_not_made_due_to'=>'Noc Not Made Due To',
						'noc_hold_due_to'=>'Noc Hold Due To',
						'noc_lying_in_branch'=>'Noc Lying in Branch',
						'noc_dispatched'=>'NOC Dispatched',
						'noc_return_by_branch'=>'NOC Return By Branch'
					];

		$filter = $this->app->stickyGET('filter');
		$from_date = $this->app->stickyGET('from_date')?:0;
		$to_date = $this->app->stickyGET('to_date')?:0;
		$branch = $this->app->stickyGET('branch');
		$account = $this->app->stickyGET('account');
		$report_type = $this->app->stickyGET('report_type');
		

		$view = $this->add('View')->setStyle('width','900px');
		$form = $view->add('Form',null,null,['form/horizontal']);
		$from_date_field = $form->addField('DatePicker','from_date');
		$to_date_field = $form->addField('DatePicker','to_date');
		$branch_field = $form->addField('DropDown','branch');
		$account_field = $form->addField('autocomplete/Basic','account');
		$report_type_field = $form->addField('DropDown','report_type');

		$form->addSubmit('Filter');

		$branch_model = $this->add('Model_Branch')->addCondition('id','=',$this->app->current_branch->id);
		$branch_field->setModel($branch_model);
		$branch_field->setEmptyText('All Branch');

		$account_model = $this->add('Model_Account_Loan');
		$account_model->addCondition('DefaultAC',false);
		$account_field->setModel($account_model);

		$report_type_field->setValueList($report_type_list);
		$report_type_field->setEmptyText('All');

		$this->add('View')->setElement('hr');

		$grid = $this->add('Grid');
		$model = $this->add('Model_NocLog');
		$model->addExpression('account_number')->set(function($m,$q){
			return $q->expr('[0]',[$m->refSQL('accounts_id')->fieldQuery('AccountNumber')]);
		});
		$model->addExpression('member_name')->set(function($m,$q){
			return $q->expr('[0]',[$m->refSQL('accounts_id')->fieldQuery('member_name_only')]);
		});
		$model->addExpression('member_id')->set(function($m,$q){
			return $q->expr('[0]',[$m->refSQL('accounts_id')->fieldQuery('member_id')]);
		});

		$model->addExpression('mobile_no')->set(function($m,$q){
			$member_model = $m->add('Model_Member')->addCondition('id',$m->getElement('member_id'));
			return $q->expr('[0]',[$member_model->fieldQuery('PhoneNos')]);
		});

		// duration
		// if dispatched then dispatched_date
		// if return then return date
		// else today;
		$model->addExpression('duration')->set(function($m,$q){
			return $q->expr('DATEDIFF(
						(
							CASE 
								WHEN [is_dispatch_to_customer] = 1 THEN [dispatch_at]
								WHEN [is_return] = 1 THEN [return_at]
								ELSE [today] 
							END
						),
						[created_on]
					)',[
						'created_on'=>$m->getElement('send_at'),
						'today'=>'"'.$this->app->today.'"',
						'is_dispatch_to_customer'=>$m->getElement('is_dispatch_to_customer'),
						'dispatch_at'=>$m->getElement('dispatch_at'),
						'is_return'=>$m->getElement('is_return'),
						'return_at'=>$m->getElement('return_at')
						]
				);
		});

		if($filter){
			if($from_date) $model->addCondition('send_at','>=',$from_date);
			if($to_date) $model->addCondition('send_at','<',$this->app->nextDate($to_date));
			if($branch > 0 ) $model->addCondition('to_branch_id',$branch);
			if($account > 0 ) $model->addCondition('accounts_id',$account);

			switch ($report_type){
				case 'noc_not_made_due_to':
					$model->addCondition('is_noc_not_made',true);
					break;
				case 'noc_hold_due_to':
					$model->addCondition('is_noc_hold',true);
					break;
				case 'noc_lying_in_branch':
					$model->addCondition('is_dispatch_to_customer',false);
					$model->addCondition('is_return',false);
					$model->addCondition('received_by_id','>',0);
					break;
				case 'noc_dispatched':
					$model->addCondition('is_dispatch_to_customer',true);
					break;
				case 'noc_return_by_branch':
					$model->addCondition('is_return',true);
					break;
			}
		}

		$model->setOrder('id','desc');
		$model->getElement('created_by')->caption('Send Detail');
		$model->getElement('received_narration')->caption('Receive Detail');
		$model->getElement('dispatch_narration')->caption('Dispatch Detail');
		$model->getElement('return_narration')->caption('Return Detail');
		$model->getElement('return_received_narration')->caption('Return Received Detail');

		$grid->addSno();
		$grid->setModel($model,['account_number','member_name','noc_letter_received_on','send_at','created_by','received_by','received_at','received_narration','from_branch','to_branch','received_by','is_dispatch_to_customer','dispatch_at','dispatch_by','dispatch_narration','return_by','return_received','send_at','send_narration','received_narration','is_return','return_at','return_narration','return_received_narration','return_received_by','accounts_id','from_branch_id','to_branch_id','created_by_id','received_by_id','dispatch_by_id','return_by_id','return_received_by_id','noc_not_made_due_to','noc_hold_due_to']);
		
		$grid->addHook('formatRow',function($g){
			$g->current_row_html['created_by'] = 'Created By: '.$g->model['created_by']."<br/> From Branch: ".$g->model['from_branch']."<br/>"."Narration: ".$g->model['send_narration'];
			$g->current_row_html['received_narration'] = 'Receive By: '.$g->model['received_by']."<br/>Branch: ".$g->model['to_branch']."<br/>"."Narration: ".$g->model['received_narration'];
			$g->current_row_html['dispatch_narration'] = 'Dispatch By: '.$g->model['dispatch_by']."<br/>"."Narration: ".$g->model['dispatch_narration'];
			$g->current_row_html['return_narration'] = 'Return By: '.$g->model['return_by']."<br/>"."Narration: ".$g->model['return_narration'];
			$g->current_row_html['return_received_narration'] = 'Return Receive By: '.$g->model['return_received_by']."<br/>"."Narration: ".$g->model['return_received_narration'];
			
			if($g->model['received_at'] === "0000-00-00 00:00:00")
				$g->current_row_html['received_at'] = "-";
			else
				$g->current_row_html['received_at'] = $g->model['received_at'];
			if($g->model['dispatch_at'] === "0000-00-00 00:00:00")
				$g->current_row_html['dispatch_at'] = "-";
			else
				$g->current_row_html['dispatch_at'] = $g->model['dispatch_at'];

			if($g->model['return_at'] === "0000-00-00 00:00:00")
				$g->current_row_html['return_at'] = "-";
			else
				$g->current_row_html['return_at'] = $g->model['return_at'];
		});


		$remove_column = ['accounts_id','from_branch_id','to_branch_id','created_by_id','received_by_id','dispatch_by_id','return_by_id','return_received_by_id','from_branch','send_narration','received_by','to_branch','dispatch_by','return_by','return_received_by'];

		foreach ($remove_column as $key => $value) {
			$grid->removeColumn($value);
		}
		// grid column
		// sn_no | Account_no | member_name | Noc Letter Receive Date |noc sent by| send_date | Received_by | Received_date | Noc Dispatched Detail | Return Detail | AC deactivated date
	
		if($form->isSubmitted()){
			$grid->js()->reload([
					'filter'=>1,
					'from_date'=>$form['from_date']?:0,
					'to_date'=>$form['to_date']?:0,
					'branch'=>$form['branch']?:0,
					'account'=>$form['account']?:0,
					'report_type'=>$form['report_type'],
				])->execute();
		}


	}
}