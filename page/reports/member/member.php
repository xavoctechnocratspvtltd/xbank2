<?php

class page_reports_member_member extends Page {
	public $title='Member Report';

	function page_index(){
		// parent::init();

		$form = $this->add('Form');
		$type_array = [];
		foreach (explode(",", MEMBER_TYPES) as $key => $value) {
			$type_array[$value] = $value; 
		}

		$form->addField('Dropdown','type')->setValueList(array_merge(["All"=>"All"],$type_array));
		$bank_field = $form->addField('Dropdown','bank')->setEmptyText('All Banks');
		$bank_field->setModel('Bank');
		$form->addField('dropdown','status')->setValueList(array('all'=>'All','0'=>'InActive','1'=>'Active'));
		$form->addField('Line','pan_no');
		$form->addField('Line','adhar_no');
		
		$form->addField('Line','name');
		$form->addField('Line','mobile');
		$form->addField('Line','landmark');
		$form->addField('Line','address');
		$form->addField('Checkbox','form_61_is_submitted');

		$form->addSubmit('Get List');
		
		$member_model=$this->add('Model_Member');
		$member_model->setOrder('created_at','desc');
		$member_model->addExpression('bank_a_id')->set($member_model->refSQL('bankbranch_a_id')->fieldQuery('bank_id'));
		$member_model->addExpression('bank_b_id')->set($member_model->refSQL('bankbranch_b_id')->fieldQuery('bank_id'));
		
		$grid=$this->add('Grid');
		if($_GET['filter']){
			$this->api->stickyGET('filter');
			$this->api->stickyGET('status');
			$this->api->stickyGET('type');
			$this->api->stickyGET('bank');
			$this->api->stickyGET('pan_no');
			$this->api->stickyGET('adhar_no');
			$this->api->stickyGET('name');
			$this->api->stickyGET('address');
			$this->api->stickyGET('mobile');
			$this->api->stickyGET('landmark');
			$this->api->stickyGET('form_61_is_submitted');
			
			if($_GET['status'] !=='all')
				$member_model->addCondition('is_active',$_GET['status']==0?false:true);
			
			if($_GET['type']){
				$this->api->stickyGET('type');
				if($_GET['type'] != "All"){
					$member_model->addCondition('memebr_type',$_GET['type']);
				}
			}
			if($_GET['bank']){
				$this->api->stickyGET('bank');
				$member_model->addCondition([['bank_a_id',$_GET['bank']],['bank_b_id',$_GET['bank']]]);
			}

			if($_GET['pan_no']){
				$this->app->stickyGET('pan_no');
				$member_model->addCondition('PanNo',$_GET['pan_no']);
			}

			if($_GET['adhar_no']){
				$this->app->stickyGET('adhar_no');
				$member_model->addCondition('AdharNumber',$_GET['adhar_no']);
			}

			if($_GET['name']){
				$member_model->addCondition('name','like','%'.$_GET['name'].'%');
			}

			if($_GET['address']){
				$member_model->addCondition('PermanentAddress','like','%'.$_GET['address'].'%');
			}

			if($_GET['mobile']){
				$member_model->addCondition('PhoneNos','like','%'.$_GET['mobile'].'%');
			}

			if($_GET['landmark']){
				$member_model->addCondition('landmark','like','%'.$_GET['landmark'].'%');
			}

			// if($_GET['form_61_is_submitted']){
				// form 60 is submitted in current financial year
				// $fy = $this->app->getFinancialYear();
				// $member_model->addCondition('created_at','>=',$fy['start_date']);
				// $member_model->addCondition('created_at','<',$this->app->nextDate($fy['end_date']));
				// $member_model->addCondition('FilledForm60',true);
			// }

		}else{
			$member_model->addCondition('id',-1);
		}
		// $grid->add('H3',null,'grid_buttons')->set('Member Repo As On '. date('d-M-Y',strtotime($till_date))); 
		$grid->setModel($member_model,array('member_no','branch','gender','name','FatherName','RelationWithFatherField','CurrentAddress','landmark','tehsil','city','PhoneNos','created_at','is_active','is_defaulter','doc_thumb_url','sig_image_id'));
		$grid->addPaginator(500);
		$grid->addQuickSearch(array('member_no','name','PhoneNos'));
		$self=$this;

		$self=$this;
		$grid->addColumn('comment');
		$grid->addMethod('format_comment',function($g,$f)use($self){
			// throw new \Exception($g->model->id, 1);
			$comment_model=$self->add('Model_Comment');//->load($g->model->id);
			$comment_model->addCondition('member_id',$g->model->id);
			$comment_model->setOrder('created_at','desc');
			$comment_model->tryLoadAny();
			$narration=$comment_model->get('narration');
			$g->current_row[$f]=$narration;
		});

		$grid->addMethod('init_image2',function($g){
			$this->js('click')->_selector('img')->univ()->frameURL('IMAGE',[$this->app->url('image'),'image_id'=>$this->js()->_selectorThis()->data('sig-image-id') ]);
		});

		$grid->addMethod('format_image2',function($g,$f)use($self){
			$g->current_row_html[$f]=$g->model['doc_thumb_url']?'<img width="100px;" src="'.$g->model['doc_thumb_url'].'" data-sig-image-id="'.$g->model['sig_image_id'].'"/>':'';
		});
		
		$grid->addFormatter('comment','comment');
		$grid->addFormatter('landmark','wrap');
		$grid->addFormatter('doc_thumb_url','image2');
		

		
		$grid->addColumn('expander','details');
		$grid->addColumn('expander','accounts');
		$grid->addColumn('expander','guarantor_in');
		$grid->removeColumn('sig_image_id');

		// $js=array(
		// 	$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
		// 	$this->js()->_selector('#header')->toggle(),
		// 	$this->js()->_selector('#footer')->toggle(),
		// 	$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
		// 	$this->js()->_selector('.atk-form')->toggle(),
		// 	);

		// $grid->js('click',$js);

		if($form->isSubmitted()){
			$send = array('pan_no'=>$form['pan_no'],'adhar_no'=>$form['adhar_no'],'type'=>$form['type'],'bank'=>$form['bank'],'status'=>$form['status'],'name'=>$form['name'],'address'=>$form['address'],'landmark'=>$form['landmark'],'mobile'=>$form['mobile'],'form_61_is_submitted'=>$form['form_61_is_submitted'],'filter'=>1);
			$grid->js()->reload($send)->execute();

		}	
	}

