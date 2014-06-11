<?php
class Model_Premium extends Model_Table {
	var $table= "premiums";
	function init(){
		parent::init();


		$this->hasOne('Account','account_id');
		$this->addField('Amount');
		$this->addField('Paid');//->type('boolean')->defaultValue(false);
		$this->addField('Skipped')->type('boolean')->defaultValue(false);
		$this->addField('created_at')->type('datetime')->defaultValue($this->api->now);
		$this->addField('updated_at')->type('datetime')->defaultValue($this->api->now);
		$this->addField('PaidOn')->type('datetime')->defaultValue(null);
		$this->addField('AgentCommissionSend')->type('boolean')->defaultValue(false);
		$this->addField('AgentCommissionPercentage')->type('money');
		$this->addField('PaneltyCharged')->type('money');
		$this->addField('PaneltyPosted')->type('money');
		$this->addField('DueDate')->type('date');

		$this->addExpression('panelty_to_post')->set('PaneltyCharged - PaneltyPosted');

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function payNowForRecurring($on_date=null){
		if(!$on_date) $on_date = $this->api->now;
		
		$this['PaidOn'] = $on_date;
		$this->save();

		$this->reAdjustPaidValues($on_date);

		$this->giveAgentCommission($on_date);
	}

	function giveAgentCommission($on_date = null){
		if(!$on_date) $on_date = $this->api->now;

		$all_paid_noncommissioned_preimums = $this->ref('account_id')->ref('Premiums');
		$all_paid_noncommissioned_preimums->addCondition('Paid',true);
		$all_paid_noncommissioned_preimums->addCondition('AgentCommissionSend',false);

		$commission = 0;

		foreach($all_paid_noncommissioned_preimums as $junk){
			$commission = $commission + ($all_paid_noncommissioned_preimums['Amount'] * $all_paid_noncommissioned_preimums['AgentCommissionPercentage'] / 100.00);
			$all_paid_noncommissioned_preimums['AgentCommissionSend'] = true;
			$all_paid_noncommissioned_preimums->saveAndUnload();			
		}

		$tds_percentage = $account->ref('agent_id')->ref('member_id')->get('PanNo')?10:20;
		$tds = $commission * $tds_percentage / 100;


		$account = $this->ref('account_id');

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT, $account->ref('branch_id'), $on_date, "RD Premium Commission ".$account['AccountNumber'], null, array('reference_account_id'=>$account->id));
		
		$transaction->addDebitAccount($account->ref('branch_id')->get('Code') . SP . COMMISSION_PAID_ON . $account['scheme_name'] , $commission);
		
		$transaction->addCreditAccount($account->ref('agent_id')->ref('account_id'), $commission -$tds);
		$transaction->addCreditAccount($account['branch_code'].SP.BRANCH_TDS_ACCOUNT, $tds);
		
		$transaction->execute();

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

    function reAdjustPaidValues($on_date=null){

    	if(!$on_date) $on_date=$this->api->now;

        // $CI->db->query("UPDATE jos_xpremiums SET Paid=0 WHERE accounts_id = $this->accounts_id");
    	// reset all paid  = 0 first
    	$this->add('Model_Premium')
    		->addCondition('account_id',$this['account_id'])
    		->_dsql()
    		->set('Paid',0)
    		->update();

        // $due_and_paid_query = $CI->db->query("
        // 		SELECT 
        // 			GROUP_CONCAT(EXTRACT(YEAR_MONTH FROM DueDate)) DueArray, 
        // 			GROUP_CONCAT(EXTRACT(YEAR_MONTH FROM PaidOn)) PaidArray 
        // 		FROM 
        // 			jos_xpremiums 
        // 		WHERE 
        // 			accounts_id = $this->accounts_id 
        // 		AND 
        // 			(PaidOn < '$tilldate' OR DueDate < '$tilldate') 
        // 		ORDER BY id
        // 		")->row();

        $due_and_paid_query = $this->api->db->dsql()
        							->table('premiums')
        							->field($this->dsql()->expr('GROUP_CONCAT(EXTRACT(YEAR_MONTH FROM DueDate)) DueArray'))
        							->field($this->dsql()->expr('GROUP_CONCAT(EXTRACT(YEAR_MONTH FROM PaidOn)) PaidArray'))
									->where('account_id',$this['account_id'])
									->where("(PaidOn < '$tilldate' OR DueDate < '$tilldate')")
									->order('id')
									->getHash()
									;						


        $due_array=explode(",",$due_and_paid_query['DueArray']);
        $paid_array=explode(",",$due_and_paid_query['PaidArray']);

        
        $account_premiums=$this->add('Model_Premium');
        $account_premiums
        ->_dsql()
        ->where('account_id',$this['account_id'])
        ->where("(PaidOn <= '$tilldate' OR DueDate <= '$tilldate')")
        ->order('id');

        $i=0;
        foreach($account_premiums as $p){
            // echo "setting";
            $paid=0;
            for($j=0;$j<=$i;$j++){
                if(isset($paid_array[$j]) AND $paid_array[$j] <= $due_array[$i]) $paid++;
                // if(isset($paid_array[$j]) AND $j==0 AND $paid_array[$j] > $due_array[$i]) $paid++;
            }
            $account_premiums['Paid']= $paid;
            $account_premiums->save();
            $i++;
        }

    }

}