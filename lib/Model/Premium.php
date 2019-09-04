<?php
class Model_Premium extends Model_Table {
	var $table= "premiums";
	function init(){
		parent::init();


		$this->hasOne('Account','account_id');
		$this->addField('Amount');
		$this->addField('Paid')->defaultValue('0');//->type('boolean')->defaultValue(false);
		$this->addField('Skipped')->type('boolean')->defaultValue(false);
		$this->addField('created_at')->type('datetime')->defaultValue($this->api->now);
		$this->addField('updated_at')->type('datetime')->defaultValue($this->api->now);
		$this->addField('PaidOn')->type('datetime')->defaultValue(null);
		$this->addField('AgentCommissionSend')->type('boolean')->defaultValue(false)->caption('Comm Send');
		$this->addField('AgentCommissionPercentage')->type('money')->caption('Comm %');
		$this->addField('AgentCollectionChargesPercentage')->type('money')->caption('Collection Charge');
		$this->addField('AgentCollectionChargesSend')->type('boolean')->defaultValue(false)->caption('Coll. Charg. Send');
		$this->addField('PaneltyCharged')->type('money')->defaultValue(0);
		$this->addField('PaneltyPosted')->type('money')->defaultValue(0);
		$this->addField('DueDate')->type('date');

		$this->addExpression('name')->set($this->refSQL('account_id')->fieldQuery('name'));

		$this->addExpression('panelty_to_post')->set('PaneltyCharged - PaneltyPosted');

		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function payNowForRecurring($on_date=null){
		
		if(!$on_date) $on_date = $this->api->now;

		if(!isset($on_date)){
			$on_date = date('Y-m-d h:i:s');
		}
		
		$this['PaidOn'] = $on_date;
		$this->saveAs('Model_Premium');

		$this->reAdjustPaidValue($on_date);
		$this->giveAgentCommission($on_date);
		$this->giveCollectionCharges($on_date);

	}

	function account(){
		return $this->ref('account_id');
	}

	function giveAgentCommission($on_date = null){
		
		if(!$on_date) $on_date = $this->api->now;

		if(!$this->account()->agent()) return ;

		$all_paid_noncommissioned_preimums = $this->ref('account_id')->ref('Premium');
		// $all_paid_noncommissioned_preimums->addCondition('Paid','<>',0);
		$all_paid_noncommissioned_preimums->addCondition('PaidOn','<>',null);
		$all_paid_noncommissioned_preimums->addCondition('AgentCommissionSend',0);

		$commission = 0;

		foreach($all_paid_noncommissioned_preimums as $junk){
			$commission = $commission + ($all_paid_noncommissioned_preimums['Amount'] * $all_paid_noncommissioned_preimums['AgentCommissionPercentage'] / 100.00);
			$all_paid_noncommissioned_preimums['AgentCommissionSend'] = true;
			$all_paid_noncommissioned_preimums->saveAndUnload();			
		}

		

		$commissionForThisAgent = round($this->account()->agent()->cadre()->selfEfectivePercentage() * $commission / 100.00,2);

		if(!$commissionForThisAgent) return;

		$tds_percentage = $this->ref('account_id')->ref('agent_id')->ref('member_id')->get('PanNo')?TDS_PERCENTAGE_WITH_PAN:TDS_PERCENTAGE_WITHOUT_PAN;
		$tds = round($commissionForThisAgent * $tds_percentage / 100,2);


		$account = $this->ref('account_id');
		$atype='RD';
		if($account['SchemeType']=='DDS') $atype='DDS';

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT, $account->ref('branch_id'), $on_date, $atype." Premium Commission ".$account['AccountNumber'], null, array('reference_id'=>$account->id));
		
		$transaction->addDebitAccount($account['branch_code'] . SP . COMMISSION_PAID_ON . SP. $account['scheme_name'] , $commissionForThisAgent);
		
		$transaction->addCreditAccount($account->ref('agent_id')->ref('account_id'), $commissionForThisAgent - $tds);
		$transaction->addCreditAccount($account['branch_code'].SP.BRANCH_TDS_ACCOUNT, $tds);

		$transaction_id = $transaction->execute();

		$this->add('Model_AgentTDS')
			->createNewEntry($account['agent_id'],$transaction_id,$account->id,$commissionForThisAgent,$tds,$commissionForThisAgent - $tds);

		$this->account()->propogateAgentCommission($account['branch_code'] . SP . COMMISSION_PAID_ON . SP. $account['scheme_name'], $total_commission_amount = $commission, $on_date,$account->id);
		
	}

