<?php

// TODOS: voucher_no in transaction table to be double now
// TODOS: all admission fee voucher narration is '10 (memberid)' format ... put memberid in reference id
// TODOS: refence_account_id to reference_id name change
// TODOS: account_type of existing accounts 

// DONE: Scheme Loan type => boolean to text PL/VL/SL or empty for non loan type accounts
// DONE: Saving account current interests till date as now onwards its keep saved on transaction

class page_testcorrections extends Page {
	public $total_taks=9;
	public $title = "Correction";

	function init(){
		parent::init();
		ini_set('memory_limit', '2048M');
		set_time_limit(0);

		// $this->correct_reference();
		// $this->checkAndCreateDefaultAccounts();
	}

	function correct_reference(){
		$trans = $this->add('Model_Transaction');
		$tr_row_j = $trans->join('transaction_row.transaction_id');
		$tr_row_j->hasOne('Account','account_id');
		$trans->addCondition('created_at','>','2015-04-01');

		foreach ($trans as $tr) {
			$acc = $tr->ref('account_id');
			if($acc->isFD() || $acc->isMIS() || $acc->isDDS() || $acc->isLoan() || $acc->isRecurring()){
				$tr['reference_id'] = $acc->id;
				$tr->save();
			}
		}

		$trans = $this->add('Model_Transaction');
		$trans->addCondition('created_at','>','2015-04-01');
		$trans->addCondition('reference_id',null);

		foreach ($trans as $tr) {
			preg_match_all("/(UDR|JHD|GOG|SYR|OGN)(FD|RD|DDS|MIS|CC|SB|VL|SL)([0-9]+)/i", $tr['Narration'], $acNo);
			// echo $acNo[1][0].$acNo[2][0].$acNo[3][0].' <br/>';
			$acc= $this->add('Model_Account')->tryLoadBy('AccountNumber',$acNo[1][0].$acNo[2][0].$acNo[3][0]);
			if($acc->loaded()){
				$tr['reference_id'] = $acc->id;
				$tr->saveAndUnload();
			}
		}

	}

	// function checkAndCreateDefaultAccounts(){

 //   		$scheme = $this->add('Model_Scheme');
	// 	$branch = $this->add('Model_Branch');
	// 	foreach (explode(",", ACCOUNT_TYPES) as $acc_type) {
	//    		$all_schemes = $this->add('Model_Scheme_'.$acc_type);
	// 		foreach ($all_schemes as $junk) {
 //                                foreach ($default_accounts = $all_schemes->getDefaultAccounts() as $details) {
 //                                        $account = $this->add('Model_Account');
 //                                        $account->_dsql()->where('AccountNumber Like "%'.$branch['Code'].SP.$details['intermediate_text'].'%'.$all_schemes['name'].'"');
 //                                        $account->tryLoadAny();
 //                                        if($account->loaded())
 //                                                $this->add('View_Error')->set($account['AccountNumber']);
 //                                        else
 //                                            $this->add('View_Info')->set($account['AccountNumber']);
 //                                        $account->destroy();
 //                                        $scheme->unload();
 //                                }
	// 		}
	// 	}
 //   	}

	function query($q,$get=false){
		$obj = $this->api->db->dsql()->expr($q);
		if($get)
			return $obj->getOne();
		else
			return $obj->execute();
	}


	function page_updateVlDocument(){
		$loan_account = $this->add('Model_Account_Loan');
		$loan_account->addCondition($loan_account->dsql()->orExpr()->where('AccountNumber','like','%vl%')->where('AccountNumber','like','%fvl%'));
		$loan_account->addCondition('ActiveStatus',true);
		$loan_account->setOrder('id','desc');

		$str="";
		$count = 0;
		foreach ($loan_account as $junk) {
			$document = $this->add('Model_DocumentSubmitted');
			$document->addCondition('accounts_id',$junk->id);
			$document->addCondition('documents_id',9);
			$document->tryLoadAny();
			if(!$document->count()->getOne()){
				$document->save();
				$count++;
			}

			$str .= $junk['id'].$junk['AccountNumber']."<br/>"; 
		}

		$this->add('View_Info')->set("Total Account".$loan_account->count()->getOne()."Total Account Update".$count);
		$this->add('View_Info')->setHtml($str);


	}



}