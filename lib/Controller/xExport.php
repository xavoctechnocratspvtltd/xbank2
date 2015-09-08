
<?php

class Controller_xExport extends \AbstractController {
	public $fields=null;
	public $add_sno = true;
	public $totals=array();
	public $total_values=array();
	public $row_separator="\n";
	public $column_separator=",";
	public $output=null;
	public $output_type = "text/csv";
	public $output_disposition = "attachment";
    public $output_filename = "export.csv";
    public $model=null;
    public $export_title='Export';

	function init(){
		parent::init();
		if(!$this->fields) $this->fields = $grid->model->getActualFields();
		// $this->addHook("output", array($this, "output"));
		if(!$this->model) $this->model= $this->owner->model;
		if($_GET[$this->name]){
			$this->getData();
			$this->output();
		}

		if($this->owner instanceof \Grid)
			$this->btn = $this->owner->addButton($this->export_title);
		else
			$this->btn = $this->owner->add('Button')->set($this->export_title);
		
		$this->btn->js("click")->univ()->location($this->api->url(null, array($this->name => "1")));
	}

	function getData(){
		// Add Headers First
		$header=array();
		if($this->add_sno){
			$header[] = $this->add_sno===true?"Sno":$this->add_sno;
			$this->total_values[]= "Total";
		}
		foreach ($this->fields as $f) {
			if($fn = $this->model->hasElement($f)){
				$f= $fn->caption()?:$f;
			}
			$header[] = "\"" . preg_replace("/\"/", "\"\"", ucwords(str_replace("_", " ", $f))) . "\"";
		}
		$this->output= implode($this->column_separator, $header);
		
		// Add Data
		$i=1;
		foreach ($this->model as $row) {
            $cols = array();
            
            if($this->add_sno)
            	$cols[] = $i++;

            foreach ($this->fields as $f){
            	// if(!in_array($col, $this->fields)) continue;
            	$col= $row[$f];
            	if(in_array($f, $this->totals)){
            		if(!isset($this->total_values[$f])) $this->total_values[$f] = 0;
            		$this->total_values[$f] += $col;
            	}else{
            		$this->total_values[$f]="";
            	}

                $cols[] = "\"" . preg_replace("/\"/", "\"\"", $col) . "\"";
            }
            if ($this->output){
                $this->output .= $this->row_separator;
            }
            $this->output .= implode($this->column_separator, $cols);
		}
		
        $this->output .= $this->row_separator;
        $this->output .= implode($this->column_separator, $this->total_values);
	}

	function setOutput($type=null, $disposition=null, $filename=null){
        if ($type){
            $this->output_type = $type;
        }
        if ($disposition){
            $this->output_disposition = $disposition; // inline or attachment
        }
        if ($filename){
            $this->output_filename = $filename;
        }
    }

    function output(){
    	header("Content-type: " . $this->output_type);
        header("Content-disposition: " . $this->output_disposition . "; filename=\"" . $this->output_filename . "\"");
        header("Content-Length: " . strlen($this->output));
        header("Content-Transfer-Encoding: binary");
        print $this->output;
        exit;
    }
}