<?php

class page_index extends xPage{
	public $title ="Dashboard";
	
	function init(){
		parent::init();
	

		// $a = $this->add('Model_Account_Recurring');
		// $a->addCondition('branch_id',2);
		// $a->addCondition('DefaultAC',false);
		// $a->tryLoadAny();

		// $non_interest_paid_premiums_till_now = $a->ref('Premium');
		// $non_interest_paid_premiums_till_now->addCondition('Paid','<>',0);

		// $this->add('Grid')->setModel($non_interest_paid_premiums_till_now);

		// $x = $a->ref('Premium');
		// $x->addCondition('Paid','<>',0);
		// echo $x->_dsql()->del('fields')->field($this->api->db->dsql()->expr('SUM(Amount*Paid)'))->debug()->getOne();


	}
}