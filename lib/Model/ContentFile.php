<?php


class Model_ContentFile extends Model_Table {
	public $table = "content_files";
	public $file_path = 'templates/contents/';

	public $works_on='Account'; // or Member
	
	function init(){
		parent::init();

		$this->addField('name');
		// $this->addField('caption');
		$this->addField('account_type')->enum(array_merge(explode(",", ACCOUNT_TYPES),['Base']));

		$this->addHook('beforeSave',$this);
		$this->addHook('beforeDelete',$this);
	}

	function beforeSave(){
		$this['name'] = $this->app->normalizeName($this['name']);
		if(!file_exists($this->getPath())){
			touch($this->getPath());
		}
	}

	function beforeDelete(){
		if(file_exists($this->getPath())){
			unlink($this->getPath());
		}
	}

	function getPath(){
		return $this->file_path.'/'.$this['name'];
	}

	function getContent(){
		return file_get_contents($this->getPath());
	}

	function getAccountModel($id=null){
		$type="";
		if($this['account_type'] && $this['account_type'] != 'Base' ){
			$type='_'.$this['account_type'];
		}

		$member_model = $this->add('Model_Member');
		$member_fields = $member_model->getActualFields();
		
		$model = $this->add('Model_Account'.$type);

		$model->addHook('afterLoad',function($m){
			foreach ($m->getActualFields() as $fld) {
				if($m->getElement($fld)->type() == 'boolean'){
					$m[$fld] = $m[$fld]?"Yes":"No";
				}
			}
		});

		$model->addExpression('logo')->set(function($m,$q){
			return '"<img src=\"templates/images/logo.jpg\" style=\"width:100%; max-width:100%\"/>"';
		});

		$model->addExpression('logo_src')->set(function($m,$q){
			return "'templates/images/logo.jpg'";
		});

		$model->addExpression('emi_count')->set(function($m,$q){
			$p_m=$m->refSQL('Premium');
						// ->addCondition('PaidOn','<>',null);
			// if($from_date)
			// 	$p_m->addCondition('DueDate','>=',$from_date);
			// if($to_date)
			// 	$p_m->addCondition('DueDate','<',$m->api->nextDate($to_date));
			return $p_m->count();
		})->sortable(true);

		$model->addExpression('paid_premium_count')->set(function($m,$q){
			$p_m=$m->refSQL('Premium')
						->addCondition('PaidOn','<>',null);
			// if($from_date)
			// 	$p_m->addCondition('DueDate','>=',$from_date);
			// if($to_date)
			// 	$p_m->addCondition('DueDate','<',$m->api->nextDate($to_date));
			return $p_m->count();
		})->sortable(true);

		$model->addExpression('due_premium_count')->set(function($m,$q){
			$p_m = $m->refSQL('Premium')
						->addCondition('PaidOn',null);
			// if($from_date)
			// 	$p_m->addCondition('DueDate','>=',$from_date);
			// if($to_date)
			// 	$p_m->addCondition('DueDate','<',$m->api->nextDate($to_date));
			return $p_m->count();
		});

		$model->addExpression('overdue_premium_count')->set(function($m,$q){
			$p_m = $m->refSQL('Premium')
						->addCondition('PaidOn',null);
			// if($from_date)
			// 	$p_m->addCondition('DueDate','>=',$from_date);
			// if($to_date)
			$p_m->addCondition('DueDate','<',$m->api->nextDate($this->app->today));
			return $p_m->count();
		});

		$model->addExpression('due_premium_amount')->set(function($m,$q){
			$p_m = $m->refSQL('Premium')
						->addCondition('PaidOn',null);
			// if($from_date)
			// 	$p_m->addCondition('DueDate','>=',$from_date);
			// if($to_date)
			// 	$p_m->addCondition('DueDate','<',$m->api->nextDate($to_date));
			return $p_m->sum('Amount');
		});

		$model->addExpression('overdue_premium_amount')->set(function($m,$q){
			$p_m = $m->refSQL('Premium')
						->addCondition('PaidOn',null);
			// if($from_date)
			// 	$p_m->addCondition('DueDate','>=',$from_date);
			// if($to_date)
			$p_m->addCondition('DueDate','<',$m->api->nextDate($this->app->today));
			return $p_m->sum('Amount');
		});

		$model->addExpression('due_date')->set(function($m,$q){
			$t = $m->refSQL('Premium')->setLimit(1);
			return $q->expr("DAY([0])",array($t->fieldQuery('DueDate')));
			return "'due_premium_count'";
		});

		$model->addExpression('last_premium')->set(function($m,$q){
			return $m->RefSQL('Premium')->setOrder('id','desc')->setLimit(1)->fieldQuery('DueDate');
			return "'last_premium'";
		});

		$model->addExpression('emi_amount')->set(function($m,$q){
			return $m->RefSQL('Premium')->setOrder('id','desc')->setLimit(1)->fieldQuery('Amount');
			return "'emi_amount'";
		});

		$model->addExpression('due_panelty')->set(function($m,$q){
			$trans_type = $this->add('Model_TransactionType')->tryLoadBy('name',TRA_PENALTY_ACCOUNT_AMOUNT_DEPOSIT);
			
			$tr_m = $m->add('Model_TransactionRow',array('table_alias'=>'due_panelty_tr'));
			$tr_m->addCondition('transaction_type_id',$trans_type->id); 
			$tr_m->addCondition('account_id',$q->getField('id'));
			// $tr_m->addCondition('created_at','>=',$from_date);
			// $tr_m->addCondition('created_at','<',$this->app->nextDate($to_date));

			return $tr_m->sum('amountDr');

			// Previously this was running, and was including un entered amount also, but
			// this was changed as per request ... 
			// Reason, old accounts was not included in penalty
			$p_m = $m->refSQL('Premium');
			// if($from_date)
			// 	$p_m->addCondition('DueDate','>=',$from_date);
			// if($to_date)
			// 	$p_m->addCondition('DueDate','<',$m->api->nextDate($to_date));
			return $p_m->sum($m->dsql()->expr('IFNULL(PaneltyCharged,0)'));
		});

		$model->addExpression('other_charges')->set(function($m,$q){
			$tr_m = $m->add('Model_TransactionRow',array('table_alias'=>'other_charges_tr'));
			$tr_m->addCondition('transaction_type_id',[13, 46, 39]); // JV, TRA_VISIT_CHARGE, LegalChargeReceived
			$tr_m->addCondition('account_id',$q->getField('id'));
			return $tr_m->sum('amountDr');
		});

		$model->addExpression('other_received')->set(function($m,$q){
			$tr_m = $m->add('Model_TransactionRow',array('table_alias'=>'other_charges_tr'));
			$tr_m->addCondition('account_id',$q->getField('id'));
			$received = $tr_m->sum('amountCr');
			$premium_paid = $q->expr('([0]*[1])',[$m->getElement('paid_premium_count'),$m->getElement('emi_amount')]);
			return $q->expr('(IFNULL([0],0)-IFNULL([1],0))',[$received,$premium_paid]);
		});

		$model->addExpression('total_due_amount')->set(function($m,$q){
			return $q->expr('IFNULL([0],0)+IFNULL([1],0)+IFNULL([2],0)',[$m->getElement('due_premium_amount'),$m->getElement('due_panelty'),$m->getElement('other_charges')]);
		});

		$model->addExpression('total_overdue_amount')->set(function($m,$q){
			return $q->expr('IFNULL([0],0)+IFNULL([1],0)+IFNULL([2],0)',[$m->getElement('overdue_premium_amount'),$m->getElement('due_panelty'),$m->getElement('other_charges')]);
		});		

		$model->addExpression('member_sm_account')->set(function($m,$q){
			return  $this->add('Model_Account_SM',['table_alias'=>'sm_accounts'])->addCondition('member_id',$q->getField('member_id'))->setLimit(1)->fieldQuery('AccountNumber');
		});

		$model->addExpression('account_created_date')->set(function($m,$q){
			return  $q->expr('DATE([0])',[$m->getElement('created_at')]);
		});

		foreach ($member_fields as $mf) {
			if(in_array($mf, ['id'])) continue;
			$model->addExpression('member_'.$mf)->set(function($m,$q)use($mf){
				return $m->refSQL('member_id')->fieldQuery($mf);
			})->type($member_model->getElement($mf)->type());
		}

		foreach ($member_fields as $mf) {
			if(in_array($mf, ['id'])) continue;

			$model->addExpression('guarantor_'.$mf)->set(function($m,$q)use($mf){
				$gm = $this->add('Model_Member',['table_alias'=>'gm']);
				$ag_j = $gm->leftJoin('account_guarantors.member_id');
				$ag_j->addField('account_id');
				$gm->addCondition('account_id',$m->getField('id'));
				$gm->setOrder('created_at','desc');
				$gm->setLimit(1);
				return $gm->fieldQuery($mf);
			})->type($member_model->getElement($mf)->type());
		}

		$documents = $this->add('Model_DocumentAll');
		foreach ($documents as $dci=>$obj) {
			$model->addExpression($this->app->normalizeName($obj['name']))->set(function($m,$q)use($dci){
				return $m->refSQL('DocumentSubmitted')
						->addCondition('documents_id',$dci)
						->addCondition('accounts_id',$m->getField('id'))
						->fieldQuery('Description');
			});
		}

		if($id){

			$model->tryLoadBy('AccountNumber',$id);
			if(!$model->loaded()){
				$model->tryLoad($id);
			}
			if(!$model->loaded()){
				throw new \Exception("AccountNumber ". $id. ' Not found'. ($type?' Under '.$type:''), 1);
			}
		} 

		return $model;

	}

}