<?php

/*
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 * 
 * Copyright (c) 2012 Ing. Švantner Ján <janci@janci.net>
 * 
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

/**
 * AdminGrid is component render datagrid in administration
 *
 * @author Ing. Švantner Ján <janci@janci.net>
 */
class AdminGrid extends CMSControl {
    private $columns = array();
    private $actions;
    private $query;
    private $add;
    
    public function setTable(NTableSelection $table){
        $this->query = $table;
        $this->query->select('id');
    }
    
    public function configure($config){
        if(isset($config['add'])) $this->add = $config['add'];
    }
    
    public function addColumn(IDatagridColumn $column){
        $this->columns[] = $column;
        if(!isset($this->query)) throw new PluginException("Missing connection to database by method setTable.");
        
        $column->modifySelection($this->query);
    }
    
    public function addAction(IDatagridAction $action){
        $this->actions[] = $action;
    }
    
    public function render(){
        $template = $this->getDefaultTemplate();
        $template->columns = $this->columns;
        $template->items   = $this->query;
        $template->add     = $this->add;
        $template->actions = $this->actions;
        $template->render();
    }
}
