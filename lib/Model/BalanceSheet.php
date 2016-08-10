<?php
class Model_BalanceSheet extends Model_Table {
	var $table= "balance_sheet";
	function init(){
		parent::init();

		$this->addField('name')->mandatory(true);
		$this->addField('positive_side')->enum(array('LT','RT'))->mandatory(true);
		$this->addField('is_pandl')->type('boolean')->mandatory(true);
		$this->addField('show_sub')->enum(array('SchemeGroup','SchemeName','Accounts'))->mandatory(true);
		$this->addField('subtract_from')->enum(array('Cr','Dr'))->mandatory(true);
		$this->addField('order');

		$this->addHook('beforeDelete',$this);

		$this->hasMany('Scheme','balance_sheet_id');
		$this->hasMany('TransactionRow','transaction_row_id');
		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeDelete(){
		if($this->ref('Scheme')->count()->getOne() > 0)
			throw $this->exception('Balance Sheet Head Contains Scheme, Cannot Delete');
	}

	function getClosingBalance($on_date=null,$side='both',$forPandL=false,$branch=null,$from_date=null){
		return $this->getOpeningBalance($this->api->nextDate($on_date),$side,$forPandL,$branch,$from_date);
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
		$aj_ta = $account_join->table_alias;

		$scheme_join = $account_join->join('schemes','scheme_id');
		$scheme_join->addField('balance_sheet_id');

		$transaction_row->addCondition('balance_sheet_id',$this->id);
		$transaction_row->addCondition('created_at','<',$on_date);
		// $transaction_row->addCondition('ActiveStatus',true);
		// $transaction_row->addCondition('affectsBalanceSheet',false);

		// $transaction_row->_dsql()->where("(($aj_ta.ActiveStatus = 1 OR $aj_ta.affectsBalanceSheet = 1))");

		if($branch)
			$transaction_row->addCondition('branch_id',$branch->id);

		if($forPandL){
			$financial_start_date = $this->api->getFinancialYear($from_date,'start');
			$transaction_row->addCondition('created_at','>=',$financial_start_date);
		}

		$transaction_row->_dsql()->del('fields')->field('SUM(amountDr) sdr')->field('SUM(amountCr) scr');
		$result = $transaction_row->_dsql()->getHash();

		// Opening balance SUM Now
		
		$account = $this->add('Model_Account',array('table_alias'=>'accounts'));
		$account->join('schemes','scheme_id')->addField('balance_sheet_id');
		$account->addCondition('balance_sheet_id',$this->id);

		if($branch)
			$account->addCondition('branch_id',$branch->id);

		$account->_dsql()->del('fields')->field('SUM(OpeningBalanceCr) opcr')->field('SUM(OpeningBalanceDr) opdr');
		// $account->_dsql()->where("((accounts.ActiveStatus = 1 OR accounts.affectsBalanceSheet = 1))");
		$result_op = $account->_dsql()->getHash();


		$cr = $result['scr'];
		if(!$forPandL) $cr = $cr + $result_op['opcr'];
		if(strtolower($side) =='cr') return $cr;

		$dr = $result['sdr'];		
		if(!$forPandL) $dr = $dr + $result_op['opdr'];
		if(strtolower($side) =='dr') return $dr;

		return array('CR'=>$cr,'DR'=>$dr,'cr'=>$cr,'dr'=>$dr,'Cr'=>$cr,'Dr'=>$dr);
	}

	function getPandLClosingValue($from_date,$to_date, $branch){

		$to_date = $this->api->nextDate($to_date);

		$transaction_row=$this->add('Model_TransactionRow');
		
		$account_join = $transaction_row->join('accounts','account_id');
		$account_join->addField('ActiveStatus');
		$account_join->addField('affectsBalanceSheet');
		$aj_ta = $account_join->table_alias;

		$scheme_join = $account_join->join('schemes','scheme_id');
		$scheme_join->addField('balance_sheet_id');

		$head_join = $scheme_join->join('balance_sheet','balance_sheet_id');
		$head_join->addField('is_pandl');

		$transaction_row->addCondition('is_pandl',1);
		$transaction_row->addCondition('created_at','<',$to_date);
		
		if($branch)
			$transaction_row->addCondition('branch_id',$branch->id);

		$transaction_row->addCondition('created_at','>=',$from_date);

		$transaction_row->_dsql()->where("(($aj_ta.ActiveStatus = 1 OR $aj_ta.affectsBalanceSheet = 1))");


		$transaction_row->_dsql()->del('fields')->field('SUM(amountDr) sdr')->field('SUM(amountCr) scr');
		$result = $transaction_row->_dsql()->getHash();
		return $result;
	}


	// function getBalanceByAccounts($on_date=null,$forPandL=false,$branch=null){
	// 	if(!$on_date) $on_date = '1970-01-02';
		
	// 	$transaction_row=$this->add('Model_TransactionRow');
	// 	// $transaction_join=$transaction_row->join('transactions.id','transaction_id');
	// 	// $transaction_join->addField('transaction_date','created_at');
		
	// 	$account_join = $transaction_row->join('accounts','account_id');
	// 	$account_join->addField('scheme_id');
	// 	$account_join->addField('AccountActiveStatus','ActiveStatus');
	// 	$account_join->addField('affectsBalanceSheet');
	// 	$aj_ta = $account_join->table_alias;

	// 	$scheme_join = $account_join->join('schemes','scheme_id');
	// 	$scheme_join->addField('SchemeType');
	// 	$scheme_join->addField('SchemeGroup');
	// 	$scheme_join->addField('balance_sheet_id');

	// 	$head_join = $scheme_join->join('balance_sheet','balance_sheet_id');
	// 	$head_join->addField('subtract_from');

	// 	$transaction_row->addCondition('created_at','<',$on_date);
	// 	$transaction_row->addCondition('balance_sheet_id',$underHead->id);
	// 	$transaction_row->_dsql()->where("($aj_ta.ActiveStatus=1 OR $aj_ta.affectsBalanceSheet=1)");

	// 	if($branch)
	// 		$transaction_row->addCondition('branch_id',$branch->id);

	// 	if($forPandL){
	// 		$financial_start_date = $this->api->getFinancialYear($on_date,'start');
	// 		$transaction_row->addCondition('created_at','>=',$financial_start_date);
	// 	}

	// 	// SPECIAL GROUP BY CONDITION
	// 	// $transaction_row->addCondition($groupByField,$groupBy);
		
	// 	$transaction_row->_dsql()->del('fields')
	// 		->field('SUM(amountDr) sDr')
	// 		->field('SUM(amountCr) sCr')
	// 		->field('(SUM(amountDr) - SUM(amountCr)) amt')
	// 		->field('subtract_from')
	// 		->field($groupByField)
	// 		->group($groupByField);

	// 	$results = $transaction_row->_dsql()->get();

	// 	// Opening balance SUM Now
		
	// 	$account = $this->add('Model_Account');
	// 	$scheme_join = $account->join('schemes','scheme_id');
	// 	$head_join = $scheme_join->join('balance_sheet','balance_sheet_id');

	// 	$scheme_join->addField($groupByField);
	// 	$head_join->addField('subtract_from');


	// 	if($branch)
	// 		$account->addCondition('branch_id',$branch->id);

	// 	// SPECIAL GROUP BY CONDITION
	// 	// $account->addCondition($groupByField,$groupBy);

	// 	$account->_dsql()->del('fields')
	// 		->field('SUM(OpeningBalanceCr) opCr')
	// 		->field('SUM(OpeningBalanceDr) opDr')
	// 		->field('subtract_from')
	// 		->field($scheme_join->table_alias.'.'.$groupByField)
	// 		->group($groupByField)
	// 		;

	// 	$results_op = $account->_dsql()->get();

	// 	$return_array=array();

	// 	foreach ($results as $r) {
	// 		$_subtract_from = $r['subtract_from'];
	// 		$_subtract_what = $r['subtract_from']=='Cr'?'Dr':'Cr';

	// 		$amt_cr = $r['sCr'];
	// 		$amt_dr = $r['sDr'];
	// 		$amt = $r['s'.$_subtract_from] - $r['s'.$_subtract_what];

	// 		foreach ($results_op as $a_o) {
	// 			if($a_o['SchemeGroup'] == $r['SchemeGroup']){
	// 				$op_amount = $a_o['op'.$_subtract_from] - $a_o['op'.$_subtract_what];
	// 				$amt += $op_amount;
	// 				$amt_cr += $a_o['opcr'];
	// 				$amt_dr += $a_o['opdr'];
	// 			}
	// 		}



	// 		if($amt != 0)
	// 			$return_array[] = array('id'=>$r['SchemeGroup'],'SchemeGroup'=>$r['SchemeGroup'],'Amount'=>$amt/*. ($amt_dr > $amt_cr ? ' Dr':' Cr')*/);
	// 	}
	// 	return $return_array;
	// }

	// function digIn($groupBy,$on_date,$condition=null, $forPandL=false, $from_date=null, $branch=null){
		
	// 	$allowed_array=array('BalanceSheet','SchemeGroup','SchemeName','PAndLGroup','Account');
		
	// 	if(!in_array($groupBy, $allowed_array))
	// 		throw $this->exception('Group By must be one of '. print_r($allowed_array,true), 'ValidityCheck')->setField('FieldName');

	// 	if(!$forPandL and $from_date )
	// 		throw $this->exception('from_date must be specified only for panl');

	// 	$transaction_row=$this->add('Model_TransactionRow');
		
	// 	$account_join = $transaction_row->join('accounts','account_id');
	// 	$account_join->addField('ActiveStatus');
	// 	$account_join->addField('affectsBalanceSheet');
	// 	$aj_ta = $account_join->table_alias;

	// 	$scheme_join = $account_join->join('schemes','scheme_id');
	// 	$scheme_join->addField('balance_sheet_id');
	// 	$sj_ta = $scheme_join->table_alias;

	// 	$balance_sheet_join = $scheme_join->join('balance_sheet','balance_sheet_id');
	// 	$balance_sheet_join->addField('positive_side');
	// 	$balance_sheet_join->addField('subtract_from');
	// 	$bsj_ta = $balance_sheet_join->table_alias;

	// 	$transaction_row->_dsql()->field('SUM(amountDr) sdr')->field('SUM(amountCr) scr');

	// 	if($branch)
	// 		$transaction_row->addCondition($transaction_row->table_alias.'.branch_id',$branch->id);

	// 	if($condition){
	// 		$transaction_row->_dsql()->where("($condition)");
	// 	}

	// 	$transaction_row->_dsql()->where("(($aj_ta.ActiveStatus = 1 OR $aj_ta.affectsBalanceSheet = 1))");
	// 	$transaction_row->addCondition('created_at','<',$on_date);

	// 	$transaction_row->_dsql()->group($groupBy);

	// 	$transaction_array = $transaction_row->getHash();


	// 	// Opening balance SUM Now
		
	// 	$account = $this->add('Model_Account');
	// 	$scheme_join = $account->join('schemes','scheme_id');
	// 	$scheme_join->addField('balance_sheet_id');
	// 	$sj_ta = $scheme_join->table_alias;

	// 	$balance_sheet_join = $scheme_join->join('balance_sheet','balance_sheet_id');
	// 	$balance_sheet_join->addField('positive_side');
	// 	$balance_sheet_join->addField('subtract_from');
	// 	$bsj_ta = $balance_sheet_join->table_alias;

	// 	if($branch)
	// 		$account->addCondition('branch_id',$branch->id);

	// 	$account->_dsql()->del('fields')->field('SUM(OpeningBalanceCr) opcr')->field('SUM(OpeningBalanceDr) opdr');
	// 	$account->_dsql()->group($groupBy);

	// 	$result_op = $account->_dsql()->getHash();

	// 	$result=array();
	// 	foreach ($variable as $key => $value) {
	// 		# code...
	// 	}

	// }

}