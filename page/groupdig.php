<?php

class page_groupdig extends Page{
	public $title = "Group Digging";

	function init(){
		parent::init();
		return;
		$group_id = $this->api->stickyGET('group_id');
		$to_date = $this->api->stickyGET('to_date');
		$from_date = $this->api->stickyGET('from_date');

		$bs_group = $this->add('xepan\accounts\Model_BSGroup');
		$bs_group->addCondition('parent_group_id',$group_id);

		$dr_bal = 0;
		$cr_bal = 0;

		foreach ($bs_group as $bsg) {
			$dr_bal += $bsg['ClosingBalanceDr']; 
			$cr_bal += $bsg['ClosingBalanceCr'];
		}

		$subgroupandledger = [];
		foreach ($bs_group as $group){
			$subgroupandledger[] = ['name'=>$group['name'],'type'=>'group','id'=>$group['id'],'class'=>'xepan-accounts-sub-group','balancecr'=>$group['ClosingBalanceCr'],'balancedr'=>$group['ClosingBalanceDr']]; 
		}
		
		$bs_ledger = $this->add('xepan\accounts\Model_BSLedger');
		$bs_ledger->addCondition('group_id',$group_id);

		foreach ($bs_ledger as $led) {
			$dr_bal += $led['ClosingBalanceDr']; 
			$cr_bal += $led['ClosingBalanceCr'];
		}

		foreach ($bs_ledger as $ledger){
			$subgroupandledger[] = ['name'=>$ledger['name'],'type'=>'ledger','id'=>$ledger['id'],'class'=>'xepan-accounts-ledger','balancecr'=>$ledger['ClosingBalanceCr'],'balancedr'=>$ledger['ClosingBalanceDr']]; 
		}

		$grid = $this->add('xepan\hr\Grid',null,null,['view\grid\subgroupandledger']);
		$grid->setSource($subgroupandledger);

		$g = $this->add('xepan\accounts\Model_BSGroup')->load($group_id);

		$grid->template->trySet('parent',$g['name']);
		$grid->template->trySet('from_date',$from_date);
		$grid->template->trySet('to_date',$to_date);
		$grid->template->trySet('dr',$dr_bal);
		$grid->template->trySet('cr',$cr_bal);

        $this->js('click')->_selector('.xepan-accounts-ledger')->univ()->frameURL('Account Statement',[$this->api->url('xepan_accounts_statement'),'ledger_id'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id'), 'from_date'=>$from_date, 'to_date'=>$to_date]);	
        $this->js('click')->_selector('.xepan-accounts-sub-group')->univ()->frameURL('Groups And Ledger',[$this->api->url('xepan_accounts_groupdig'),'group_id'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id'), 'from_date'=>$from_date, 'to_date'=>$to_date]);	
	}
}