<?php

class Grid_BalanceSheet extends Grid_AccountsBase{
	function format_details($field){
		if($this->current_row['Amount']==0){
            $this->current_row_html[$field]='';
        }
	}
	/**
     * Initialize expander
     *
     * @param string $field field name
     *
     * @return void
     */
    function init_expander($field)
    {
        // set column style
        @$this->columns[$field]['thparam'] .= ' style="width:40px; text-align:center"';

        // set column refid - referenced model table for example
        if (!isset($this->columns[$field]['refid'])) {

            if ($this->model) {
                $refid = $this->model->table;
            } elseif ($this->dq) {
                $refid = $this->dq->args['table'];
            } else {
                $refid = preg_replace('/.*_/', '', $this->api->page);
            }

            $this->columns[$field]['refid'] = $refid;
        }

        // initialize button widget on page load
        $class = $this->name.'_'.$field.'_expander';
        $this->js(true)->find('.'.$class)->button();

        // initialize expander
        $this->js(true)
            ->_selector('.'.$class)
            ->_load('ui.atk4_expander2')
            ->atk4_expander();
    }

    /**
     * Format expander
     *
     * @param string $field field name
     * @param array $column column configuration
     *
     * @return void
     */
    function format_expander($field, $column)
    {
        if (!@$this->current_row[$field]) {
            $this->current_row[$field] = $column['descr'];
        }

        // TODO: 
        // reformat this using Button, once we have more advanced system to
        // bypass rendering of sub-elements.
        // $this->current_row[$field] = $this->add('Button',null,false)
        $key   = $this->name . '_' . $field . '_';
        $id    = $key . $this->api->normalizeName($this->model->id);
        $class = $key . 'expander';

        @$this->current_row_html[$field] = 
            '<input type="checkbox" '.
                'class="'.$class.'" '.
                'id="'.$id.'" '.
                'rel="'.$this->api->url(
                    $column['page'] ?: './'.$field,
                    array(
                        'expander' => $field,
                        'expanded' => $this->name,
                        'cut_page' => 1,
                        // TODO: id is obsolete
                        //'id' => $this->model->id,
                        $this->columns[$field]['refid'].'_id' => $this->model->id
                    )
                ).'" '.
            '/>'.
            '<label for="'.$id.'">' . $this->current_row[$field] . '</label>';
    }

    function format_SchemeGroupToSchemeName($field){
        if(!isset($this->from_date))
            throw $this->exception('Specify from_date as grid variable $grid->from_date =xyz', 'ValidityCheck')->setField('FieldName');
        
        if(!isset($this->to_date))
            throw $this->exception('Specify to_date as grid variable $grid->to_date =xyz', 'ValidityCheck')->setField('FieldName');

        $this->current_row_html[$field]=
                '<a href="#" onclick="$(this).univ().frameURL(\'Dig in to Scheme Group '.$this->current_row[$field].' \',\''.$this->api->url("reports_BSAndPANL_group2scheme",array('SchemeGroup'=>$this->current_row['SchemeGroup'], 'from_date'=>$this->from_date,'to_date'=>$this->to_date)).'\')">
                '.$this->current_row[$field].'
                </a>';
    }

    function format_SchemeNameToAccounts($field){

        if(!isset($this->from_date))
            throw $this->exception('Specify from_date as grid variable $grid->from_date =xyz', 'ValidityCheck')->setField('FieldName');
        
        if(!isset($this->to_date))
            throw $this->exception('Specify to_date as grid variable $grid->to_date =xyz', 'ValidityCheck')->setField('FieldName');

        $this->current_row_html[$field]=
                '<a href="#" onclick="$(this).univ().frameURL(\'Dig in to '.$this->current_row[$field].' \',\''.$this->api->url("reports_BSAndPANL_scheme2accounts",array('Scheme'=>$this->current_row['Scheme'], 'from_date'=>$this->from_date,'to_date'=>$this->to_date)).'\')">
                '.$this->current_row[$field].'
                </a>';   
    }

    function format_toAccountStatement($field){
        if(!isset($this->from_date))
            throw $this->exception('Specify from_date as grid variable $grid->from_date =xyz', 'ValidityCheck')->setField('FieldName');
        
        if(!isset($this->to_date))
            throw $this->exception('Specify to_date as grid variable $grid->to_date =xyz', 'ValidityCheck')->setField('FieldName');

        $this->current_row_html[$field]=
                '<a class="col-width;" href="#" onclick="$(this).univ().frameURL(\'Account Statement '.$this->current_row[$field].' \',\''.$this->api->url("accounts_statement",array('AccountNumber'=>$this->current_row['AccountNumber'], 'from_date'=>$this->from_date,'to_date'=>$this->to_date)).'\')">
                '.$this->current_row[$field].'
                </a>';  
    }

}