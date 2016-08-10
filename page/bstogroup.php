<?php

class page_bstogroup extends Page{
	public $title = "Balance Sheet Groups";
	function init(){
		parent::init();
				
		$bs_id = $this->api->stickyGET('bs_id');
		$from_date = $this->api->stickyGET('from_date');
		$to_date = $this->api->stickyGET('to_date');
		
		$bs_group = $this->add('Model_BS_Scheme',['from_date'=>$from_date,'to_date'=>$to_date]);
		$bs_group->addCondition('balance_sheet_id',$bs_id);

		$grid = $this->add('Grid_Template',null,null,['view\grid\bstogroup']);
		$grid->setModel($bs_group);

		$bs = $this->add('Model_BS_BalanceSheet')->load($bs_id);
		$grid->template->trySet('head',$bs['name']);
		$grid->template->trySet('from_date',$from_date);
		$grid->template->trySet('to_date',$to_date);

		$grid->addTotals(['ClosingBalanceDr','ClosingBalanceCr']);
        $this->js('click')->_selector('.xepan-accounts-bs-subgroup')->univ()->frameURL('Groups And Ledger',[$this->api->url('groupdig'),'group_id'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id'), 'from_date'=>$from_date, 'to_date'=>$to_date]);
	}
}