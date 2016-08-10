<?php


class Model_BS_BalanceSheet extends Model_BalanceSheet{

	public $from_date='1970-01-01';
	public $to_date='2017-03-31';
	public $branch_id = null;

	public $balance_sheet_id=null;

	function init(){
		parent::init();

		$this->addExpression('OpeningBalanceDr')->set(function($m,$q){
			$query = '(select sum(IFNULL(a.OpeningBalanceDr,0))
							from accounts a
							join schemes s on a.scheme_id =s.id 
							where s.balance_sheet_id = [0]
							';
			
			if($this->branch_id) $query .= ' and a.branch_id = ' . $this->branch_id;
			$query .= ' )';

			return $q->expr($query,[$this->balance_sheet_id,$this->from_date]);
		});
		$this->addExpression('OpeningBalanceCr')->set(function($m,$q){

			$query = '(select sum(IFNULL(a.OpeningBalanceCr,0))
							from accounts a
							join schemes s on a.scheme_id =s.id 
							where s.balance_sheet_id = [0]
							';
			if($this->branch_id) $query .= ' and a.branch_id = ' . $this->branch_id;
				
			$query .= ' )';
			return $q->expr($query,[$this->balance_sheet_id,$this->from_date]);
		});

		$this->addExpression('PreviousTransactionsDr')->set(function($m,$q){
			// return '"0"';
			$query = '(select sum(tr.amountDr)
							from transaction_row tr 
							join accounts a on tr.account_id=a.id
							where tr.balance_sheet_id = [0]
							and tr.created_at < "[1]"
							';
			if($this->branch_id) $query .= ' and a.branch_id = ' . $this->branch_id;
				
			$query .= ' )';

			return $q->expr($query,[$this->balance_sheet_id,$this->from_date]);
		});
		$this->addExpression('PreviousTransactionsCr')->set(function($m,$q){
			$query = '(select sum(tr.amountCr)
							from transaction_row tr 
							join accounts a on tr.account_id=a.id
							where tr.balance_sheet_id = [0]
							and tr.created_at < "[1]"
							';
			if($this->branch_id) $query .= ' and a.branch_id = ' . $this->branch_id;
				
			$query .= ' )';
						
			return $q->expr($query,[$this->balance_sheet_id,$this->from_date]);

		});

		$this->addExpression('TransactionsDr')->set(function($m,$q){
			$query = '(select sum(tr.amountDr)
							from transaction_row tr 
							join accounts a on tr.account_id=a.id
							where tr.balance_sheet_id = [0]
							and tr.created_at >= "[1]" and tr.created_at < "[2]"
							';
			if($this->branch_id) $query .= ' and a.branch_id = ' . $this->branch_id;
				
			$query .= ' )';
					
			return $q->expr($query,[$this->balance_sheet_id,$this->from_date, $this->app->nextDate($this->to_date)]);

		});
		$this->addExpression('TransactionsCr')->set(function($m,$q){
			$query = '(select sum(tr.amountCr)
							from transaction_row tr 
							join accounts a on tr.account_id=a.id
							where tr.balance_sheet_id = [0]
							and tr.created_at >= "[1]" and tr.created_at < "[2]"
							';
			if($this->branch_id) $query .= ' and a.branch_id = ' . $this->branch_id;
				
			$query .= ' )';
					
			return $q->expr($query,[$this->balance_sheet_id,$this->from_date, $this->app->nextDate($this->to_date)]);

		});

		// $this->addExpression('ClosingBalanceDr')->set(function($m,$q){
		// 	return $q->expr('
		// 		IF(is_pandl=0,
		// 		IFNULL([0],0)+IFNULL([1],0)+IFNULL([2],0),
		// 		IFNULL([2],0))',[
		// 			$m->getElement('OpeningBalanceDr'),
		// 			$m->getElement('PreviousTransactionsDr'),
		// 			$m->getElement('TransactionsDr')
		// 		]);
		// });
		// $this->addExpression('ClosingBalanceCr')->set(function($m,$q){
		// 	return $q->expr('
		// 		IF(is_pandl=0,
		// 		IFNULL([0],0)+IFNULL([1],0)+IFNULL([2],0),
		// 		IFNULL([2],0))',[
		// 			$m->getElement('OpeningBalanceCr'),
		// 			$m->getElement('PreviousTransactionsCr'),
		// 			$m->getElement('TransactionsCr')
		// 		]);
		// })->type('money');

		// $this->addExpression('is_left')->set(function($m,$q){
		// 	return $q->expr('IF(([0]-[1])>=0 AND [2]="LT",1,0)',[
		// 			$m->getElement('ClosingBalanceDr'),
		// 			$m->getElement('ClosingBalanceCr'),
		// 			$m->getElement('positive_side'),

		// 		]);
		// })->type('money');

		$this->setOrder('order');

	}
}