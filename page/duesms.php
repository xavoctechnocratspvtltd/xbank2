<?php
class page_duesms extends \Page{
	
	public $title="Due SMS sending for Loan and RD from premium table";

	function init(){
		parent::init();

		// Just to avoid unnessaroy data breach
		// $token1= $_GET['token1'];
		// $token2= $_GET['token2'];

		// if(!$token1 || !$token2 || ($token1*$token2)%13 !=0 || $token1 != date('d',strtotime($this->app->today))){
		// 	throw new Exception("Not proper way to call duesms page", 1);
		// }

		set_time_limit(0);
		
		$before_days = [0,5];
		$date=[];

		foreach ($before_days as $bd) {
			$date[] = date('Y-m-d',strtotime($this->app->today.' +'.$bd.' days'));
		}

		$premium = $this->add('Model_Premium');

		$acc_j = $premium->join('accounts','account_id');
		$acc_j->addField('AccountNumber');
		$acc_j->addField('ActiveStatus');
		$mem_j = $acc_j->join('members','member_id');
		$mem_j->addField('PhoneNos');

		$premium->addExpression('msg',function($m,$q){
			return $q->expr('CONCAT("Dear Member, Your premium of INR ",[0]," is due on dated ",DATE_FORMAT([1],"%d-%m-%Y")," for account ",[2],", Please pay on time to avoid penalties. From:- Bhawani Credit Co-Operative Society Ltd. +91 8003597814")',
				[
					$q->getField('Amount'),
					$m->getElement('DueDate'),
					$m->getElement('AccountNumber')
				]);
		});

		$premium->addCondition('ActiveStatus',true);
		$premium->addCondition('DueDate',$date);
		$premium->addCondition('PaidOn','is',null);

		if($this->app->stickyGET('debug')){
			$grid = $this->add('Grid');
			$grid->setModel($premium,['AccountNumber','PhoneNos','Amount','DueDate','msg']);
			return;
		}

		$cont = $this->add('Controller_Sms');
		foreach ($premium as $p) {
			$no = explode(",", $p['PhoneNos']);
			$no = $no[0];
			$no = explode("/", $no);
			$no = $no[0];
			$return = $cont->sendMessage($no,$p['msg']);
			echo $no.' '. $p['msg']. '<br/>'.$return.'<hr/>';
			if($_GET['test']) break;
		}

	}
}