	function giveCollectionCharges($on_date=null){
		if(!$on_date) $on_date = $this->api->today;

		$all_paid_noncollected_preimums = $this->ref('account_id')->ref('Premium');
		$all_paid_noncollected_preimums->addCondition('AgentCollectionChargesSend',0);
		// $all_paid_noncollected_preimums->addCondition('Paid','<>',0);
		$all_paid_noncollected_preimums->addCondition('PaidOn','<>',null);

		$commission = 0;
		$account = $this->account();

		$atype='RD';
		if($account['SchemeType']=='DDS') $atype='DDS';

		if(!$account->collectionAgent()) return;

		foreach($all_paid_noncollected_preimums as $junk){
			$commission = $commission + ($all_paid_noncollected_preimums['Amount'] * $all_paid_noncollected_preimums['AgentCollectionChargesPercentage'] / 100.00);
			$all_paid_noncollected_preimums['AgentCollectionChargesSend'] = true;
			$all_paid_noncollected_preimums->saveAndUnload();			
		}

		$commissionForThisAgent = round($commission,2);

		$tds_percentage = $this->ref('account_id')->ref('agent_id')->ref('member_id')->get('PanNo')?TDS_PERCENTAGE_WITH_PAN:TDS_PERCENTAGE_WITHOUT_PAN;
		$tds = round($commissionForThisAgent * $tds_percentage / 100,2);

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_PREMIUM_AGENT_COLLECTION_CHARGE_DEPOSIT, $account->ref('branch_id'), $on_date, $atype." Premium Collection ".$account['AccountNumber'], null, array('reference_id'=>$account->id));
		
		$transaction->addDebitAccount($account['branch_code'] . SP . COLLECTION_CHARGE_PAID_ON . SP. $account['scheme_name'] , $commissionForThisAgent);
		
		$transaction->addCreditAccount($account->ref('agent_id')->ref('account_id'), $commissionForThisAgent -$tds);
		$transaction->addCreditAccount($account['branch_code'].SP.BRANCH_TDS_ACCOUNT, $tds);
		
		$transaction_id = $transaction->execute();

