<?php

/*
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 * 
 * Copyright (c) 2012 Ing. Švantner Ján <janci@janci.net>
 * 
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

/**
 * AdminForm is component render formular in administration
 *
 * @author Ing. Švantner Ján <janci@janci.net>
 */
class AdminForm extends CMSControl {
    /** @var NAppForm */
    protected $form;
    
    /** @var NTableSelection */
    protected $table;
    
    protected $id;
    
    public function setTable($selection){
        $this->table = $selection;
    }
    
    public function configure($config) {
        parent::configure($config);
        $this['form'] = $this->form = $form = new NAppForm;
        $items = $config['items'];
        foreach($items as $name => $item){
            $label = isset($item['title'])? $item['title']:"";
            switch($item['type']){
                case "block": $this->form->addGroup($label); break;
                case "text": $this->form[$name] = new NTextInput($label); break;
                case "textarea": $this->form[$name] = new NTextArea($label, 40, 10); break;
                case "checkbox": $this->form[$name] = new NCheckbox($label); break;
            }
        }
       
    }
    
    protected function attached($presenter) {
        /* @var $presenter NPresenter */
        parent::attached($presenter);
        $this->id = $id = $presenter->getParameter('id');
        if(isset($id)) {
            
            $this->form->onSuccess[] = array($this, 'update');
            if(!$this->form->isSubmitted() )
                $this->form->setValues($this->table->where('id', $id)->fetch()->toArray());
        } else {
            $this->form->onSuccess[] = array($this, 'insert');
        }
    }
    
    public function update(NAppForm $form){
        $values = $form->getValues();
        $this->table->where('id', $this->id)->update($values);
        $this->getPresenter()->redirect('default');
    }
    
    public function insert(NAppForm $form){
        $values = $form->getValues();
        $values['created'] = new DateTime;
        $this->table->insert($values);
        $this->getPresenter()->redirect('default');
    }
    
    public function render($param){
        $template = $this->getDefaultTemplate();
        $template->form   = $this->form;
        if(isset($this->id)){
            $this->form->addSubmit('odoslat','Upraviť záznam');
        } else {
            $this->form->addSubmit('odoslat','Pridať záznam');
        }
        
        $template->render();
    }
}
