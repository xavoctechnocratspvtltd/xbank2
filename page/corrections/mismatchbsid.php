<?php

/**
* description: ATK Page
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

class page_corrections_mismatchbsid extends \Page {
	public $title='Mismatch Balance Sheet ID';

	function init(){
		parent::init();
		
		$tr = $this->add('Model_Transaction');
		$tr->addCondition('id',['1992517','1992353','1990709','1986931','1992517','1992876','1992894','2008304','1992877','1992883','1992878','1992885','2008306','1992897','1992889','1992890','1992886','1993127']);
		$grid = $this->add('Grid');
		$grid->setModel($tr);

		// $from_date = $this->api->stickyGET('from_date');
		// $to_date = $this->api->stickyGET('to_date');

		// $form = $this->add('Form');
		// $form->addField('DatePicker','from_date')->validateNotNull();
		// $form->addField('DatePicker','to_date')->validateNotNull();
		// $view = $this->add('View');

		// $form->addSubmit("Update");
		// if($form->isSubmitted()){
		// 	$view->js()->reload(['from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0])->execute();
		// }
		// if(!$from_date){
		// 	$this->add('View')->set('Please select date range')->addClass('date-range-view');
		// 	return;
		// }

		// $tr = $this->add('Model_TransactionRow');
		// $act_join = $tr->join('accounts.id','account_id');
		// $act_join->addField('act_scheme_id','scheme_id');
		// $scheme_join = $tr->join('schemes.id',$tr->getElement('act_scheme_id'));
		// $scheme_join->addField('s_balance_sheet_id','balance_sheet_id');

		// $tr->addCondition('created_at','>=',$from_date);
		// $tr->addCondition('created_at','<',$this->app->nextDate($to_date));
		// $tr->addCondition('balance_sheet_id','<>',$tr->getElement('s_balance_sheet_id'));
		// $grid = $view->add('Grid');
		// $grid->setModel($tr);

		// $curr_trans_q ='select tr.account_id,tr.amountDr,tr.amountCr,tr.created_at,tr.transaction_id,s.id,s.balance_sheet_id,tr.balance_sheet_id
		// 			from transaction_row tr 
		// 			join accounts a on tr.account_id = a.id
		// 			join schemes s on a.scheme_id = s.id
		// 			where s.balance_sheet_id != tr.balance_sheet_id 
		// 				and 
		// 					tr.created_at >= "'.$from_date.'" 
		// 				and tr.created_at < "'.$this->app->nextDate($to_date).'"';
		
		// $curr_trans = $this->api->db->dsql()->expr($curr_trans_q)->get();
		// query saved
		// select tr.id, tr.account_id,tr.amountDr,tr.amountCr,tr.created_at,tr.transaction_id,s.id,s.balance_sheet_id,tr.balance_sheet_id from transaction_row tr join accounts a on tr.account_id = a.id join schemes s on a.scheme_id = s.id where s.balance_sheet_id != tr.balance_sheet_id and tr.created_at >= "2018-04-01" and tr.created_at < "2019-04-01" 

	}
}
