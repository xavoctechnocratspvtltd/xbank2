<?php


class Model_ShareHistory extends Model_Table {
	public $table ='share_history';

	function init(){
		parent::init();

		$this->hasOne('Member','member_id')->display(['form'=>'autocomplete/Basic'])->sortable(true);
		$this->hasOne('Share','share_id')->display(['form'=>'autocomplete/Basic'])->sortable(true);
		$this->hasOne('ShareCertificate','share_certificate_id')->display(['form'=>'autocomplete/Basic'])->sortable(true);
		$this->addField('from_date')->type('datetime')->defaultValue($this->app->now)->sortable(true);
		$this->addField('final_to_date')->type('datetime')->caption('Orig To Date')->sortable(true);

		$this->addExpression('to_date')->set(function($m,$q){
			return $q->expr('IFNULL([0],"[1]")',[$m->getElement('final_to_date'),$this->app->now]);
		})->type('datetime')->sortable(true);

		$this->setOrder('from_date','desc');

		$this->add('dynamic_model/Controller_AutoCreator');
	}

	// function createNew($no_of_shares,$to_member_id=null,$start_no=null){
	// 	if(!$start_no) {
	// 		$start_no = ($this->add('Model_Share')->setOrder('no','desc')->tryLoadAny()->get('no') + 1);
	// 	}

	// 	$status='Available';
	// 	if($to_member_id) $status='Issued';

	// 	for ($i=0; $i < $no_of_shares; $i++) { 
	// 		$new_m = $this->add('Model_Share');
	// 		$new_m['no'] = $start_no;
	// 		$new_m['status'] = $status;
	// 		if($to_member_id) $new_m['current_member_id'] = $to_member_id;
	// 		$new_m->save();

	// 		$start_no++;
	// 	}
	// }

	// function transfer($share_nos=[],$from_member,$to_member){

	// }

}