<?php

// DONE: voucher_no in transaction table to be double now
// DONE: all admission fee voucher narration is '10 (memberid)' format ... put memberid in reference id
//        TransactionTYpe id = same from 7 or 3 which one is in use now ie 3
// DONE: account_type of existing accounts 
// DONE: Scheme Loan type => boolean to text PL/VL/SL or empty for non loan type accounts
// DONE: Saving account current interests till date as now onwards its keep saved on transaction
// DONE: penalty not implemented if applied on last emi ... LOAN SCHEME 82 line
// DONE: Take every date transaction for each CC account aftre 31 march and update Interest in CurrentInterest Field

// DONE: refence_account_id to reference_id name change
// TODOS: PandLGroup correction as per default accounts
// TODOS: Default Accounts Query should be faster
// TODOS: Transaction_id to be indexed in transaction_row Table
// FD Schemes from Month to days
// TODO : Recurring in function setAccountType ???
// DONE : movetomany .. to be checked ... -> To do it manually for accounts as well as guarenters ..


// DONE : Run query to convert all SM accounts account_type = SM


// mannual words : FD Schemes from month to days
// all accountand agent guarenters
// commission structure
// agents cadre



class page_corrections extends Page {
	public $total_taks=11;
	public $title = "Correction";
	function init(){
		parent::init();
		ini_set('memory_limit', '2048M');
		set_time_limit(0);
		error_reporting(E_ALL);
		$this->add('progressview/View_Progress',array('interval'=>500));
	}

	function page_index(){
		$this->api->resetProgress();

		if(!$_GET['execute']) {
			// $this->add('View_Error')->set('Execute Another thread with execute=1 Querry parameter');
			return;
		}


		try{
			$this->api->db->beginTransaction();
			$this->query('SET FOREIGN_KEY_CHECKS = 0');

			if($jmp=$_GET['jump_to']){
				$this->$jmp();
				$this->query('SET FOREIGN_KEY_CHECKS = 1');
				$this->api->db->commit();
				return;
			}


			$this->api->markProgress('Corrections',"",'Renaming tables',$this->total_taks);
			$this->renameTables();

			$this->createFielStoreTables();
			
			$this->add('Model_AgentGuarantor');
			$this->add('Model_AccountGuarantor');
			$this->add('Model_Transaction');
			
			$this->api->markProgress('Corrections',1,'Adding, Editing, Removing Fields ...',$this->total_taks);
			$this->page_fields();
			
			$this->api->markProgress('Corrections',2,'member_id from narration to referecen_id',$this->total_taks);
			$this->page_memberidToReferenceAndMisc();

			$this->api->markProgress('Corrections',2.5,'Moving To Many ...',$this->total_taks);
			$this->page_movetomany();

			$this->api->markProgress('Corrections',3,'Transactions Table Refactoring ...',$this->total_taks);
			$this->page_transactionsUpdate();
			
			$this->api->markProgress('Corrections',4,'Agent AccountNumber to account_id ...',$this->total_taks);
			$this->agentAccountToRelation();

			$this->api->markProgress('Corrections',5,'Account Type set for existing accounts',$this->total_taks);
			$this->setAccountType();
			
			$this->api->markProgress('Corrections',6,'Saving Account Interests ...',$this->total_taks);
			$this->savingInterestTillNow();
			
			$this->api->markProgress('Corrections',7,'CC Account Interest',$this->total_taks);
			$this->ccInterestTillNow();

			$this->api->markProgress('Corrections',8,'done',$this->total_taks);

			// Make currentInterest = 0 for Account_CC <== Now done in ccInterestTillNow function
			// $this->add('Model_Account_CC')->_dsql()->set('CurrentInterest',0)->update();

			$this->api->markProgress('Corrections',9,'Creating Default Accounts',$this->total_taks);
			$this->checkAndCreateDefaultAccounts();
			
			$this->api->markProgress('Corrections',10,'Closing Dates Correcting',$this->total_taks);
			$this->closingDateCorrections();

			$this->query('SET FOREIGN_KEY_CHECKS = 1');
			$this->api->db->commit();
		}catch(Exception $e){
			$this->api->db->rollBack();
			throw $e;
		}
	}

