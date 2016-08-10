<?php



class Model_BS_scheme extends Model_Scheme{

	public $from_date=null;
	public $to_date=null;
	public $branch_id = null;

	function init(){
		parent::init();

		$this->addExpression('OpeningBalanceDr')->set(function($m,$q){
			$ledger = $m->add('Model_BS_Ledger');
			if($this->branch_id) $ledger->addCondition('branch_id',$this->branch_id);
			return $ledger->addCondition('scheme_id',$q->getField('id'))
						->sum($q->expr('IFNULL([0],0)',[$ledger->getElement('OpeningBalanceDr')]));
		});
		$this->addExpression('OpeningBalanceCr')->set(function($m,$q){
			$ledger = $m->add('Model_BS_Ledger');
			if($this->branch_id) $ledger->addCondition('branch_id',$this->branch_id);
			return $ledger->addCondition('scheme_id',$q->getField('id'))
						->sum($q->expr('IFNULL([0],0)',[$ledger->getElement('OpeningBalanceCr')]));
		});

		$this->addExpression('PreviousTransactionsDr')->set(function($m,$q){
			return "'0'";
			$transaction =  $m->add('Model_BS_TransactionRow');
			if($this->branch_id) $transaction->addCondition('branch_id',$this->branch_id);
			return $transaction->addCondition('scheme_id',$q->getField('id'))
								->addCondition('created_at','<',$this->from_date)
								->sum($q->expr('IFNULL([0],0)',[$transaction->getElement('amountDr')]));
		});
		$this->addExpression('PreviousTransactionsCr')->set(function($m,$q){
			return "'0'";
			$transaction =  $m->add('Model_BS_TransactionRow');
			return $transaction->addCondition('scheme_id',$q->getField('id'))
								->addCondition('created_at','<',$this->from_date)
								->sum($q->expr('IFNULL([0],0)',[$transaction->getElement('amountCr')]));
		});

		$this->addExpression('TransactionsDr')->set(function($m,$q){
			return "'0'";
			$transaction =  $m->add('Model_BS_TransactionRow');
			if($this->branch_id) $transaction->addCondition('branch_id',$this->branch_id);
			return $transaction->addCondition('scheme_id',$q->getField('id'))
								->addCondition('created_at','>=',$this->from_date)
								->addCondition('created_at','<',$this->app->nextDate($this->to_date))
								->sum($q->expr('IFNULL([0],0)',[$transaction->getElement('amountDr')]));
		});
		$this->addExpression('TransactionsCr')->set(function($m,$q){
			return "'0'";
			$transaction =  $m->add('Model_BS_TransactionRow');
			if($this->branch_id) $transaction->addCondition('branch_id',$this->branch_id);
			return $transaction->addCondition('scheme_id',$q->getField('id'))
								->addCondition('created_at','>=',$this->from_date)
								->addCondition('created_at','<',$this->app->nextDate($this->to_date))
								->sum($q->expr('IFNULL([0],0)',[$transaction->getElement('amountCr')]));
		});

		$this->addExpression('ClosingBalanceDr')->set(function($m,$q){
			return $q->expr('IFNULL([0],0)+IFNULL([1],0)+IFNULL([2],0)',[
					$m->getElement('OpeningBalanceDr'),
					$m->getElement('PreviousTransactionsDr'),
					$m->getElement('TransactionsDr')
				]);
		});
		$this->addExpression('ClosingBalanceCr')->set(function($m,$q){
			return $q->expr('IFNULL([0],0)+IFNULL([1],0)+IFNULL([2],0)',[
					$m->getElement('OpeningBalanceCr'),
					$m->getElement('PreviousTransactionsCr'),
					$m->getElement('TransactionsCr')
				]);
		});

	}
}