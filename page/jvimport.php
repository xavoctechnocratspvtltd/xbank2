<?php


class page_jvimport extends Page {
	public $title ="Import Multiple Jvs";

	function page_index(){

		ini_set("memory_limit", "-1");
		set_time_limit(0);

		$this->add('Controller_Acl');

		$this->app->stickyGET('do');

		$this->import_vp = $this->add('VirtualPage');
		$this->import_vp->set([$this,'performImport']);

		$form= $this->add('Form');
		$form->template->loadTemplateFromString("<form method='POST' action='".$this->api->url()."' enctype='multipart/form-data'>
			<input type='file' name='csv_jv_file'/>
			<input type='submit' value='Upload'/>
			</form>"
			);

		if($_FILES['csv_jv_file']){
			if ( $_FILES["csv_jv_file"]["error"] > 0 ) {
				$this->add( 'View_Error' )->set( "Error: " . $_FILES["csv_jv_file"]["error"] );
			}else{
				$mimes = ['text/comma-separated-values', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.ms-excel', 'application/vnd.msexcel', 'text/anytext'];
				if(!in_array($_FILES['csv_jv_file']['type'],$mimes)){
					$this->add('View_Error')->set('Only CSV Files allowed');
					return;
				}

				$file_path = $_FILES['csv_jv_file']['tmp_name'];
				move_uploaded_file($file_path, 'jvimport.csv');

				$this->js(true)->univ()->frameURL($this->app->url($this->import_vp->getURL(),['file_path'=>'jvimport.csv']));

				// $importer = new CSVImporter($file_path,true,',');
				// $data = $importer->get();
				
				// $this->add('View')->set('All Data Imported');
			}
		}
	}

	// Transaction	DR_Account	DR_Amount	CR_Account	CR_Amount	Transaction_Type	Narration	transaction_date

	function performImport($p){
		$file_path = $p->app->stickyGET('file_path');
		$p->add('View_Console')->set(function($c)use($file_path){
			try{
				$this->api->db->beginTransaction();

				$importer = new CSVImporter($file_path,true,',');
				$data = $importer->get();

				// verify all data okay: for account and other figures etc.
				$row_no=2;
				$errors_count=0;
				foreach ($data as $row) {
					if(!$row['Transaction']) continue;

					if($row['DR_Account']){
						$dr_found = $this->api->db->dsql()->expr('select count(AccountNumber) from accounts where trim(AccountNumber)="'.trim($row['DR_Account']).'"')->getOne();
						if(!$dr_found){
							$c->err('At row '. $row_no.' DR_Account not found : '.$row['DR_Account']);
							$errors_count++;
						}
					}

					if($row['CR_Account']){
						$cr_found = $this->api->db->dsql()->expr('select count(AccountNumber) from accounts where trim(AccountNumber)="'.trim($row['CR_Account']).'"')->getOne();
						if(!$cr_found){
							$c->err('At row '. $row_no.' CR_Account not found : '.$row['CR_Account']);
							$errors_count++;
						}
					}

					if($row['Transaction_Type']){
						$tr_type_found = $this->api->db->dsql()->expr('select count(name) from transaction_types where trim(name)="'.trim($row['Transaction_Type']).'"')->getOne();
						if(!$tr_type_found){
							$c->err('At row '. $row_no.' Transaction_Type not found : '.$row['Transaction_Type']);
							$errors_count++;
						}
					}
					$c->out($row_no.' is checked');
					$row_no++;
				}

				if($errors_count){
					$c->err('Please solve '.$errors_count.' errors first to proceed');
					return;	
				}

				// Actual transaction creation
				$do = $this->app->stickyGET('do')?false:true;
				$row_no=2;
				$running_transaction_number=null;
				foreach ($data as $row) {
					if($row['Transaction'] != $running_transaction_number){
						if(isset($transaction) && !$transaction->executed) $transaction->execute($do);

						$transaction = $this->add('Model_Transaction');
						$transaction->createNewTransaction($row['Transaction_Type'],$in_branch=null,$row['Transaction_Date'],$row['Narration']);
						$running_transaction_number = $row['Transaction'];
					}

					if($row['DR_Account']) $transaction->addDebitAccount($row['DR_Account'],$row['DR_Amount']);
					if($row['CR_Account']) $transaction->addCreditAccount($row['CR_Account'],$row['CR_Amount']);
					$c->out($row_no.' is imported');
					$row_no++;
				}
				if(isset($transaction) && !$transaction->executed) $transaction->execute($do);
				$this->api->db->commit();
			}catch(\Exception $e){
				$this->api->db->rollback();
				throw $e;
			}

			unlink($file_path);
		});
	}

}