	function createFielStoreTables(){
		$sql = file_get_contents(getcwd().'/atk4-addons/misc/docs/filestore.001.sql');
		$this->query($sql);

	}

	// task 1
	function renameTables(){
		$rename_tables =array(
				'jos_xbalance_sheet'=>'balance_sheet',
				'jos_xbranch'=>'branches',
				'jos_xdealer'=>'dealers',
				'jos_xdocuments'=>'documents',
				'jos_xmember'=>'members',
				'jos_xschemes'=>'schemes',
				'jos_xstaff'=>'staffs',
				'jos_xtransactions'=>'transaction_row',
				'jos_xaccounts'=>'accounts',
				'jos_xtransaction_type'=>'transaction_types',
				'jos_xdocuments_submitted'=>'documents_submitted',
				'jos_xagents'=>'agents',
				'jos_xpremiums'=>'premiums',
				'jos_xclosings'=>'closings',

				);
		$this->api->markProgress('Renaming_Tables',0,'...',count($rename_tables));

		$i=1;
		foreach ($rename_tables as $old_table_name => $new_table_name) {
			try{
				$this->query("RENAME TABLE $old_table_name TO $new_table_name");
				$this->api->markProgress('Renaming_Tables',$i++,$old_table_name . ' => ' . $new_table_name);
			}catch(Exception $e){
				$this->add('View')->set("Could not rename table $old_table_name  -- " . $e->getMessage());
			}
		}
		$this->api->markProgress('Renaming_Tables',null,'...');
	}

