<?php

class page_corrections extends Page {
	public $rename_fields=array();// [] = array(table_name,old_field,new_field)
	public $new_fields=array();// [] = array(table_name,old_field,new_field)
	public $remove_fields=array();// [] = array(table_name,old_field,new_field)

	function page_index(){

	}	

	function page_fields(){

		$renameFields =array(
				array('defaultaccounts','a3','a4'),
				array('defaultaccounts','a3','a4'),
			);

		foreach ($renameFields as $dtl) {
			$this->renameField($dtl[0],$dtl[1],$dtl[2]);
		}

		$new_fields=array(
				array('table_name','field_name','type')
			);

		foreach ($new_fields as $dtl) {
			$this->addField($dtl[0],$dtl[1],$dtl[2]);
		}



		$remove_fields=array(
				array('table_name','field_name')
			);

		foreach ($remove_fields as $dtl) {
			$this->removeField($dtl[0],$dtl[1]);
		}


	}

	function hasField($table,$field){

	}

	function removeField($table,$field){
		try{
			$this->query("ALTER TABLE $table DROP $field");
		}catch(Exception $e){
			$this->add('Text')->set($e->getMessage());
		}
	}

	function renameField($table,$old_field_name,$new_name){
		try{
			$field_type = $this->query("SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$table' AND COLUMN_NAME = '$old_field_name'",true);
			if(!$field_type) return;
			$this->query("ALTER TABLE $table CHANGE $old_field_name $new_name $field_type")->getOne();
		}catch(Exception $e){
			$this->add('Text')->set($e->getMessage());
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
			$this->add('Text')->set($e->getMessage());
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

    	$to_move = array(
    			array(
    					'from'=>array('tabel_name',array(0,'id_4_referenece_field','field2','field3')),
    					'to'=>'table_name'
    				)
    		);

    	foreach ($to_move as $move) {
    		$this->query('TRUNCATE '. $move['to']);

	    	$this->query("INSERT INTO ".$move['to']." (
						SELECT ".implode(",", $move['from'][1])."
						FROM ".$move['from'][0]." 
						)");
    		
    	}

    	return;

    	$to_move = array(
    			array(
    					'from'=>array('model_name',array('field1','field2','fiel3')),
    					'to'=>array('model_name',array('newname1','newname2','newname3')),
    					'related_field'=>'from_table_id'
    				),
    		);

    	foreach ($to_move as $move) {
    		$model_obj = $this->add($move['from'][0]);
			$to_obj = $this->add($move['to'][0]);

    		foreach($model_obj as $junk){
    			$to_obj[$move['related_field']] = $model_obj->id;
    			for($i=0;$i<count($move['from'][1]);$i++){
    				$to_obj[$move['to'][1][$i]] = $model_obj[$move['from'][1][$i]];
    			}
    			$to_obj->saveAndUnload();
    		}
    	}
    }



    function page_transactionsUpdate(){
    	// fill display voucher from voucher first where display voucher =0
    		
    	$this->query('UPDATE transaction_row SET display_voucher_no=voucher_no WHERE display_voucher_no = 0');

    	// Create Transaction master with 
    	// display_voucher_no,branch_id,transaction_type_id,created_at, updated_at

    	$this->query("INSERT INTO transactions (
					SELECT 0,  transaction_type_id, staff_id, reference_account_id, branch_id ,voucher_no , display_voucher_no ,Narration, created_at, updated_at 
					FROM transaction_row 

					GROUP BY voucher_no, branch_id
					ORDER BY voucher_no, created_at, branch_id
					)");
    	
    	$this->addField('transaction_row','transaction_id','int');
    	// join transactionrow with transaction on vaoucherno and branchid and fill transaction's id in trnsaction_id  
    	$this->query('UPDATE 
			transaction_row tr join transactions t on t.voucher_no_original=tr.voucher_no and t.branch_id = tr.branch_id
			SET
			tr.transaction_id = t.id');

    	// Remove unwanted columns
    	
    	
    }



}