<?php

/**
* description: xEPAN Grid, lets you defined template by options
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/


class Grid_Template extends \Grid{
	
    public $row_edit=true;
	public $row_delete=true;
    public $defaultTemplate = null;
    public $paginator_class='Paginator';

    public $sort_icons = array(
        ' fa fa-sort',
        ' fa fa-sort-asc',
        ' fa  fa-sort-desc'
    );

    function defaultTemplate(){
        if($this->defaultTemplate) return $this->defaultTemplate;
        return parent::defaultTemplate();
    }
	
	function precacheTemplate(){
		if($this->template->template_file != 'grid'){
            foreach ($this->columns as $name => $column) {
                if (isset($column['sortable'])) {
                    $s = $column['sortable'];
                    $temp_template= $this->add('GiTemplate')
                        ->loadTemplateFromString('<span class="{$sorticon}">');
                    $temp_template->trySet('order', $s[0])
                        ->trySet('sorticon', $this->sort_icons[$s[0]]);
                    $this->template
                        ->trySet($name.'_sortid', $sel = $this->name.'_sort_'.$name)
                        ->trySetHTML($name.'_sort', $temp_template->render());

                    $this->js('click', $this->js()->reload(array($this->name.'_sort'=>$s[1])))
                        ->_selector('#'.$sel);
                }
            }  
            return;
        }
		return parent::precacheTemplate();
	}

	function formatRow(){

	    parent::formatRow();

	    if($this->owner instanceof \CRUD){
            if(!$this->current_row_html['edit']){
                if($this->row_edit)
                    $this->current_row_html['edit']= '<a class="table-link pb_edit" href="#" data-id="'.$this->model->id.'"><span class="fa-stack"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-pencil fa-stack-1x fa-inverse"></i></span></a>';
                else
                    $this->current_row_html['edit']= '<span class="fa-stack table-link"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-pencil fa-stack-1x fa-inverse"></i></span>';
            }

            if(!$this->current_row_html['delete']){
    			if($this->row_delete)
    			    $this->current_row_html['delete']= '<a class="table-link danger do-delete" href="#" data-id="'.$this->model->id.'"><span class="fa-stack"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-trash-o fa-stack-1x fa-inverse"></i></span></a>';
    			else
    			    $this->current_row_html['delete']= '<span class="table-link fa-stack"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-trash-o fa-stack-1x fa-inverse"></i></span>';
            }
	    }
	}

	function applyTDParams($field, &$row_template = null)
    {
        // data row template by default
        if (!$row_template) {
            $row_template = &$this->row_t;
        }

        // setting cell parameters (tdparam)
        $tdparam = @$this->tdparam[$this->getCurrentIndex()][$field];
        $tdparam_str = '';
        if (is_array($tdparam)) {
            if (is_array($tdparam['style'])) {
                $tdparam_str .= 'style="';
                foreach ($tdparam['style'] as $key=>$value) {
                    $tdparam_str .= $key . ':' . $value . ';';
                }
                $tdparam_str .= '" ';
                unset($tdparam['style']);
            }

            //walking and combining string
            foreach ($tdparam as $id=>$value) {
                $tdparam_str .= $id . '="' . $value . '" ';
            }

            // set TD param to appropriate row template
            $row_template->trySet("tdparam_$field", trim($tdparam_str));
        }
    }


    function init_expanderplain($field)
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
        // $this->js(true)->find('.'.$class)->button();

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
    function format_expanderplain($field, $column)
    {

        $class = $this->columns[$field]['button_class'].' button_'.$field;
        $icon = isset($this->columns[$field]['icon'])
                    ? $this->columns[$field]['icon']
                    : '';
        // if($icon)
        //     $icon = "<i class='icon-$icon'></i> ";

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
            '<div '.
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
            '>'.
            $icon. (!isset($this->columns[$field]['icon_only'])? $this->current_row[$field]:'') .
            '</div>';
    }

    function render(){
        // $this->js(true)->_load('footable')->_css('libs/footable.core')->find('table')->footable();
        parent::render();
    }

}
