<?php
class Model_Branch extends Model_Table {
	var $table= "jos_xbranch";
	function init(){
		parent::init();

		$this->addField('name')->display(array('grid'=>'grid/inline'));
		$this->addField('Address');
		$this->addField('Code');
		$this->addField('PerformClosings')->type('boolean')->defaultValue(true)->display(array('grid'=>'grid/inline'));
		$this->addField('SendSMS')->type('boolean')->defaultValue(true);
		$this->addField('published')->type('boolean')->defaultValue(true);

		$this->hasMany('Staff','branch_id');

		$this->addHook('afterInsert',$this);

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function afterInsert($branch,$id){
		$new_branch = $this->add('Model_Branch')->load($id);
		$staff=$new_branch->ref('Staff');
		$staff['name']=$this->api->normalizeName($new_branch['name'].' admin');
		$staff['password']=rand(1000,9999);
		$staff['AccessLevel']=80;
		$staff->save();
	}

	function getFinancialYear($date=null,$start_end = 'both'){
		if(!$date) $date = $this->api->now;
		$month = date('m',strtotime($date));
		$year = date('Y',strtotime($date));
		if($month >=1 AND $month <=3  ){
			$f_year_start = $year-1;
			$f_year_end = $year;
		}
		else{
			$f_year_start = $year;
			$f_year_end = $year+1;
		}

		if(strpos($start_end, 'start') !==false){
			return $f_year_start.'-04-01';
		}
		if(strpos($start_end, 'end') !==false){
			return $f_year_end.'-03-31';
		}

		return array(
				'start_date'=>$f_year_start.'-04-01',
				'end_date'=>$f_year_end.'-03-31'
			);

	}

	function newVoucherNumber($branch_id=null, $transaction_date=null){

		if(!$branch_id) $branch_id = $this->id;
		if(!$transaction_date) $transaction_date = $this->api->today;

		$f_year = $this->getFinancialYear($transaction_date);


		$transaction_model = $this->add('Model_Transaction');
		$transaction_model->addCondition('branch_id',$branch_id);
		$transaction_model->addCondition('created_at','>=',$f_year['start_date']);
		$transaction_model->addCondition('created_at','<=',$this->api->nextDate($f_year['end_date'])); // ! important next date

		$transaction_model->max('voucher_no');

		$max_voucher = $transaction_model->getOne();
		
		return $max_voucher+1;

	}
}