	function page_fields(){

		$this->add('View_Info')->set('Renaming fields');
		$renameFields =array(
				array('balance_sheet','Head','name'),
				array('branches','Name','name'),
				array('dealers','DealerName','name'),
				array('documents','Name','name'),
				array('members','Name','name'),
				array('schemes','Name','name'),
				// array('schemes','LoanType','type'),
				array('staffs','Name','name'),
				array('transaction_types','Transaction','name'), //CHECK
				array('accounts','schemes_id','scheme_id'),
				array('accounts','agents_id','agent_id'),
				array('accounts','RdAmount','Amount'),
				array('accounts','InterestToAccount','intrest_to_account_id'),
				array('accounts','LoanAgainstAccount','LoanAgainstAccount_id'),
				array('staffs','StaffID','username'),
				array('transaction','accounts_id','account_id'),
				array('transaction_row','accounts_id','account_id'),
				array('premiums','accounts_id','account_id'),
				array('transactions','reference_account_id','reference_id'),
			);

		$this->api->markProgress('Rename_Fields',0,'...',count($renameFields));
		$i=1;
		foreach ($renameFields as $dtl) {
			$this->renameField($dtl[0],$dtl[1],$dtl[2]);
			$this->api->markProgress('Rename_Fields',$i++,print_r($dtl,true));		
		}
		$this->add('View_Info')->set('fields renamed adding new ');

		

		$remove_fields=array(
				array('members','IsCustomer'),
				array('members','IsMember'),
				array('schemes','branch_id'),
				array('schemes','LoanType'),
				array('members','collector_id'),
				array('members','Age'),
				array('members','ParentAddress'),
			);
		$this->api->markProgress('Remove_Fields',0,'...',count($remove_fields));
		
		$i=1;
		foreach ($remove_fields as $dtl) {
			$this->removeField($dtl[0],$dtl[1]);
			$this->api->markProgress('Remove_Fields',$i++,print_r($dtl,true));
		}
		$this->api->markProgress('Remove_Fields',null,'...');

		$new_fields=array(
				array('members','title','string'),
				array('members','is_agent','boolean'),
				array('members','landmark','string'),
				array('members','tehsil','string'),
				array('members','district','string'),
				array('members','city','string'),
				array('members','pin_code','string'),
				array('members','doc_image_id','int'),
				array('members','state','string'),
				array('members','ParentAddress','text'),
				array('members','is_active','boolean'), /*Query to be 1*/
				array('members','is_defaulter','boolean'), /*Query to be 0*/
				array('staffs','name','string'),
				//New Added
				array('staffs','father_name','string'),
				array('staffs','pf_amount','money'),
				array('staffs','basic_pay','money'),
				array('staffs','variable_pay','money'),
				array('staffs','created_at','datetime'),
				array('staffs','present_address','text'),
				array('staffs','parmanent_address','text'),
				array('staffs','mobile_no','string'),
				array('staffs','landline_no','string'),
				array('staffs','DOB','date'),
				array('accounts_pending','sig_image_id','int'),
				array('accounts','sig_image_id','int'),
				//New Added//

				array('dealers','loan_panelty_per_day','int'),
				array('dealers','time_over_charge','int'),
				array('dealers','dealer_monthly_date','string'),
				array('dealers','properitor_name','string'),
				array('dealers','properitor_phone_no','string'),
				array('dealers','product','string'),
				array('agents','account_id','int'),
				array('accounts','`Group`','string'),
				array('accounts','`account_type`','string'),
				array('accounts','`extra_info`','text'),
				array('accounts','`mo_id`','int'),
				array('accounts','`team_id`','int'),
				array('premiums','`PaneltyCharged`','money'),
				array('premiums','`PaneltyPosted`','money'),
				array('accounts','`MaturityToAccount_id`','int'),
				array('accounts','`related_account_id`','int'),
				array('accounts','`doc_image_id`','int'),
				array('schemes','`type`','string'),
				array('agents','`cadre_id`','int'),
			);
		$this->api->markProgress('New_Field',0,'...',count($new_fields));
		$i=1;
		foreach ($new_fields as $dtl) {
			$this->addField($dtl[0],$dtl[1],$dtl[2]);
			$this->api->markProgress('New_Field',$i++,print_r($dtl,true));
		}

		$this->query('UPDATE members SET is_active=1');
		$this->query('UPDATE members SET is_defaulter=0');

		$this->api->markProgress('New_Field',null,'...');
		$this->query('UPDATE staffs SET name=username');


		$drop_table=array('jos_banner','jos_bannerclient','jos_bannertrack',
						'jos_categories','jos_components','jos_contact_details'
						,'jos_content','jos_content_frontpage','jos_content_rating'
						,'jos_core_acl_aro','jos_core_acl_aro_groups','jos_core_acl_aro_map',
						'jos_core_acl_aro_sections','jos_core_acl_groups_aro_map','jos_core_log_items'
						,'jos_core_log_searches','jos_groups','jos_menu','jos_menu_types',
						'jos_messages','jos_messages_cfg','jos_migration_backlinks',
						'jos_modules','jos_modules_menu','jos_newsfeeds','jos_plugins','jos_poll_data',
						'jos_poll_date','jos_poll_menu','jos_polls','jos_sections','jos_session','jos_stats_agents',
						'jos_templates_menu','jos_users','jos_weblinks');

		$this->api->markProgress('Drop_Table',0,'...',count($drop_table));
		$i=1;
		foreach ($drop_table as $table_name) {
			try{
				$this->query("DROP Table $table_name");
				$this->api->markProgress('Drop_Table',$i++,$table_name);
			}catch(Exception $e){
				$this->add('View')->set($table_name.' can not drop');
			}
		}

		$this->api->markProgress('Drop_Table',null,'...');
		$this->add('Model_Account_Loan')->_dsql()
			->set('CurrentInterest',0)
			->update();

	}

	function hasField($table,$field){

	}

	function removeField($table,$field){
		try{
			$this->query($q="ALTER TABLE $table DROP $field");
		}catch(Exception $e){
			$this->add('View')->set($q. ' -- '. $e->getMessage());
		}
	}

	function renameField($table,$old_field_name,$new_name){
		try{
			$field_type = $this->query($q="SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$table' AND COLUMN_NAME = '$old_field_name'",true);
			if(!$field_type) return;
			if($field_type=='varchar' || $field_type=='char') $field_type = 'varchar(255)';
			
			$this->query($q="ALTER TABLE $table CHANGE $old_field_name $new_name $field_type")->getOne();
		}catch(Exception $e){
			$this->add('View')->set($q. " -- " . $e->getMessage());
		}
	}

	function query($q,$get=false){
		$obj = $this->api->db->dsql()->expr($q);
		if($get)
			return $obj->getOne();
		else
			return $obj->execute();
	}

