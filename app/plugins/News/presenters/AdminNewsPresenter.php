<?php
/**
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 *
 * Copyright (c) 2012 Ján Švantner (http://www.janci.net)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

class AdminNewsPresenter extends BaseAdminPresenter {
    private $new_id;
    public function renderDefault(){
        $this->template->news = $this->db->table('new')->where('remove',0);
    }

    public function actionAdd(){
        $this->view = 'edit';
    }

    public function actionEdit($id){
        $this->new_id = $id;
    }

    public function handleRemove($id){
        $a = $this->db->table('new')->where('id',$id)->update(array('remove'=>1));
        if (isset($a) && is_numeric($a)) 
            $this->flashMessage('Stránka bola úspešne vymazaná', 'success');
        else
            $this->flashMessage('Stránku sa nepodarilo vymazať', 'error');
        $this->redirect('default');
    }

    public function createComponentEditNewForm(){
        $new = $this->db->table('new')->where(array('id'=>$this->new_id))->fetch();
        $new = ($new)? $new->toArray():array();
        $form = new NAppForm; 
        $form->addGroup('Novinka');
        $form->addText('title','Názov:');
        $form->addTextarea('content','Text novinky:');
        if (isset($this->new_id)) {
            $form->addHidden('operation','edit');
            $form->addSubmit('odoslat','Upraviť novinku');
        } else {
            $form->addHidden('operation','insert');
            $form->addSubmit('odoslat','Pridať novinku');
        }
        if (isset($this->new_id)) $form->setValues($new);

        $form->onSuccess[] = array($this,'onSubmitEditNewForm');
        return $form;
    }

    public function onSubmitEditNewForm(NAppForm $form){
        $val = $form->values;
        $up_pages = array('title'  => $val['title'], 'content'=> $val['content'] );

        $dbq = $this->db->table('new')->where(array('id'=>$this->new_id));
        if ($val['operation']=='insert'){
            $up_pages['created'] = new DateTime();
            $dbq->insert($up_pages);
        } else {
            $dbq->update($up_pages);
        }

        $this->flashMessage('Uloženie prebehlo v poriadku!','success');
        $this->redirect('default');
    }
}
