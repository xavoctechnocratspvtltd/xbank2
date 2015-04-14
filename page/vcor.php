<?php

class page_vcor extends Page {
	function init(){
		parent::init();

		$branches = $this->add('Model_Branch');
		foreach ($branches as $branch) {
			$tra = $this->add('Model_Transaction');
			$tra->addCondition('branch_id',$branch->id);
			$tra->addCondition('created_at','>=','2015-04-01');
			$tra->setOrder('created_at','asc');
			$i=1;
			foreach ($tra as $t) {
				$t['voucher_no'] =$i;
				$i++;
				$t->save();
			}
		}
	}
}