	function page_details(){
		$this->api->stickyGET('members_id');
		$member_model=$this->add('Model_Member');
		$member_model->addCondition('id',$_GET['members_id']);

		$grid=$this->add('Grid');

		$extra_fields=array('branch_id','name','CurrentAddress','tehsil','city','PhoneNos','created_at','is_active','is_defaulter');
		foreach ($extra_fields as $key => $value) {
			$member_model->getElement($value)->system(true);
		}
		$grid->setModel($member_model);
		
		// $grid->addMethod('format_Nominee',function($g,$q){
		// 	if($g->model['Nominee'])
		// 		$nominee = $g->model['Nominee'];
		// 	else{
		// 		$sm_account = $g->add('Model_Account_SM')->addCondition('member_id',$g->model->id);
		// 		$sm_account->tryLoadAny();
		// 		$nominee = $sm_account['Nominee'];
		// 	}
		// 	$g->current_row_html['Nominee'] = $nominee;
		// });
		// $grid->addFormatter('Nominee','Nominee');


		// $grid->addMethod('format_RelationWithNominee',function($g,$q){
		// 	if($g->model['RelationWithNominee'])
		// 		$nominee = $g->model['RelationWithNominee'];
		// 	else{
		// 		$sm_account = $g->add('Model_Account_SM')->addCondition('member_id',$g->model->id);
		// 		$sm_account->tryLoadAny();
		// 		$nominee = $sm_account['RelationWithNominee'];
		// 	}
		// 	$g->current_row_html['RelationWithNominee'] = $nominee;
		// });
		// $grid->addFormatter('RelationWithNominee','RelationWithNominee');
		
		// $grid->addMethod('format_NomineeAge',function($g,$q){
		// 	if($g->model['NomineeAge'])
		// 		$nominee = $g->model['NomineeAge'];
		// 	else{
		// 		$sm_account = $g->add('Model_Account_SM')->addCondition('member_id',$g->model->id);
		// 		$sm_account->tryLoadAny();
		// 		$nominee = $sm_account['NomineeAge'];
		// 	}
		// 	$g->current_row_html['NomineeAge'] = $nominee;
		// });
		// $grid->addFormatter('NomineeAge','NomineeAge');



	}


	function page_accounts(){
		$this->api->stickyGET('members_id');

		$this->api->stickyGET('members_id');
		$member_model=$this->add('Model_Member');
		$member_model->addCondition('id',$_GET['members_id']);
		$member_model->loadAny();

		$this->add('H4')->setHTML('Accounts Details for <span style="text-transform:capitalize"><u>'.$member_model['name'].'</u></span>');
		$grid=$this->add('Grid');
		$accounts=$member_model->ref('Account');
		$accounts->addExpression('LoanAgainst')->set(function($m,$q){
			$x = $m->add('Model_Account',['table_alias'=>'loan_ag']);
			return $x->addCondition('id',$q->getField('LoanAgainstAccount_id'))->fieldQuery('AccountNumber');
		});
		// $accounts->addCondition('ActiveStatus',true);
		// $accounts->addCondition('MaturedStatus',false);
		$grid->setModel($accounts,array('branch','AccountNumber','LoanAgainst','scheme','agent','Amount','ActiveStatus','MaturedStatus','SchemeType'));

		$grid->addMethod('format_cuBal',function($g,$f){
			$bal = $g->model->getOpeningBalance($on_date=$g->api->nextDate($g->api->today),$side='both',$forPandL=false);
			if($bal['cr'] > $bal['dr']){
				$bal = ($bal['cr'] - $bal['dr']) . ' Cr';
			}else{
				$bal = ($bal['dr'] - $bal['cr']) . ' Dr';
			}

			$g->current_row[$f]=$bal ;
		});

		$grid->addMethod('format_maturityDate',function($g,$f){
			$acc = $this->add('Model_Account_'.$g->model['SchemeType']);
			$acc->load($g->model->id);
			$g->current_row[$f]=$acc['maturity_date'] ;
		});

		$grid->addColumn('maturityDate','maturity_date');
		$grid->addColumn('cuBal','cur_balance');

	}


	function page_guarantor_in(){

		$this->api->stickyGET('members_id');

		$account_model=$this->add('Model_Account');
		$account_model->join('account_guarantors.account_id')->addField('guarantor_member_id','member_id');
		$account_model->addCondition('guarantor_member_id',$_GET['members_id']);
		$account_model->addCondition('ActiveStatus',true);
		$account_model->addCondition('MaturedStatus',false);

		$this->add('H4')->setHTML('Accounts Details for <span style="text-transform:capitalize"><u>'.$this->add('Model_Member')->load($_GET['members_id'])->get('name').'</u></span>');
		$grid=$this->add('Grid');
		$grid->setModel($account_model,array('branch','AccountNumber','scheme','agent','Amount'));
	}
}