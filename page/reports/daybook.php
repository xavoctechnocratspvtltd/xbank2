<?php

class page_reports_daybook extends Page {
	public $title = "Day Book";
	// public $title = 'Accounts Manager';
	function init(){
		parent::init();
		
		$form = $this->add('Form')->addClass('noneprintalbe');
		$form->addField('DatePicker','from_date')->validateNotNull();
		$form->addField('DatePicker','date')->validateNotNull();
		$form->addField('DropDown','voucher_type')->setEmptyText('All')->setModel('TransactionType');

		if($this->app->auth->model->isSuper()){
			$form->addField('DropDown','branch')->setEmptyText('All')->setModel('Branch');
		}

		$selectedVoucher = $form->addField('hidden','selected_voucher');

		$open_day_book = $form->addSubmit('Open Day Book');
		$print_voucher = $form->addSubmit('Print Voucher');

		$day_transaction_model = $this->add('Model_Transaction');
		$transaction_row=$day_transaction_model->join('transaction_row.transaction_id');
		$transaction_row->hasOne('Account','account_id');
		$transaction_row->addField('amountDr');
		$transaction_row->addField('amountCr');

		// $ref_account = $day_transaction_model->join('accounts','reference_id');
		// $ref_account_member = $ref_account->join('members','member_id');
		// $ref_account_member->addField('member_name','name');
		// $ref_account_member->addField('PermanentAddress');
		// $ref_account_member->addField('PanNo');

		
		$day_transaction_model->add('Controller_Acl');
		$day_transaction_model->setOrder('voucher_no');
		
		$daybook_lister_grid = $this->add('Grid_DayBook');
		$daybook_lister_grid->add('View',null,'grid_buttons')->set('Day Book');
		$daybook_lister_grid->add('View',null,'grid_buttons')->set('Date :'. $_GET['date_selected'] )->addClass('pull-right');

		if($_GET['date_selected']){			
			// $day_transaction_model->addCondition('created_at','>=',$_GET['date_selected']);
			$day_transaction_model->addCondition('created_at','<',$this->api->nextDate($_GET['date_selected']));
		}
		else{
			$day_transaction_model->addCondition('created_at','>=',$this->api->today);
			$day_transaction_model->addCondition('created_at','<',$this->api->nextDate($this->api->today));
		}

		if($_GET['from_date']){
			$day_transaction_model->addCondition('created_at','>=',$_GET['from_date']);

		}

		if($_GET['voucher_type']){
			$day_transaction_model->addCondition('transaction_type_id',$_GET['voucher_type']);
		}

		if($_GET['branch']){
			$day_transaction_model->addCondition('branch_id',$_GET['branch']);
		}
 
		$daybook_lister_grid->setModel($day_transaction_model,array('voucher_no','Narration','member_name','PermanentAddress','PanNo','account','amountDr','amountCr'));
		$daybook_lister_grid->removeColumn('Narration');
		// $daybook_lister_grid->addPaginator(10);

		$daybook_lister_grid->addSelectable($selectedVoucher);


		if($form->isSubmitted()){

			if($form->isClicked($print_voucher)){
				$selected_voucher_array= explode(',', $form['selected_voucher']);
				if(!$form['selected_voucher'] or $selected_voucher_array[0] == "" or $selected_voucher_array[0] == '[]'){
					throw new \Exception("Please Select Voucher, to be Print");
				}				
				$form->js()->univ()->newWindow($this->api->url('voucher_print',array('selected_voucher_list'=>$form['selected_voucher'],'hide_print_btn'=>true,'cut_page'=>0)))->execute();
			}
			if($form->isClicked($open_day_book)){
				$daybook_lister_grid->js()->reload(array('date_selected'=>$form['date']?:0,'from_date'=>$form['from_date']?:0,'voucher_type'=>$form['voucher_type'],'branch'=>$form['branch']?:0))->execute();
			}

		}

		$js=array(
			$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
			$this->js()->_selector('#header')->toggle(),
			$this->js()->_selector('#footer')->toggle(),
			$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
			$this->js()->_selector('.atk-form')->toggle(),
			);

		$pl = $daybook_lister_grid->addButton('Print List')->addClass('noneprintalbe');
		$pl->js('click',$js);
	}
}