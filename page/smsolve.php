<?php

class page_smsolve extends Page{
	function page_index(){

		$sm_accounts =$this->add('Model_Member');//->addCondition('id',array(1417,1546,1741,2961,5791));
		$sm_accounts->addExpression('sm_account_count')->set($sm_accounts->refSQL('Account')->addCondition('AccountNumber','like','SM%')->count());
		$sm_accounts->addCondition('sm_account_count','>',1);

		foreach ($sm_accounts as $sma) {
			$m= $this->add('Model_Member');
			$m->addExpression('has_sm_account')->set($m->refSQL('Account')->addCondition('AccountNumber','like','SM%')->count());
			$m->addCondition('created_at','<',$sma['created_at']);
			$m->addCondition('has_sm_account',false);
			
			$grid=$this->add('Grid');
			$grid->add('View',null,'grid_buttons')->set($sma['id'] ." " .$sma['name']. " ". $m['created_at']);

			$grid->setModel($m,array('id','name','created_at'));
			$grid->addPaginator(20);
		}

	}
}