<?php


class page_reports_bs_easybsstudy extends Page {
	public $table = "Easy Individual Balancesheet Component";

	function init(){
		parent::init();

		$form = $this->add('Form');
		$form->addField('DatePicker','from_date')->validateNotNull();
		$form->addField('DatePicker','to_date')->validateNotNull();
		$form->addField('DropDown','balance_sheet_heads')->setEmptyText("Please select")->validateNotNull()->setModel('BalanceSheet');
		$form->addSubmit('Go');

		$view = $this->add('View');
		if($this->app->stickyGet('_id')){
			$view->add('page_reports_bs_bstoschemegroup');
		}

		if($form->isSubmitted()){			
			$view->js()->reload(
				[
					'_id'=>$form['balance_sheet_heads'],
					'from_date'=>$form['from_date'],
					'to_date'=>$form['to_date'],
					'branch_id'=>($this->app->current_branch->id == 1 ? 0:$this->app->current_branch->id)
				])->execute();
		}
	}
}