	function addField($table, $field, $type){
		try{
			$q=$this->api->db->dsql()->expr('alter table [al_table] add [field_name] [type_expr]');
			$q->setCustom('al_table',$table);
			$q->setCustom('field_name',$field);
			$q->setCustom('type_expr',$this->resolveFieldType($type));
			$q->execute();
		}catch(Exception $e){
			$this->add('View')->set("Add field $table, $field  -- ".$e->getMessage());
		}
	}

	// TODO: move this to a setparate controller
    function resolveFieldType($type){
        $cast = array(
            "int" => "integer",
            "money" => "decimal(10,2)",
            "datetime" => "datetime",
            "date" => "date",
            "string" => "varchar(255)",
            "text" => "text",
            "boolean" => "bool",
        );
        if(isset($cast[$type]))return $cast[$type];
        return 'varchar(255)';
    }

    function page_movetomany(){
    	// throw $this->exception('Guarenter for loan accounts should move ... not working ... and ONLY FOR LOAN', 'ValidityCheck')->setField('FieldName');
    	$to_move = array(
    			array(
    					'from'=>array('agents',array(0,'id','Guarantor1Name','Guarantor1FatherHusbandName','Guarantor1Address',0,'Guarantor1Occupation')),
    					'to'=>'agent_guarantors',
    					'field'=>'remove'
    				),
    			array(
    					'from'=>array('agents',array(0,'id','Guarantor2Name','Guarantor2FatherHusbandName','Guarantor2Address',0,'Guarantor2Occupation')),
    					'to'=>'agent_guarantors',
    					'field'=>'remove' /*remove*/
    				),
    			array(
    					'from'=>array('accounts',array(0,'id','Nominee',0,'MinorNomineeParentName','RelationWithNominee',0)),
    					'to'=>'account_guarantors',
    					'field'=>'remove' /*remove*/
    				),
    		);

    	foreach ($to_move as $move) {
    		try{
	    		$this->query($q='TRUNCATE '. $move['to']);
		    	$this->query($q="INSERT INTO ".$move['to']." (
							SELECT ".implode(",", $move['from'][1])."
							FROM ".$move['from'][0]." 
							)");
		    	foreach ($move['from'][1] as $field) {
		    			if($field==0 or $field=='id') continue;
		    			if($move['field']=='remove')
			    			$this->removeField($move['from'][0],$field);
		    		}	
		    }catch(Exception $e){
		    	$this->add('View')->set('Coudnot move ' . $e->getMessage() . ' <br>'.$q);
		    }

		    // TODO : Empty moved columsn for perticular accounts types ...
    		
    	}



    }


