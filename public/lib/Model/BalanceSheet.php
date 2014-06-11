<?php
class Model_BalanceSheet extends Model_Table {
	var $table= "balance_sheet";
	function init(){
		parent::init();

		$this->addField('name');
		$this->addField('positive_side')->enum(array('LT','RT'));
		$this->addField('is_pandl')->type('boolean');
		$this->addField('show_sub');
		$this->addField('subtract_from');
		$this->addField('order');

		$this->hasMany('Scheme','balance_sheet_id');
		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function getOpeningBalance($on_date=null,$side='both',$forPandL=false,$branch=null,$from_date=null) {

		if(!$this->loaded()) throw $this->exception('Balance Sheet Head Must be Loaded');
		if(!$on_date) $on_date = '1970-01-01';
		if(!$this->loaded()) throw $this->exception('Model Must be loaded to get opening Balance','Logic');
		if(!$forPandL and $from_date )
			throw $this->exception('from_date must be specified only for panl');

		$transaction_row=$this->add('Model_TransactionRow');
		// $transaction_join=$transaction_row->join('transactions.id','transaction_id');
		// $transaction_join->addField('transaction_date','created_at');
		
		$account_join = $transaction_row->join('accounts','account_id');
		$account_join->addField('ActiveStatus');
		$account_join->addField('affectsBalanceSheet');

		$scheme_join = $account_join->join('schemes','scheme_id');
		$scheme_join->addField('balance_sheet_id');

		$transaction_row->addCondition('balance_sheet_id',$this->id);
		$transaction_row->addCondition('created_at','<',$on_date);
		$transaction_row->addCondition('ActiveStatus',true);
		$transaction_row->addCondition('affectsBalanceSheet',false);

		if($branch)
			$transaction_row->addCondition('branch_id',$branch->id);

		if($forPandL){
			$financial_start_date = $this->api->getFinancialYear($from_date,'start');
			$transaction_row->addCondition('created_at','>=',$financial_start_date);
		}

		$transaction_row->_dsql()->del('fields')->field('SUM(amountDr) sdr')->field('SUM(amountCr) scr');
		$result = $transaction_row->_dsql()->getHash();

		// Opening balance SUM Now
		
		$account = $this->add('Model_Account');
		$account->join('schemes','scheme_id')->addField('balance_sheet_id');
		$account->addCondition('balance_sheet_id',$this->id);

		if($branch)
			$account->addCondition('branch_id',$branch->id);

		$account->_dsql()->del('fields')->field('SUM(OpeningBalanceCr) opcr')->field('SUM(OpeningBalanceDr) opdr');
		$result_op = $account->_dsql()->getHash();


		$cr = $result['scr'];
		if($forPandL OR true) $cr = $cr + $result_op['opcr'];
		if(strtolower($side) =='cr') return $cr;

		$dr = $result['sdr'];		
		if($forPandL OR true) $dr = $dr + $result_op['opdr'];
		if(strtolower($side) =='dr') return $dr;

		return array('CR'=>$cr,'DR'=>$dr,'cr'=>$cr,'dr'=>$dr,'Cr'=>$cr,'Dr'=>$dr);
	}
}