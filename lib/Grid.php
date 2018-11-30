<?php

class Grid extends Grid_Advanced {
	
	public $fixed_header=false;
    public $sno=1;

    function init(){
        parent::init();
        $this->order = $this->addOrder();
    }

    function addSno(){
        $this->addColumn('sno','s_no');
        $this->order->move('s_no','first');
        return $this;
    }

    function format_sno($field){
        // if($this->model->loaded())
            $this->current_row[$field] = (($this->sno++) + ($_GET[$this->name.'_paginator_skip']));
        
        // $this->current_row[$field] = $this->skip_var;        
    }

    /**
     * Overrided function from Grid Basics to allow Html in Column Headers
     *
     * @return void
     */
    function precacheTemplate()
    {
        // Extract template chunks from grid template

        // header
        $header = $this->template->cloneRegion('header');
        $header_col = $header->cloneRegion('col');
        $header_sort = $header_col->cloneRegion('sort');
        $header->del('cols');

        // data row and column
        $row = $this->row_t;
        $col = $row->cloneRegion('col');
        $row->setHTML('row_id', '{$id}');
        $row->trySetHTML('odd_even', '{$odd_even}');
        $row->del('cols');

        // totals row and column
        if ($t_row = $this->totals_t) {
            $t_col = $t_row->cloneRegion('col');
            $t_row->del('cols');
        }

        // Add requested columns to row templates
        foreach ($this->columns as $name => $column) {

            // header row
            $header_col
                ->setHTML('descr', $column['descr'])
                ->trySet('type', $column['type']);

            // sorting
            // TODO: rewrite this (and move into Advanced)
            if (isset($column['sortable'])) {
                $s = $column['sortable'];
                $header_sort
                    ->trySet('order', $s[0])
                    ->trySet('sorticon', $this->sort_icons[$s[0]]);
                $header_col
                    ->trySet('sortid', $sel = $this->name.'_sort_'.$name)
                    ->setHTML('sort', $header_sort->render());

                $this->js('click', $this->js()->reload(array($this->name.'_sort'=>$s[1])))
                    ->_selector('#'.$sel);
            } else {
                $header_col
                    ->del('sort')
                    ->tryDel('sortid')
                    ->tryDel('sort_del');
            }

            // add thparams for header columns
            if ($column['thparam']) {
                $header_col->trySetHTML('thparam', $column['thparam']);
            } else {
                $header_col->tryDel('thparam');
            }
            $header->appendHTML('cols', $header_col->render());

            // data row
            $col->del('content')
                ->setHTML('content', '{$'.$name.'}')
                ->setHTML('tdparam', '{tdparam_'.$name.'}style="white-space:nowrap"{/}');
            $row->appendHTML('cols', $col->render());

            // totals row
            if (isset($t_row) && isset($t_col)) {
                $t_col
                    ->del('content')
                    ->setHTML('content', '{$'.$name.'}')
                    ->trySetHTML('tdparam', '{tdparam_'.$name.'}style="white-space:nowrap"{/}');
                $t_row
                    ->appendHTML('cols', $t_col->render());
            }
        }

        // Generate templates from rendered strings

        // header
        $this->template->setHTML('header', $this->show_header ? $header->render() : '');

        // data row
        $this->row_t = $this->api
            ->add('GiTemplate')
            ->loadTemplateFromString($row->render());

        // totals row
        if (isset($t_row) && $this->totals_t) {
            $this->totals_t = $this->api
                ->add('GiTemplate')
                ->loadTemplateFromString($t_row->render());
        }
    }

	function render(){		
		if($this->fixed_header){
            $options=['zIndex'=>1];
            if($this->app->isAjaxOutput()){
                // $options['scrollContainer']=$this->js(null,"return ev.closest('.atk-table')")->_enclose();
                // $options['position']='absolute';
            }
            $this->js(true)->_load('jquery.floatThead.min')->find('table:not(.ui-dialog table)')->floatThead($options);
        }
		parent::render();
	}
}