    function page_transactionsUpdate(){
    	
    	// fill display voucher from voucher first where display voucher =0
    
    	$this->query('UPDATE transaction_row SET display_voucher_no=voucher_no WHERE display_voucher_no = 0');

    	// Create Transaction master with 
    	// display_voucher_no,branch_id,transaction_type_id,created_at, updated_at

    	$this->query('TRUNCATE transactions');

    	$this->query("INSERT INTO transactions (
					SELECT 0,  transaction_type_id, staff_id, reference_account_id, branch_id ,voucher_no , display_voucher_no ,Narration, created_at, updated_at 
					FROM transaction_row 

					GROUP BY voucher_no, branch_id
					ORDER BY voucher_no, created_at, branch_id
					)");
    	
    	$this->addField('transaction_row','transaction_id','int');

    	try{
	    	$this->query('CREATE INDEX voucher_no_original ON transactions (voucher_no_original) USING BTREE');
    	}catch(Exception $e){
    		
    	}

    	// join transactionrow with transaction on vaoucherno and branchid and fill transaction's id in trnsaction_id  
    	$this->query('UPDATE 
			transaction_row tr join transactions t on t.voucher_no_original=tr.voucher_no and t.branch_id = tr.branch_id
			SET
			tr.transaction_id = t.id');
    	
    	// Remove unwanted columns
    	// TODOS: 
    	
    }

    function agentAccountToRelation(){
    	$this->query("
				UPDATE agents ag
				JOIN accounts ac on ac.AccountNumber = ag.AccountNumber
				SET
					ag.account_id = ac.id
    		");
    }

    function savingInterestTillNow($on_date=null){
        if(!$on_date) $on_date = $this->api->today;
    	$sa_update=$this->add('Model_Account_SavingAndCurrent');
    	$sa_update->dsql()->set('CurrentInterest',0)->set('LastCurrentInterestUpdatedAt','2014-03-31')->update();

    	$sa=$this->add('Model_Active_Account_SavingAndCurrent');

    	$total = $sa->count()->getOne();
    	$i=1;
    	foreach ($sa as $sa_array) {
    		$this->api->markProgress('Saving_Interest',$i++,$sa['AccountNumber'],$total);
	    	$transaction_row = $sa->ref('TransactionRow');
	    	$transaction_row->addCondition('created_at','>','2014-03-31');
	    	
	    	$last_tr=null;
	    	foreach ($transaction_row->getRows() as $tr) {
	    		$sa['CurrentInterest'] = $sa['CurrentInterest'] + $sa->getSavingInterest($tr['created_at']);
				$sa['LastCurrentInterestUpdatedAt'] = $tr['created_at'];	
				$last_tr = $tr;
	    	}

	    	if(strtotime(date('Y-m-d',strtotime($last_tr['created_at']))) != strtotime(date('Y-m-d',strtotime($on_date)))){
	    		$sa['CurrentInterest'] = $sa['CurrentInterest'] + $sa->getSavingInterest($on_date,null,null,null,true);
				$sa['LastCurrentInterestUpdatedAt'] = $on_date;
	    	}

	    	$sa->save();
    	}
    	$this->api->markProgress('Saving_Interest',null,'');
    }

   	function checkAndCreateDefaultAccounts(){

   		$i=1;
   		$scheme = $this->add('Model_Scheme');
		$account = $this->add('Model_Account');
		$existing_account = $this->add('Model_Account');
		foreach (explode(",", ACCOUNT_TYPES) as $st) {
	   		$all_schemes = $this->add('Model_Scheme_'.$st);
			$branch = $this->add('Model_Branch')->addCondition('published',true);
			foreach($branch as $b){
				foreach ($all_schemes as $sc) {
					foreach ($all_schemes->getDefaultAccounts() as $details) {
			    		$this->api->markProgress('Default_Accounts_Create',$i++,$st . ' in ' . $branch['name']. ' - ' .$branch['Code'].SP.$details['intermediate_text'].SP.$sc['name'] );
						$scheme->loadBy('name',$details['under_scheme']);
						$existing_account->tryLoadBy('AccountNumber',$branch['Code'].SP.$details['intermediate_text'].SP.$sc['name']);
						if(!$existing_account->loaded())
							$account->createNewAccount($branch->getDefaultMember()->get('id'),$scheme->id,$branch,$branch['Code'].SP.$details['intermediate_text'].SP.$sc['name'],array('DefaultAC'=>true,'Group'=>$details['Group'],'PAndLGroup'=>$details['PAndLGroup']));
						else{
							if($existing_account['PAndLGroup'] != $details['PAndLGroup'] ){
								$existing_account['PAndLGroup'] = $details['PAndLGroup'];
								$existing_account->save();
							}
							$this->add('View_Error')->setHtml("<b>".$branch['Code'].SP.$details['intermediate_text'].SP.$sc['name']. '</b> Already exists');
						}
						$account->unload();
						$scheme->unload();
						$existing_account->unload();
					}
				}
			}
			$branch->destroy();
			$all_schemes->destroy();
		}
   	}

   	function closingDateCorrections(){
   		foreach($closings = $this->add('Model_Closing') as $cls){
   			$cls['daily'] = $this->api->previousDate($cls['daily']);
   			$cls['weekly'] = $this->api->previousDate($cls['weekly']);
   			$cls['monthly'] = $this->api->previousDate($cls['monthly']);
   			$cls['halfyearly'] = $this->api->previousDate($cls['halfyearly']);
   			$cls['yearly'] = $this->api->previousDate($cls['yearly']);
   			$cls->save();
   		}
   	}

    function ccInterestTillNow($on_date=false){

    	// IMPORTANT: This Interest is posted MONTHLY (END OF MONTH)

    	if(!$on_date) $on_date = $this->api->today;

    	$cc_update=$this->add('Model_Account_CC');
    	$cc_update->dsql()->set('CurrentInterest',0)->set('LastCurrentInterestUpdatedAt',$this->api->monthLastDate($this->api->previousMonth($this->api->today)))->update();
    	// TODOS: Take every date transaction for each CC account aftre LAST MONTHLY CLOSING and update Interest in CurrentInterest Field

    	// $this['CurrentInterest'] = $this['CurrentInterest'] + $this->getCCInterest($on_date);
		// $this['LastCurrentInterestUpdatedAt'] = $on_date;

    	$cc=$this->add('Model_Active_Account_CC');

    	$total = $cc->count()->getOne();
    	$i=1;
    	foreach ($cc as $cc_array) {
    		$this->api->markProgress('CC_Interest',$i++,$cc['AccountNumber'],$total);
	    	$transaction_row = $cc->ref('TransactionRow');
	    	$transaction_row->addCondition('created_at','>',$cc['LastCurrentInterestUpdatedAt']);
	    	
	    	$last_tr=null;
	    	foreach ($transaction_row->getRows() as $tr) {
	    		$cc['CurrentInterest'] = $cc['CurrentInterest'] + $cc->getCCInterest($tr['created_at']);
				$cc['LastCurrentInterestUpdatedAt'] = $tr['created_at'];	
				$last_tr = $tr;
	    	}

	    	// if last transaction is before last day of last month .. get interest as on last date of last month
	    	$last_date_last_month = strtotime(date('Y-m-t',strtotime($on_date. ' -1 month')));

	    	if(strtotime(date('Y-m-d',strtotime($last_tr['created_at']))) < $last_date_last_month){
	    		$cc['CurrentInterest'] = $cc['CurrentInterest'] + $cc->getCCInterest(date('Y-m-d',$last_date_last_month),null,null,null,true);
				$cc['LastCurrentInterestUpdatedAt'] = $on_date;
	    	}

	    	$cc->save();
    	}
    	$this->api->markProgress('CC_Interest',null,'');


    }

    function setAccountType(){
    	$q="
    	UPDATE 
		accounts a 
		JOIN
    	(
			SELECT
			accounts.id,
			accounts.AccountNumber,
			schemes.SchemeType,


        IF (
            schemes.SchemeType = 'Loan',
        	IF(accounts.LoanAgainstAccount_id is not null,
        		'Loan Against Deposit',
       			IF (
                	LOCATE('pl ', schemes. NAME),
                	'Personal Loan',
                	'Two Wheeler Loan'
       			)
        ),


		IF (
			schemes.SchemeType = 'FixedAndMis',
			IF (
				LOCATE(
					accounts.AccountNumber,
					'MIS'
				),
				'MIS',
				'FD'
			),
			IF (
				schemes.SchemeType = 'SavingAndCurrent',
				IF (
					LOCATE(
						'SB',
						accounts.AccountNumber
					)
					OR LOCATE(
						'_SA_',
						accounts.AccountNumber
					)
					OR LOCATE(
						'Saving',
						accounts.AccountNumber
					)
				
		,
					'Saving',
					'Current'
				),
				schemes.SchemeType
			)
		)
		) as should_be
		FROM
			accounts
		INNER JOIN schemes ON accounts.scheme_id = schemes.id
		) as Temp on Temp.id=a.id
	
		SET
		a.account_type = Temp.should_be
    	";

    	$this->query($q);

    	// share capital
    	$q="UPDATE accounts SET account_type='SM' WHERE AccountNumber like 'SM%'";
    	$this->query($q);

    }

    function page_memberidToReferenceAndMisc(){
    	$q1="UPDATE transactions SET transaction_type_id = 3 WHERE transaction_type_id=7";
    	$this->query($q1);

    	$q2="ALTER TABLE `transactions` CHANGE `voucher_no` `voucher_no` DECIMAL( 10, 5 ) NULL DEFAULT NULL ";
    	$this->query($q2);

    	$q3="
			UPDATE 
				transactions
				SET
				reference_id = 
				SUBSTR(
				Narration ,
				LOCATE('(',Narration)+1
				,
				LOCATE(')',Narration) - LOCATE('(',Narration)-1
				)
				WHERE
				transaction_type_id=3 or transaction_type_id=7

    	";

    	$this->query($q3);
    }

}