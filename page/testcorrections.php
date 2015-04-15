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
		
		$this->correct_reference();
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



}