		$this->add('Model_AgentTDS')
			->createNewEntry($account['agent_id'],$transaction_id,$account->id,$commissionForThisAgent,$tds,$commissionForThisAgent - $tds);

	}

	function getAllForPaneltyPosting($on_date=null){

		$this->getElement('account_id')->destroy();

		if(!$on_date) $on_date = $this->api->now;

		$loan_premiums = $this;
		$loan_premiums->dsql()->del('fields');//->fieldQuery('sum(PaneltyCharged - PaneltyPosted )');

		$account_join = $loan_premiums->join('accounts','account_id');
		$scheme_join = $account_join->join('schemes','scheme_id');

		$account_join->addField('ActiveStatus');
		$scheme_join->addField('SchemeType');

		$loan_premiums->addCondition('ActiveStatus',true);
		$loan_premiums->addCondition('SchemeType',ACCOUNT_TYPE_LOAN);
		$loan_premiums->addCondition('PaneltyCharged','<>',$this->dsql()->expr('PaneltyPosted'));
		

		$loan_premiums->_dsql()->debug()->group('account_id');

		return $this;
	}

	function reAdjustPaidValue($on_date=null){
		if(!$on_date) $on_date = $this->api->now;

		// echo "Re adjusting ".$this['account'].' on '. $on_date . ' <br/>';

		$premiums_to_affect = $this->ref('account_id')->ref('Premium');
		$no_of_premiums = $premiums_to_affect->count()->getOne();
		$premiums_to_affect->_dsql()->where("(DueDate <='$on_date')");
		$premiums_to_affect->setOrder('id');

		$i=1;
		foreach ($premiums_to_affect as $junk) {
			$paid_premiums_before_date = $this->add('Model_Premium');
			$paid_premiums_before_date->addCondition('account_id',$premiums_to_affect['account_id']);

			if($i < $no_of_premiums){
				$paid_premiums_before_date->addCondition('PaidOn','<',$this->api->nextDate(date('Y-m-t',strtotime($premiums_to_affect['DueDate']))));
				// echo $i. '/'. $no_of_premiums .' PaidOn < '. date('Y-m-t',strtotime($this->api->nextDate($premiums_to_affect['DueDate']))). '<br/>';
			}
			else{
				$paid_premiums_before_date->addCondition('PaidOn','<',$this->api->nextDate($premiums_to_affect['DueDate']));
				// echo $i. '/'. $no_of_premiums .' PaidOn < '. $this->api->nextDate($premiums_to_affect['DueDate']). '<br/>';
			}
			
			$paid_premiums_before_date->addCondition('id','<=',$premiums_to_affect->id);			
			$premiums_to_affect['Paid'] = $paid_premiums_before_date->count()->getOne();
			$premiums_to_affect->saveAndUnload();
			$i++;
		}

		// var_dump($this->add('Model_Premium')->addCondition('account_id',$this['account_id'])->getRows(['DueDate','PaidOn','Paid','Account']));
	}

    // function reAdjustPaidValues($on_date=null){ udrrd1005

    // 	if(!$on_date) $on_date=$this->api->now;
    // 	$tilldate = $on_date;

    //     // $CI->db->query("UPDATE jos_xpremiums SET Paid=0 WHERE accounts_id = $this->accounts_id");
    // 	// reset all paid  = 0 first
    // 	$this->add('Model_Premium')
    // 		->addCondition('account_id',$this['account_id'])
    // 		->_dsql()
    // 		->set('Paid',0)
    // 		->update();

    //     // $due_and_paid_query = $CI->db->query("
    //     // 		SELECT 
    //     // 			GROUP_CONCAT(EXTRACT(YEAR_MONTH FROM DueDate)) DueArray, 
    //     // 			GROUP_CONCAT(EXTRACT(YEAR_MONTH FROM PaidOn)) PaidArray 
    //     // 		FROM 
    //     // 			jos_xpremiums 
    //     // 		WHERE 
    //     // 			accounts_id = $this->accounts_id 
    //     // 		AND 
    //     // 			(PaidOn < '$tilldate' OR DueDate < '$tilldate') 
    //     // 		ORDER BY id
    //     // 		")->row();

    //     $due_and_paid_query = $this->api->db->dsql()
    //     							->table('premiums')
    //     							->field($this->dsql()->expr('GROUP_CONCAT(EXTRACT(YEAR_MONTH FROM DueDate)) DueArray'))
    //     							->field($this->dsql()->expr('GROUP_CONCAT(EXTRACT(YEAR_MONTH FROM PaidOn)) PaidArray'))
				// 					->where('account_id',$this['account_id'])
				// 					->where("(PaidOn < '$tilldate' OR DueDate < '$tilldate')")
				// 					->order('id')
				// 					->getHash()
				// 					;						


    //     $due_array=explode(",",$due_and_paid_query['DueArray']);
    //     $paid_array=explode(",",$due_and_paid_query['PaidArray']);

        
    //     $account_premiums=$this->add('Model_Premium');
    //     $account_premiums
    //     ->_dsql()
    //     ->where('account_id',$this['account_id'])
    //     ->where("(PaidOn <= '$tilldate' OR DueDate <= '$tilldate')")
    //     ->order('id');

    //     $i=0;
    //     foreach($account_premiums as $p){
    //         // echo "setting";
    //         $paid=0;
    //         for($j=0;$j<=$i;$j++){
    //             if(isset($paid_array[$j]) AND $paid_array[$j] <= $due_array[$i]) $paid++;
    //             // if(isset($paid_array[$j]) AND $j==0 AND $paid_array[$j] > $due_array[$i]) $paid++;
    //         }
    //         $account_premiums['Paid']= $paid;
    //         $account_premiums->save();
    //         $i++;
    //     }

    // }

}