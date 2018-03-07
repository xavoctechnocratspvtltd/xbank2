<?php

class Grid extends Grid_Advanced {
	
	public $fixed_header=true;

	function render(){		
		if($this->fixed_header){
            $options=['zIndex'=>1];
            if($this->app->isAjaxOutput()){
                // $options['scrollContainer']=$this->js(null,"return ev.closest('.atk-table')")->_enclose();
                // $options['position']='absolute';
            }
            $this->js(true)->_load('jquery.floatThead.min')->find('table')->floatThead($options);
        }
		parent::render();
	}
}